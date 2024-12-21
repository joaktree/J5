<?php
/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */

namespace Joaktree\Component\Joaktree\Site\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class ListModel extends BaseDatabaseModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $app 		= Factory::getApplication('site');

        $context			= 'com_joaktree.list.list.';
        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');

        $limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');
        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    public function getUserAccess()
    {
        return JoaktreeHelper::getUserAccess();
    }

    public function getTreeId()
    {
        return JoaktreeHelper::getTreeId();
    }

    public function getRelationId()
    {
        return JoaktreeHelper::getRelationId();
    }

    public function getTechnology()
    {
        return JoaktreeHelper::getTechnology();
    }

    public static function getAccess()
    {
        return JoaktreeHelper::getAccessTree();
    }

    private function _buildquery()
    {
        $levels			= JoaktreeHelper::getUserAccessLevels();

        $query = $this->_db->getquery(true);

        // select from persons
        $query->select(' jpn.id ');
        $query->select(' jpn.app_id ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectFirstName().' ) AS firstName ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectPatronym().' ) AS patronym ');
        $query->select(' MIN( '.JoaktreeHelper::getConcatenatedFamilyName().' ) AS familyName ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectBirthYear().' ) AS birthDate ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectDeathYear().' ) AS deathDate ');
        $query->from(' #__joaktree_persons jpn ');

        // select from admin persons
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(false));

        // select from tree x persons
        $query->innerJoin(
            ' #__joaktree_tree_persons  jtp '
                         .' ON (   jtp.app_id    = jpn.app_id '
                         .'    AND jtp.person_id = jpn.id '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_trees         jte '
                         .' ON (   jte.app_id    = jtp.app_id '
                         .'    AND jte.id        = jtp.tree_id '
                         .'    AND jte.published = true '
                         .'    AND jte.access    IN '.$levels.' '
                         .'    ) '
        );

        // select from birth and death
        $query->leftJoin(JoaktreeHelper::getJoinBirth());
        $query->leftJoin(JoaktreeHelper::getJoinDeath());

        // Get the WHERE, GROUP BY and ORDER BY clauses for the query
        $query      	= $this->_buildContentWhere($query);
        $query->group(' jpn.id ');
        $query->group(' jpn.app_id ');
        $query->order(' '.$this->_buildContentOrderBy().' ');


        return $query;
    }

    private function _buildContentWhere($query)
    {
        $app 		= Factory::getApplication('site');
        $treeId     = intval($this->getTreeId());
        $levels		= JoaktreeHelper::getUserAccessLevels();

        $context	= 'com_joaktree.list.list.';

        $search1	= $app->getUserStateFromRequest($context.'search1', 'search1', '', 'string');
        $search1	= $this->_db->escape($search1, true);
        $search1	= strtolower($search1);

        $search2	= $app->getUserStateFromRequest($context.'search2', 'search2', '', 'string');
        $search2	= $this->_db->escape($search2, true);
        $search2	= strtolower($search2);

        $search3	= $app->getUserStateFromRequest($context.'search3', 'search3', '', 'string');
        $search3	= $this->_db->escape($search3, true);
        $search3	= strtolower($search3);

        $search4	= $app->getUserStateFromRequest($context.'search4', 'search4', '', 'string');
        $search4    = base64_decode($search4);
        $search4	= $this->_db->escape($search4, true);
        $search4 = implode("", explode("\\", $search4));
        $search4	= stripslashes($search4);

        if ($treeId) {
            $query->where('jtp.tree_id = :treeid');
            $query->bind(':treeid', $treeId, \Joomla\Database\ParameterType::INTEGER);
        }
        if ($search1) {
            $query->where('LOWER(jpn.firstName) LIKE '.$this->_db->Quote('%'.$search1.'%'));
        }

        if ($search2) {
            $query->where('LOWER(jpn.patronym) LIKE '.$this->_db->Quote('%'.$search2.'%'));
        }

        if ($search3) {
            $query->where('LOWER(jpn.familyName) LIKE '.$this->_db->Quote('%'.$search3.'%')) ;
        }

        if ($search4) {
            $query->where('EXISTS '
                        .'( '
                        .'SELECT 1 '
                        .'FROM   #__joaktree_person_events     ejpe '
                        .'JOIN   #__joaktree_display_settings  ejds '
                        .'ON     (   ejds.code      = ejpe.code '
                        .'       AND ejds.level     = '.$this->_db->Quote('person').' '
                        .'       AND ejds.published = true '
                        .'       ) '
                        .'JOIN   #__joaktree_admin_persons     ejan '
                        .'ON     (   ejan.app_id    = ejpe.app_id '
                        .'       AND ejan.id        = ejpe.person_id '
                        .'       AND ejan.published = true '
                        // privacy filter
                        .'       AND (  (ejan.living = false AND ejds.access IN '.$levels.') '
                        .'           OR (ejan.living = true  AND ejds.accessLiving IN '.$levels.') '
                        .'           ) '
                        .'       ) '
                        .'WHERE  ejpe.person_id = jpn.id '
                        .'AND    ejpe.app_id    = jpn.app_id '
                        .'AND    ejpe.location  = '.$this->_db->Quote($search4).' '
                        .'UNION '
                        .'SELECT 1 '
                        .'FROM   #__joaktree_relation_events   ejre '
                        .'JOIN   #__joaktree_display_settings  rjds '
                        .'ON     (   rjds.code      = ejre.code '
                        .'       AND rjds.level     = '.$this->_db->Quote('relation').' '
                        .'       AND rjds.published = true '
                        .'       ) '
                        .'JOIN   #__joaktree_admin_persons     ejan1 '
                        .'ON     (   ejan1.app_id    = ejre.app_id '
                        .'       AND ejan1.id        = ejre.person_id_1 '
                        .'       AND ejan1.published = true '
                        // privacy filter
                        .'       AND (  (ejan1.living = false AND rjds.access IN '.$levels.') '
                        .'           OR (ejan1.living = true  AND rjds.accessLiving IN '.$levels.') '
                        .'           ) '
                        .'       ) '
                        .'JOIN   #__joaktree_admin_persons     ejan2 '
                        .'ON     (   ejan2.app_id    = ejre.app_id '
                        .'       AND ejan2.id        = ejre.person_id_2 '
                        .'       AND ejan2.published = true '
                        // privacy filter
                        .'       AND (  (ejan2.living = false AND rjds.access IN '.$levels.') '
                        .'           OR (ejan2.living = true  AND rjds.accessLiving IN '.$levels.') '
                        .'           ) '
                        .'       ) '
                        .'WHERE  (  ejre.person_id_1 = jpn.id '
                        .'       OR ejre.person_id_2 = jpn.id '
                        .'       ) '
                        .'AND    ejre.app_id    = jpn.app_id '
                        .'AND    ejre.location  = '.$this->_db->Quote($search4).' '
                        .') ');
        }

        //$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

        return $query;
    }

    private function _buildContentOrderBy()
    {
        $app 				= Factory::getApplication('site');

        $context			= 'com_joaktree.list.list.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jpn.familyName', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order == 'jpn.familyName') {
            //$orderby 	= ' ORDER BY  jpn.familyName '.$filter_order_Dir.', jpn.firstName '.$filter_order_Dir.' ';
            $orderby 	= ' jpn.familyName '.$filter_order_Dir.', jpn.firstName '.$filter_order_Dir.' ';
        } else {
            //$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', jpn.familyName '.$filter_order_Dir.' ';
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.', jpn.familyName '.$filter_order_Dir.' ';
        }

        return $orderby;
    }

    public function getPersonlist()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_personlist)) {
            $query = $this->_buildquery();
            $this->_personlist = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
            if (!count($this->_personlist) && $this->getState('limitstart') > 0) {
                // fix pagination : not on first page : retry
                $this->setState('limitstart', 0);
                $this->_personlist = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
            }
            $action = JoaktreeHelper::getAction();
            if ($action == 'saveparent1') {
                for ($i = 0, $n = count($this->_personlist); $i < $n; $i++) {
                    $this->_personlist[$i]->partners = $this->getPartners($this->_personlist[$i]->app_id, $this->_personlist[$i]->id);
                }
            }
        }

        return $this->_personlist;
    }

    public function getTree_id()
    {
        return $this->_tree_id;
    }


    public function getPatronymSetting()
    {
        static $_patronymSetting;

        if (!isset($_patronymSetting)) {
            $params = JoaktreeHelper::getJTParams();
            $_patronymSetting	= (int) $params->get('patronym');
        }

        return $_patronymSetting;
    }

    public function getFilter3()
    {
        return $this->filter3;
    }


    public function getTotal()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildquery();

            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    public function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        } else {
            $this->setState('limitstart', 0);
        }

        return $this->_pagination;
    }

    public function getMenusJoaktree()
    {
        static $_menuTreeId1 	= array();

        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId1) == 0) {
            $_menuTreeId1 = JoaktreeHelper::getMenus('joaktree');
        }

        return $_menuTreeId1;
    }

    public function getMenusList()
    {
        static $_menuTreeId2 	= array();

        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId2) == 0) {
            $_menuTreeId2 = JoaktreeHelper::getMenus('list');
        }

        return $_menuTreeId2;
    }

    public function getLastUpdate()
    {
        return JoaktreeHelper::lastUpdateDateTime();
    }

    private function getPartnerSet($number1, $appId, $personId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $number2 = ($number1 == '1') ? '2' : '1';
        $query = $db->getquery(true);

        // select relationship
        $query->select(' jrn.family_id ');
        $query->select(' jrn.person_id_'.$number2.' AS relation_id ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id = :appid');
        $query->where(' jrn.person_id_'.$number1.' = :personid');
        $query->where(' jrn.type = '.$db->quote('partner').' ');

        // select name partner
        $query->select(JoaktreeHelper::getConcatenatedFullName().' AS fullName ');
        $query->innerJoin(
            ' #__joaktree_persons  jpn '
                         .' ON (   jpn.app_id = jrn.app_id '
                         .'    AND jpn.id     = jrn.person_id_'.$number2.' '
                         .'    ) '
        );

        // select from admin persons
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons());

        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $personId, \Joomla\Database\ParameterType::STRING);

        $db->setquery($query);
        $partners  = $db->loadAssocList();

        return $partners;
    }

    private function getChildrenSet($appId, $personId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getquery(true);

        // select relationship
        $query->select(' DISTINCT jrn.family_id ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id = :appid');
        $query->where(' jrn.person_id_2 = :personid');
        $query->where(' jrn.type IN ('.$db->quote('father').', '.$db->quote('mother').') ');
        $query->where(
            ' NOT EXISTS '
                     . ' ( SELECT 1 '
                     . '   FROM   #__joaktree_relations  jrn2 '
                     . '   WHERE  jrn2.app_id    = jrn.app_id '
                     . '   AND    jrn2.family_id = jrn.family_id '
                     . '   AND    jrn2.type      = '.$db->quote('partner').' '
                     . ' ) '
        );
        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $personId, \Joomla\Database\ParameterType::STRING);

        $db->setquery($query);
        $familyId  = $db->loadResult();

        return $familyId;
    }

    private function getPartners($appId, $personId)
    {

        $partners1 = $this->getPartnerSet('1', $appId, $personId);
        $partners2 = $this->getPartnerSet('2', $appId, $personId);

        // join the arrays and sort them
        $partners = array_merge($partners1, $partners2);
        ksort($partners);

        // add "single" person option
        $single = array();
        $familyId = $this->getChildrenSet($appId, $personId);
        $single['family_id'] = ($familyId) ? $familyId : '0';
        $single['relation_id'] = null;
        $single['fullName']  = Text::_('JT_NOPARTNER');
        $partners[] = $single;

        return $partners;
    }
}
