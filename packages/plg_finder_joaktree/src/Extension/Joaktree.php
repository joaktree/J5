<?php
/**
 * Joomla! plugin Joaktree finder
 *
 * @version	2.0.1
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 *
 */

namespace Joaktree\Plugin\Finder\Joaktree\Extension;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Taxonomy;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Model\PersonsModel;

final class Joaktree extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Joaktree';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_joaktree';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'joaktree';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Joaktree';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__joaktree_persons';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'published';


    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderChangeState'         => 'onFinderChangeState',
            'onFinderAfterDelete'         => 'onFinderAfterDelete',
            'onFinderAfterSave'           => 'onFinderAfterSave',
        ]);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete($event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();
        if ($context === 'com_joaktree.person') {
            $app_id = $table->app_id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
            $this->indexer->remove($id, true);
            return;
        } else {
            return;
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->qn('link_id'))
              ->from($db->qn('#__finder_links'))
              ->where(($db->qn('url').' LIKE '.$db->q('%option=com_joaktree%&personId='.$app_id.'!%')));
        $db->setQuery($query);
        $items = $db->loadColumn();
        // Remove items from the index.
        foreach ($items as $item) {
            $this->indexer->remove($item, true);
        }
    }
    /**
    * Smart Search after save content method.
    * Reindexes the link information for an article that has been saved.
    * It also makes adjustments if the access level of an item or the
    * category to which it belongs has changed.
    *
    * @param   FinderEvent\AfterSaveEvent   $event  The event instance.
    *
    * @return  void
    *
    * @since   2.5
    * @throws  \Exception on database error.
    */
    public function onFinderAfterSave($event): void
    {
        $context = $event->getContext();
        $app     = $event->getItem();
        $isNew   = $event->getIsNew();
        if ($context === 'com_joaktree.person') {
            Indexer::resetState();
            $this->indexApplication($app->app_id);
            Taxonomy::removeOrphanNodes();
            Indexer::resetState();
        }

    }
    protected function indexApplication($app_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // Check if we can use the supplied SQL query.
        $query = $this->getListQuery();
        $query->where(' jpn.app_id = '.(int)$app_id);
        // $query->where(' jte.id  > 0');
        $db->setQuery($query);
        $items = $this->db->loadAssocList();

        foreach ($items as &$item) {
            $item = ArrayHelper::toObject($item, Result::class);
            // Set the item type.
            $item->type_id = $this->type_id;
            // Set the mime type.
            $item->mime = $this->mime;
            // Set the item layout.
            $item->layout = $this->layout;
            $this->index($item);
        }

    }
    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished from the list view.
     *
     * @param   FinderEvent\AfterChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState($event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();
        // We only want to handle articles here.
        if ($context === 'com_joaktree.person') {
            $this->itemStateChange($pks, $value);
        }

    }
    /*
        Joaktree : change state value
    */
    protected function itemStateChange($pks, $value)
    {
        foreach ($pks as $pk) {
            $this->change($pk, 'state', $value);
        }
    }
    /*
        Create Joaktree url
        format : index.php?option=com_joaktree&view=joaktree&tech=a&treeId=<treeid>&personId=<personId
        with tech = fixed value "a"
    */
    protected function getUrl($id, $extension, $view)
    {
        $spl = explode('!', $id);
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('default_tree_id');
        $query->from(' #__joaktree_admin_persons ');
        $query->where(' app_id = '.$spl[0].' ');
        $query->where(' id     = '.$db->q($spl[1]).' ');
        $db->setquery($query);
        $treeId  = $db->loadresult();
        if (!$treeId) { // not found : assume appId = treeId
            $treeId = $spl[0];
        }
        return 'index.php?option=' . $extension . '&view=' . $view . '&tech=a&treeId='.$treeId.'&personId=' . $id;
    }

    /*
        ignore this
    */
    public function onFinderGarbageCollection()
    {
        return 0 ;
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        $item->setLanguage();
        $item->params   = new Registry($item->params);
        $item->metadata = new Registry($item->metadata);

        $item->context = 'com_joaktree.person';
        // Build the necessary route and path information.
        $item->url   = 'index.php?option=com_joaktree&view=joaktree&tech=a&treeId='.$item->tree_id.'&personId='.$item->app_id.'!'.$item->person_id;
        $item->route = $item->url;
        $item->state = 1;// force state ok

        $item->addTaxonomy('Type', 'Joaktree');

        // $item->publish_start_date = $item->created;
        // $item->start_date = $item->created;

        // Get content extras.
        Helper::getContentExtras($item);

        $this->indexer->index($item);
    }
    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A JDatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $linkType	= $this->params->def('link_option', 1);
        $userAccessLevels	= JoaktreeHelper::getUserAccessLevels();
        $displayAccess 		= JoaktreeHelper::getDisplayAccess();
        $searchJoaktree = Text::_('JTSRCH_GENEALOGY');
        $searchNotes    = Text::_('JTSRCH_NOTES');
        $notes 	= $this->params->def('search_notes', 0);
        $patronym	= $this->params->def('search_patronyms', 0);

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // Check if we can use the supplied SQL query.
        $query = $db->getQuery(true);
        $query->select(' CONCAT_WS( " "'
                        .'                   , jpn.firstName '
                        .                    (($patronym == 1) ? ', jpn.patronym ' : '')
                        .'                   , jpn.namePreposition '
                        .'                   , jpn.familyName '
                        .'                   )                  AS title '
                        .',         CONCAT_WS( " / " '
                        .'                   , '. $db->q($searchJoaktree) .' '
                        .'                   , jte.name '
                        .'                   )                  AS section '
                        .',         jpn.lastUpdateTimeStamp    	AS created '
                        .',         "'.$linkType.'"             AS browsernav '
                        .',         jpn.app_id                  AS app_id '
                        .',         jpn.id                      AS person_id '
                        .',         jte.id                      AS tree_id '
                        .',         jte.access                  AS access '
                        .',         IF ( (jan.living = true  AND '.$displayAccess['BIRTperson']->living.'    = 1) '
                        .'             , '.$db->q(Text::_('JTSRCH_ALTERNATIVE')).' '
                        .'             , birth.eventDate '
                        .'             )                        AS birthDate '
                        .',         IF ( (jan.living = true  AND '.$displayAccess['DEATperson']->living.'    = 1) '
                        .'             , '.$db->q(Text::_('JTSRCH_ALTERNATIVE')).' '
                        .'             , death.eventDate '
                        .'             )                        AS deathDate '
                        .',         NULL                        AS value ');
        $query->from('#__joaktree_persons         AS jpn ');
        $query->join('LEFT', '#__joaktree_admin_persons   AS jan '
                        .'ON        (   jan.app_id    = jpn.app_id '
                        .'          AND jan.id        = jpn.id '
                        .'          AND jan.published = true '
                        .'          AND (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' = 2 ) '
                        .'              OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    = 2 ) '
                        .'              ) '
                        .'          )');
        $query->join('LEFT', '#__joaktree_trees           AS jte '
                        .'ON        (   jte.app_id    = jan.app_id '
                        .'          AND jte.id        = jan.default_tree_id '
                        .'          AND jte.published = true '
                        .'          AND jte.access    IN '. $userAccessLevels
                        .'          ) ');
        $query->join('LEFT', '#__joaktree_person_events birth '
                        .'ON        (   birth.app_id    = jpn.app_id '
                        .'          AND birth.person_id = jpn.id '
                        .'          AND birth.code      = '.$db->Quote('BIRT').' '
                        .'          AND (  (jan.living = false AND '.$displayAccess['BIRTperson']->notLiving.' > 0 ) '
                        .'              OR (jan.living = true  AND '.$displayAccess['BIRTperson']->living.'    > 0 ) '
                        .'              ) '
                        .'          ) ');
        $query->join('LEFT', '#__joaktree_person_events death '
                        .'ON        (   death.app_id    = jpn.app_id '
                        .'          AND death.person_id = jpn.id '
                        .'          AND death.code      = '.$db->Quote('DEAT').' '
                        .'          AND (  (jan.living = false AND '.$displayAccess['DEATperson']->notLiving.' > 0 ) '
                        .'              OR (jan.living = true  AND '.$displayAccess['DEATperson']->living.'    > 0 ) '
                        .'              ) '
                        .'          ) ');



        return $query;

    }

}
