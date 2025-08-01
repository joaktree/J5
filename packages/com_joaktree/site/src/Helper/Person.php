<?php
/**
 * Joomla! component Joaktree
 *
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

namespace Joaktree\Component\Joaktree\Site\Helper;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJStaticmap;
use Joaktree\Plugin\Content\Joaktree\Extension\Joaktree as PluginContentJoaktree;

class Person extends \StdClass
{
    // member declaration
    public $app_id;
    public $id;
    public $tree_id;
    public $secondParent_id;
    public $family_id;
    public $relationtype;
    public $orderNr;
    public $sex;
    public $firstName;
    public $patronym;
    public $firstNamePatronym;
    public $familyName;
    public $prefix;
    public $suffix;
    public $namePreposition;
    public $rawFamilyName;
    public $fullName;
    public $birthDate;
    public $deathDate;
    public $living;
    public $published;
    public $page;
    public $map;
    public $robots;
    public $indHasPage;
    public $lineage;
    public $indAltName;
    public $indNote;
    public $indAltNote;
    public $indCitation;
    public $indAltSource;
    public $indHasParent;
    public $indHasPartner;
    public $indHasChild;
    public $indNameDisplay;
    public $patronymSetting;
    public $menuItemId;
    public $lastUpdateDate;
    protected $_levels = null;
    protected $_db;
    protected $person = [];

    public function __construct($id, $type = 'basic')
    {

        $this->_db			= Factory::getContainer()->get(DatabaseInterface::class);
        $app				= Factory::getApplication();
        // get parameters
        $params				= JoaktreeHelper::getJTParams();
        $patronymSetting	= (int) $params->get('patronym');
        $patronymString		= $params->get('patronymSeparation');
        // get user access info
        $this->_levels		= JoaktreeHelper::getUserAccessLevels();
        $displayAccess		= JoaktreeHelper::getDisplayAccess();
        // basic or full
        $indFull = (($type != 'basic') && (($type != 'ancestor')));
        // retrieve person from database
        $query = $this->_db->getquery(true);
        // select from persons
        $query->select(' jpn.app_id            AS app_id ');
        $query->select(' jpn.id                AS id ');
        // names
        if ($indFull) {
            $query->select(JoaktreeHelper::getSelectFirstName().' AS firstName ');
            $query->select(JoaktreeHelper::getSelectPatronym().' AS patronym ');
            $query->select(JoaktreeHelper::getConcatenatedFamilyName().' AS familyName ');
            $query->select(' jpn.namePreposition ');
            $query->select(' jpn.prefix ');
            $query->select(' jpn.suffix ');
            $query->select(' jpn.familyName        AS rawFamilyName ');
        } else {
            $query->select(JoaktreeHelper::getConcatenatedFullName().' AS fullName ');
        }

        $query->select(' jpn.sex               AS sex ');
        $query->select(' jpn.indHasParent ');
        $query->select(' jpn.indHasPartner ');
        $query->select(' jpn.indHasChild ');
        $query->select(' DATE_FORMAT( jpn.lastUpdateTimeStamp, "%e %b %Y" ) '
                      .'                       AS lastUpdateDate ');
        if ($indFull) {
            $query->select(' IF( (  (jan.living = false AND '.$displayAccess['NOTEperson']->notLiving.' > 0 ) '
                          .'     OR (jan.living = true  AND '.$displayAccess['NOTEperson']->living.'    > 0 ) '
                          .'     ) '
                          .'   , jpn.indNote '
                          .'   , false '
                          .'   )                   AS indNote ');
            $query->select(' IF( (  (jan.living = false AND '.$displayAccess['SOURperson']->notLiving.' > 0 ) '
                          .'     OR (jan.living = true  AND '.$displayAccess['SOURperson']->living.'    > 0 ) '
                          .'     ) '
                          .'   , jpn.indCitation '
                          .'   , false '
                          .'   )                   AS indCitation ');
        }
        $query->from(' #__joaktree_persons   jpn ');
        $query->where(' jpn.app_id    = :appid');
        $query->where(' jpn.id        = :personid');
        // select from admin persons
        $query->select(' jan.living            AS living ');
        $query->select(' jan.published         AS published ');
        $query->select(' jan.page              AS page ');
        $query->select(' jan.map               AS map ');
        $query->select(' IF( ISNULL( NULLIF( jan.default_tree_id, 0) ) '
                      .'   , false '
                      .'   , jan.page '
                      .'   )                   AS indHasPage ');
        $query->select(' IF( (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' > 0 ) '
                      .'     OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    > 0 ) '
                      .'     ) '
                      .'   , true '
                      .'   , false '
                      .'   )                   AS indNameDisplay ');
        $query->select(' IF((jan.robots > 0), jan.robots, jte.robots ) AS robots ');
        if ($indFull) {
            $query->select(' IF( (jan.living = true AND '.$displayAccess['NAMEname']->living.' = 1 ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indAltName ');
            $query->select(' IF( (jan.living = true AND '.$displayAccess['NOTEperson']->living.' = 1 ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indAltNote ');
            $query->select(' IF( (jan.living = true AND '.$displayAccess['SOURperson']->living.' = 1 ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indAltSource ');
        }
        $query->bind(':appid', $id[ 'app_id' ], \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $id[ 'person_id' ], \Joomla\Database\ParameterType::STRING);

        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(true));

        if ($indFull) {
            // select from tree x persons
            $query->select(' jtp.tree_id           AS tree_id ');
            $query->select(' jtp.lineage ');
            $query->innerJoin(
                ' #__joaktree_tree_persons  jtp '
                             .' ON (   jtp.app_id    = jpn.app_id '
                             .'    AND jtp.person_id = jpn.id '
                             .'    AND jtp.tree_id   = :treeid'
                             .'    ) '
            );
            $query->innerJoin(
                ' #__joaktree_trees         jte '
                             .' ON (   jte.app_id    = jtp.app_id '
                             .'    AND jte.id        = jtp.tree_id '
                             .'    AND jte.published = true '
                             .'    AND jte.access    IN '.$this->_levels.' '
                             .'    ) '
            );
            $query->bind(':treeid', $id[ 'tree_id' ], \Joomla\Database\ParameterType::INTEGER);
        } else {
            // select from default tree
            $query->select(' jte.id                AS tree_id ');
            $query->leftJoin(
                ' #__joaktree_trees    jte '
                            .' ON (   jte.app_id    = jan.app_id '
                            .'    AND jte.id        = jan.default_tree_id '
                            .'    AND jte.published = true '
                            .'    AND jte.access    IN '.$this->_levels.' '
                            .'    ) '
            );
        }
        // select birth and death dates for ancestors
        if ($type == 'ancestor') {
            // select from birth
            $query->select(
                ' MIN( IF( (jan.living = true AND '.$displayAccess['BIRTperson']->living.' = 1 ) '
                          .'        , NULL '
                          .'        , birth.eventDate '
                          .'        ) '
                          .'    ) AS birthDate '
            );
            $query->leftJoin(
                ' #__joaktree_person_events birth '
                            .' ON (   birth.app_id    = jpn.app_id '
                            .'    AND birth.person_id = jpn.id '
                            .'    AND birth.code      = '.$this->_db->Quote('BIRT').' '
                            .'    AND (  (jan.living = false AND '.$displayAccess['BIRTperson']->notLiving.' > 0 ) '
                            .'        OR (jan.living = true  AND '.$displayAccess['BIRTperson']->living.'    > 0 ) '
                            .'        ) '
                            .'    ) '
            );
            // select from death
            $query->select(
                ' MIN( IF( (jan.living = true AND '.$displayAccess['DEATperson']->living.' = 1 ) '
                          .'        , NULL '
                          .'        , death.eventDate '
                          .'        ) '
                          .'    ) AS deathDate '
            );
            $query->leftJoin(
                ' #__joaktree_person_events death '
                            .' ON (   death.app_id    = jpn.app_id '
                            .'    AND death.person_id = jpn.id '
                            .'    AND death.code = '.$this->_db->Quote('DEAT').' '
                            .'    AND (  (jan.living = false AND '.$displayAccess['DEATperson']->notLiving.' > 0 ) '
                            .'        OR (jan.living = true  AND '.$displayAccess['DEATperson']->living.'    > 0 ) '
                            .'        ) '
                            .'    ) '
            );

            $query->group(' jpn.app_id ');
            $query->group(' jpn.id ');
            $query->group(JoaktreeHelper::getConcatenatedFullName());
            $query->group(' jpn.sex ');
            $query->group(' jpn.indHasParent ');
            $query->group(' jpn.indHasPartner ');
            $query->group(' jpn.indHasChild ');
            $query->group(' DATE_FORMAT( jpn.lastUpdateTimeStamp, "%e %b %Y" ) ');
            // select from admin persons
            $query->group(' jan.living ');
            $query->group(' jan.published ');
            $query->group(' jan.page ');
            $query->group(' jan.map ');
            $query->group(' IF( ISNULL( NULLIF( jan.default_tree_id, 0) ) '
                          .'   , false '
                          .'   , jan.page '
                          .'   ) ');
            $query->group(' IF( (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' > 0 ) '
                          .'     OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    > 0 ) '
                          .'     ) '
                          .'   , true '
                          .'   , false '
                          .'   ) ');
            $query->group(' IF((jan.robots > 0), jan.robots, jte.robots ) ');
            $query->group(' jte.id ');
        }
        try {
            $this->_db->setquery($query);
            $person  = $this->_db->loadAssoc();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        if ((!$person) && ($indFull)) {
            $query->clear();
            // select from persons
            $query->select(' jpn.app_id            AS app_id ');
            $query->select(' jpn.id                AS id ');
            $query->select(JoaktreeHelper::getSelectFirstName().' AS firstName ');
            $query->select(JoaktreeHelper::getSelectPatronym().' AS patronym ');
            $query->select(JoaktreeHelper::getConcatenatedFamilyName().' AS familyName ');
            $query->select(' jpn.namePreposition ');
            $query->select(' jpn.prefix ');
            $query->select(' jpn.suffix ');
            $query->select(' jpn.familyName        AS rawFamilyName ');
            $query->select(' jpn.sex               AS sex ');
            $query->select(' jpn.indHasParent ');
            $query->select(' jpn.indHasPartner ');
            $query->select(' jpn.indHasChild ');
            $query->select(' IF( (  (jan.living = false AND '.$displayAccess['NOTEperson']->notLiving.' > 0 ) '
                          .'     OR (jan.living = true  AND '.$displayAccess['NOTEperson']->living.'    > 0 ) '
                          .'     ) '
                          .'   , jpn.indNote '
                          .'   , false '
                          .'   )                   AS indNote ');
            $query->select(' IF( (  (jan.living = false AND '.$displayAccess['SOURperson']->notLiving.' > 0 ) '
                          .'     OR (jan.living = true  AND '.$displayAccess['SOURperson']->living.'    > 0 ) '
                          .'     ) '
                          .'   , jpn.indCitation '
                          .'   , false '
                          .'   )                   AS indCitation ');
            $query->select(' DATE_FORMAT( jpn.lastUpdateTimeStamp, "%e %b %Y" ) '
                          .'                       AS lastUpdateDate ');
            $query->from(' #__joaktree_persons   jpn ');
            $query->where(' jpn.app_id    = :appid');
            $query->where(' jpn.id        = :personid');
            $query->where(
                ' NOT EXISTS '
                         . ' ( SELECT 1 '
                         . '   FROM   #__joaktree_tree_persons  jtp2 '
                         . '   WHERE  jtp2.app_id    = jpn.app_id '
                         . '   AND    jtp2.person_id = jpn.id '
                         . '   AND    jtp2.tree_id   = '.$id[ 'tree_id' ].' '
                         . ' ) '
            );
            // select from tree x persons
            $query->select(' IFNULL( jan.default_tree_id '
                          .'       , '.$id[ 'tree_id' ].' '
                          .'       )               AS tree_id ');
            // select from admin persons
            $query->select(' jan.living            AS living ');
            $query->select(' jan.published         AS published ');
            $query->select(' jan.page              AS page ');
            $query->select(' jan.map               AS map ');
            $query->select(' IF( ISNULL( NULLIF( jan.default_tree_id, 0) ) '
                          .'   , false '
                          .'   , jan.page '
                          .'   )                   AS indHasPage ');
            $query->select(' IF( (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' > 0 ) '
                          .'     OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    > 0 ) '
                          .'     ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indNameDisplay ');
            $query->select(' IF( (jan.living = true AND '.$displayAccess['NAMEname']->living.' = 1 ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indAltName ');
            $query->select(' IF( (jan.living = true AND '.$displayAccess['NOTEperson']->living.' = 1 ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indAltNote ');
            $query->select(' IF( (jan.living = true AND '.$displayAccess['SOURperson']->living.' = 1 ) '
                          .'   , true '
                          .'   , false '
                          .'   )                   AS indAltSource ');

            $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(true));
            // robots
            $query->select(' IF((jan.robots > 0), jan.robots, jte.robots ) AS robots ');
            $query->leftJoin(
                ' #__joaktree_trees    jte '
                            .' ON (   jte.app_id    = jan.app_id '
                            .'    AND jte.id        = IFNULL( jan.default_tree_id , :treeid) '
                            .'    AND jte.published = true '
                            .'    AND jte.access    IN '.$this->_levels.' '
                            .'    ) '
            );
            $query->bind(':appid', $id[ 'app_id' ], \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $id[ 'person_id' ], \Joomla\Database\ParameterType::STRING);
            $query->bind(':treeid', $id[ 'tree_id' ], \Joomla\Database\ParameterType::INTEGER);
            try {
                $this->_db->setquery($query);
                $person  = $this->_db->loadAssoc();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
        if (is_null($person)) {
            return;
        }
        $this->app_id			= $person[ 'app_id' ];
        $this->id				= $person[ 'id' ];
        $this->tree_id			= $person[ 'tree_id' ];
        $this->sex				= $person[ 'sex' ];
        $this->indHasParent		= $person[ 'indHasParent' ];
        $this->indHasPartner	= $person[ 'indHasPartner' ];
        $this->indHasChild		= $person[ 'indHasChild' ];
        $this->lastUpdateDate	= $person[ 'lastUpdateDate' ];
        $this->living			= $person[ 'living' ];
        $this->published		= $person[ 'published' ];
        $this->page				= $person[ 'page' ];
        $this->map				= $person[ 'map' ];
        $this->indHasPage		= $person[ 'indHasPage' ];
        $this->indNameDisplay	= $person[ 'indNameDisplay' ];
        $this->robots			= $person[ 'robots' ];
        // names
        if ($indFull) {
            // when patronyms are shown, there are concatenated to the first name
            $this->firstName		= $person[ 'firstName' ];
            $this->patronym			= $person[ 'patronym' ];
            if (($patronymSetting == 0) or ($this->patronym == null)) {
                $this->firstNamePatronym	= $this->firstName;
            } else {
                $this->firstNamePatronym	= $this->firstName.' '.$patronymString.$person[ 'patronym' ].$patronymString;
            }
            $this->familyName		= $person[ 'familyName' ];
            $this->prefix			= $person[ 'prefix' ];
            $this->suffix			= $person[ 'suffix' ];
            $this->namePreposition	= $person[ 'namePreposition' ];
            $this->rawFamilyName	= $person[ 'rawFamilyName' ];
            $this->indNote			= $person[ 'indNote' ];
            $this->indCitation		= $person[ 'indCitation' ];
            $this->indAltName		= $person[ 'indAltName' ];
            $this->indAltNote		= $person[ 'indAltNote' ];
            $this->indAltSource		= $person[ 'indAltSource' ];
            $this->lineage			= isset($person[ 'lineage' ]) ? trim($person[ 'lineage' ]) : null;
        } else {
            $this->fullName			= $person[ 'fullName' ];
        }
        if ($type == 'ancestor') {
            $this->birthDate		= JoaktreeHelper::displayDate($person[ 'birthDate' ]);
            $this->deathDate		= JoaktreeHelper::displayDate($person[ 'deathDate' ]);
        }
        $this->secondParent_id	= null;
        $this->family_id		= null;
        $this->relationtype		= null;
        $this->orderNr			= null;
        $this->languageFilter   = $app->getLanguageFilter();
        if (isset($person[ 'tree_id' ]) && ($person[ 'tree_id' ])) {
            $menus 				= $this->getMenus();
            $this->menuItemId 	= $menus[ $person[ 'tree_id' ] ];
        }
        if (!isset($this->menuItemId)) {
            $menu				= $app->getMenu();
            $item				= $menu->getActive();
            $this->menuItemId 	= $item->id;
        }
    }
    public function getUserAccess()
    {
        return JoaktreeHelper::getUserAccess();
    }

    private function getMenus()
    {
        static $_menuTreeId 	= array();
        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId) == 0) {
            $_menuTreeId = JoaktreeHelper::getMenus('joaktree');
        }
        return $_menuTreeId;
    }

    public function getTrees()
    {
        $query =  $this->_db->getquery(true);
        $query->select(' jtp.tree_id ');
        $query->from(' #__joaktree_tree_persons  jtp ');
        $query->where(' jtp.app_id    = :appid');
        $query->where(' jtp.person_id = :personid');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

        $this->_db->setquery($query);
        $trees  = $this->_db->loadColumn();
        return $trees;
    }

    public function getPersonNames()
    {
        $displayAccess		= JoaktreeHelper::getDisplayAccess();
        $query =  $this->_db->getquery(true);
        // select from names
        $query->select(' jpn.orderNumber ');
        $query->select(' jpn.code ');
        $query->from(' #__joaktree_person_names  jpn ');
        $query->where(' jpn.app_id    = :appid');
        $query->where(' jpn.person_id = :personid');
        // select from settings
        $query->select(' jds.ordering ');
        $query->innerJoin(
            ' #__joaktree_display_settings  jds '
                         .' ON (   jds.code  = jpn.code '
                         .'    AND jds.level = '.$this->_db->Quote('name').' '
                         .'    AND jds.published = true '
                         .'    ) '
        );
        $query->order(' jds.ordering ');
        $query->order(' jpn.orderNumber ');
        // events
        if (!$this->living) {
            // not living
            $query->select(' jpn.eventDate ');
            $query->select(' jpn.value ');
            $query->select(
                ' IF( '.$displayAccess['NOTEname']->notLiving.' > 0 '
                          .'   , jpn.indNote '
                          .'   , false '
                          .'   ) AS indNote '
            );
            $query->select(' false  AS indAltNote ');
            $query->select(
                ' IF( '.$displayAccess['SOURname']->notLiving.' > 0 '
                          .'   , jpn.indCitation '
                          .'   , false '
                          .'   ) AS indCitation '
            );
            $query->select(' false  AS indAltSource ');
            $query->where(' jds.access IN '.$this->_levels.' ');
        } else {
            // living
            $query->select(
                ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                          .'   , NULL '
                          .'   , jpn.eventDate '
                          .'   ) AS eventDate '
            );
            $query->select(
                ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                          .'   , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                          .'   , jpn.value '
                          .'   ) AS value '
            );
            $query->select(
                ' IF( '.$displayAccess['NOTEname']->living.' > 0 '
                          .'   , jpn.indNote '
                          .'   , false '
                          .'   ) AS indNote '
            );
            $query->select(
                ' IF( '.$displayAccess['NOTEname']->living.' = 1 '
                          .'   , true '
                          .'   , false '
                          .'   ) AS indAltNote '
            );
            $query->select(
                ' IF( '.$displayAccess['SOURname']->living.' > 0 '
                          .'   , jpn.indCitation '
                          .'   , false '
                          .'   ) AS indCitation '
            );
            $query->select(
                ' IF( '.$displayAccess['SOURname']->living.' = 1 '
                          .'   , true '
                          .'   , false '
                          .'   ) AS indAltSource '
            );
            $query->where(
                ' (  jds.accessLiving IN '.$this->_levels.' '
                         .' OR jds.altLiving    IN '.$this->_levels.' '
                         .' ) '
            );
        }
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

        try {
            $this->_db->setquery($query);
            $personNames  = $this->_db->loadObjectList();
        } catch (\Exception $e) {
            return new \StdClass();
        }
        return $personNames;
    }

    public function getPersonEvents()
    {
        static $personEvents;
        if (!isset($personEvents)) {
            $displayAccess		= JoaktreeHelper::getDisplayAccess();
            $query =  $this->_db->getquery(true);
            // select from person events
            $query->select(
                ' IF( ( jpe.code = '.$this->_db->Quote('EVEN').' ) '
                          .'   , IFNULL( jpe.type, jpe.code ) '
                          .'   , jpe.code '
                          .'   ) AS code '
            );
            $query->select(
                ' IF( ( jpe.code = '.$this->_db->Quote('EVEN').' ) '
                          .'   , NULL '
                          .'   , jpe.type '
                          .'   ) AS type '
            );
            $query->select(' jpe.orderNumber ');
            $query->from(' #__joaktree_person_events  jpe ');
            $query->where(' jpe.app_id    = :appid');
            $query->where(' jpe.person_id = :personid');

            // select from settings
            $query->select(' jds.ordering ');
            $query->innerJoin(
                ' #__joaktree_display_settings  jds '
                             .' ON (   jds.code  = jpe.code '
                             .'    AND jds.level = '.$this->_db->Quote('person').' '
                             .'    AND jds.published = true '
                             .'    ) '
            );
            $query->order(' jds.ordering ');
            $query->order(' jpe.orderNumber ');
            // select from locations
            $query->select(' jln.longitude ');
            $query->select(' jln.latitude ');
            // events
            if (!$this->living) {
                // not living
                $query->select(' jpe.eventDate ');
                $query->select(' jpe.location ');
                $query->select(' jpe.value ');
                $query->select(
                    ' IF( '.$displayAccess['ENOTperson']->notLiving.' > 0 '
                              .'   , jpe.indNote '
                              .'   , false '
                              .'   ) AS indNote '
                );
                $query->select(' false AS indAltNote ');
                $query->select(
                    ' IF( '.$displayAccess['ESOUperson']->notLiving.' > 0 '
                              .'   , jpe.indCitation '
                              .'   , false '
                              .'   ) AS indCitation '
                );
                $query->select(' false AS indAltSource ');
                $query->where(' jds.access IN '.$this->_levels.' ');
                $query->leftJoin(
                    ' #__joaktree_locations  jln '
                                 .' ON ( jln.id = jpe.loc_id ) '
                );
            } else {
                // living
                $query->select(
                    ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                              .'   , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                              .'   , jpe.eventDate '
                              .'   ) AS eventDate '
                );
                $query->select(
                    ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                              .'   , NULL '
                              .'   , jpe.location '
                              .'   ) AS location '
                );
                $query->select(
                    ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                              .'   , NULL '
                              .'   , jpe.value '
                              .'   ) AS value '
                );
                $query->select(
                    ' IF( '.$displayAccess['ENOTperson']->living.' > 0 '
                              .'   , jpe.indNote '
                              .'   , false '
                              .'   ) AS indNote '
                );
                $query->select(
                    ' IF( '.$displayAccess['ENOTperson']->living.' = 1 '
                              .'   , true '
                              .'   , false '
                              .'   ) AS indAltNote '
                );
                $query->select(
                    ' IF( '.$displayAccess['ESOUperson']->living.' > 0 '
                              .'   , jpe.indCitation '
                              .'   , false '
                              .'   ) AS indCitation '
                );
                $query->select(
                    ' IF( '.$displayAccess['ESOUperson']->living.' = 1 '
                              .'   , true '
                              .'   , false '
                              .'   ) AS indAltSource '
                );
                $query->where(
                    ' (  jds.accessLiving IN '.$this->_levels.' '
                             . ' OR jds.altLiving    IN '.$this->_levels.' '
                             . ' ) '
                );
                $query->leftJoin(
                    ' #__joaktree_locations  jln '
                                 .' ON (   jln.id = IF( jds.accessLiving NOT IN '.$this->_levels.' '
                                 .'                   , -1 '
                                 .'                   , jpe.loc_id '
                                 .'                   ) '
                                 .'    AND jln.indDeleted = 0 '
                                 .'    ) '
                );
            }
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

            $this->_db->setquery($query);

            $personEvents  = $this->_db->loadObjectList();
        }
        return $personEvents;
    }

    private function getRelationquery($pid1, $pid2, $relationType)
    {
        $displayAccess		= JoaktreeHelper::getDisplayAccess();
        $query =  $this->_db->getquery(true);
        // select from relations
        $query->select(' jrn.app_id                 AS app_id ');
        $query->select(' jrn.person_id_'.$pid2.'    AS pid ');
        $query->select(' jrn.family_id              AS family_id ');
        $query->select(' jrn.subtype                AS subtype ');
        $query->select(' jrn.orderNumber_'.$pid1.'  AS orderNr ');
        $query->select(' 0                          AS secondParent_id ');
        $query->select(' jrn.indNote ');
        $query->select(' jrn.indCitation ');
        $query->from(' #__joaktree_relations jrn ');
        $query->where(' jrn.app_id              = :appid');
        $query->where(' jrn.person_id_'.$pid1.' = :personid');
        $query->where(' jrn.type                = :relationtype');
        $query->group(' jrn.app_id ');
        $query->group(' jrn.person_id_'.$pid2.' ');
        $query->group(' jrn.family_id ');
        $query->group(' jrn.subtype ');
        $query->group(' jrn.orderNumber_'.$pid1.' ');
        $query->group(' jrn.indNote ');
        $query->group(' jrn.indCitation ');
        $query->order(' jrn.orderNumber_'.$pid1.' ');
        // privacy filter from admin persons
        $query->innerJoin(
            ' #__joaktree_admin_persons jan1 '
                         .' ON (   jan1.app_id    = jrn.app_id '
                         .'    AND jan1.id        = jrn.person_id_'.$pid1.' '
                         .'    AND jan1.published = true '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_admin_persons jan2 '
                         .' ON (   jan2.app_id    = jrn.app_id '
                         .'    AND jan2.id        = jrn.person_id_'.$pid2.' '
                         .'    AND jan2.published = true '
                         .'    ) '
        );
        // select from birth
        $query->select(
            ' MIN( IF( (jan2.living = true AND '.$displayAccess['BIRTperson']->living.' = 1 ) '
                      .'        , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                      .'        , birth.eventDate '
                      .'        ) '
                      .'    ) AS birthDate '
        );
        $query->leftJoin(
            ' #__joaktree_person_events birth '
                        .' ON (   birth.app_id    = jrn.app_id '
                        .'    AND birth.person_id = jrn.person_id_'.$pid2.' '
                        .'    AND birth.code      = '.$this->_db->Quote('BIRT').' '
                        .'    AND (  (jan2.living = false AND '.$displayAccess['BIRTperson']->notLiving.' > 0 ) '
                        .'        OR (jan2.living = true  AND '.$displayAccess['BIRTperson']->living.'    > 0 ) '
                        .'        ) '
                        .'    ) '
        );
        // select from death
        $query->select(
            ' MIN( IF( (jan2.living = true AND '.$displayAccess['DEATperson']->living.' = 1 ) '
                      .'        , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                      .'        , death.eventDate '
                      .'        ) '
                      .'    ) AS deathDate '
        );
        $query->leftJoin(
            ' #__joaktree_person_events death '
                        .' ON (   death.app_id    = jrn.app_id '
                        .'    AND death.person_id = jrn.person_id_'.$pid2.' '
                        .'    AND death.code = '.$this->_db->Quote('DEAT').' '
                        .'    AND (  (jan2.living = false AND '.$displayAccess['DEATperson']->notLiving.' > 0 ) '
                        .'        OR (jan2.living = true  AND '.$displayAccess['DEATperson']->living.'    > 0 ) '
                        .'        ) '
                        .'    ) '
        );
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
        $query->bind(':relationtype', $relationType, \Joomla\Database\ParameterType::STRING);

        return $query;
    }

    private function getRelations($query, $type = 'basic')
    {
        $relations = array();
        $this->_db->setquery($query);
        $relations_id  = $this->_db->loadAssocList();
        // loop through result and fill tmp-array
        foreach ($relations_id as $i => $relation_id) {
            // retrieve person
            $id[ 'app_id' ] 	= $this->app_id;
            $id[ 'person_id' ] 	= $relation_id['pid'];
            $id[ 'tree_id' ]   	= $this->tree_id ;
            //$tmp               	= new joaktreeperson($id);
            $tmp               	= new Person($id, $type);
            if ((empty($relation_id['orderNr'])) || ($relation_id['secondParent_id'] != '0')) {
                $key = $i;
            } else {
                $key = $relation_id['orderNr'];
            }
            // check whether this person can be displayed
            if ($tmp->indNameDisplay) {
                $relations[ $key ] = $tmp;
                $relations[ $key ]->orderNr  		= $relation_id['orderNr'];
                $relations[ $key ]->birthDate 		= JoaktreeHelper::displayDate($relation_id[ 'birthDate' ]);
                $relations[ $key ]->deathDate 		= JoaktreeHelper::displayDate($relation_id[ 'deathDate' ]);
                $relations[ $key ]->secondParent_id = $relation_id['secondParent_id'];
                $relations[ $key ]->family_id  		= $relation_id['family_id'];
                $relations[ $key ]->relationtype	= (
                    (!empty($relation_id['subtype']))
                                                      && ($relation_id['subtype'] != 'natural')
                )
                                                        ? $relation_id['subtype']
                                                        : null;
            }
        }
        return $relations;
    }

    public function getFathers($type = 'basic')
    {
        $query  = $this->getRelationquery('1', '2', 'father');
        $fathers = $this->getRelations($query, $type);
        return $fathers;
    }

    public function getMothers($type = 'basic')
    {
        $query  = $this->getRelationquery('1', '2', 'mother');
        $mothers = $this->getRelations($query, $type);
        return $mothers;
    }

    public function getPartners($type = 'basic')
    {
        $query = $this->getRelationquery('1', '2', 'partner');
        $partners1 = $this->getRelations($query, $type);
        $query = $this->getRelationquery('2', '1', 'partner');
        $partners2 = $this->getRelations($query, $type);
        // join the arrays and sort them
        $partners = array_merge($partners1, $partners2);
        ksort($partners);
        return $partners;
    }

    public function getChildren($type = 'basic')
    {
        $displayAccess		= JoaktreeHelper::getDisplayAccess();
        $query =  $this->_db->getquery(true);
        // select from relations
        $query->select(' jrn.app_id                 AS app_id ');
        $query->select(' jrn.person_id_1            AS pid ');
        $query->select(' jrn.family_id              AS family_id ');
        $query->select(' jrn.subtype                AS subtype ');
        $query->select(' jrn.orderNumber_2          AS orderNr ');
        $query->select(' jrn.indNote ');
        $query->select(' jrn.indCitation ');
        $query->from(' #__joaktree_relations jrn ');
        $query->where(' jrn.app_id      = :appid');
        $query->where(' jrn.person_id_2 = :personid');
        $query->where(' jrn.type IN ('.$this->_db->Quote('father').','.$this->_db->Quote('mother').') ');
        $query->order(' jrn.orderNumber_2 ');
        // privacy filter from admin persons
        $query->innerJoin(
            ' #__joaktree_admin_persons jan1 '
                         .' ON (   jan1.app_id    = jrn.app_id '
                         .'    AND jan1.id        = jrn.person_id_1 '
                         .'    AND jan1.published = true '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_admin_persons jan2 '
                         .' ON (   jan2.app_id    = jrn.app_id '
                         .'    AND jan2.id        = jrn.person_id_2 '
                         .'    AND jan2.published = true '
                         .'    ) '
        );
        // select from second parent (i.e. spouse)
        $query->select(' jrn2.person_id_2           AS secondParent_id ');
        $query->leftJoin(
            ' #__joaktree_relations  jrn2 '
                        .' ON (   jrn2.app_id      = jrn.app_id '
                        .'    AND jrn2.person_id_1 = jrn.person_id_1 '
                        .'    AND jrn2.family_id   = jrn.family_id '
                        .'    AND jrn2.person_id_2 <> :personid'
                        .'    AND jrn2.type IN ('.$this->_db->Quote('father').','.$this->_db->Quote('mother').') '
                        .'    )'
        );
        // select from birth
        $query->select(
            ' MIN( IF( (jan1.living = true AND '.$displayAccess['BIRTperson']->living.' = 1 ) '
                      .'        , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                      .'        , birth.eventDate '
                      .'        ) '
                      .'    ) AS birthDate '
        );
        $query->leftJoin(
            ' #__joaktree_person_events birth '
                        .' ON (   birth.app_id    = jrn.app_id '
                        .'    AND birth.person_id = jrn.person_id_1 '
                        .'    AND birth.code      = '.$this->_db->Quote('BIRT').' '
                        .'    AND (  (jan1.living = false AND '.$displayAccess['BIRTperson']->notLiving.' > 0 ) '
                        .'        OR (jan1.living = true  AND '.$displayAccess['BIRTperson']->living.'    > 0 ) '
                        .'        ) '
                        .'    ) '
        );
        // select from death
        $query->select(
            ' MIN( IF( (jan1.living = true AND '.$displayAccess['DEATperson']->living.' = 1 ) '
                      .'        , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                      .'        , death.eventDate '
                      .'        ) '
                      .'    ) AS deathDate '
        );
        $query->leftJoin(
            ' #__joaktree_person_events death '
                        .' ON (   death.app_id    = jrn.app_id '
                        .'    AND death.person_id = jrn.person_id_1 '
                        .'    AND death.code = '.$this->_db->Quote('DEAT').' '
                        .'    AND (  (jan1.living = false AND '.$displayAccess['DEATperson']->notLiving.' > 0 ) '
                        .'        OR (jan1.living = true  AND '.$displayAccess['DEATperson']->living.'    > 0 ) '
                        .'        ) '
                        .'    ) '
        );

        // GROUP BY
        $query->group(' jrn.app_id ');
        $query->group(' jrn.person_id_1 ');
        $query->group(' jrn.family_id ');
        $query->group(' jrn.subtype ');
        $query->group(' jrn.orderNumber_2 ');
        $query->group(' jrn2.person_id_2 ');
        $query->group(' jrn.indNote ');
        $query->group(' jrn.indCitation ');

        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

        $children = $this->getRelations($query, $type);
        return $children;
    }

    public function getPartnerEvents($relation_id, $living)
    {
        $displayAccess		= JoaktreeHelper::getDisplayAccess();
        $query =  $this->_db->getquery(true);
        $query->select(
            ' IF( ( jre.code = '.$this->_db->Quote('EVEN').' ) '
                      .'   , IFNULL( jre.type, jre.code ) '
                      .'   , jre.code '
                      .'   ) AS code '
        );
        $query->select(
            ' IF( ( jre.code = '.$this->_db->Quote('EVEN').' ) '
                      .'   , NULL '
                      .'   , jre.type '
                      .'   ) AS type '
        );
        $query->select(' jre.orderNumber ');
        $query->from(' #__joaktree_relation_events  jre ');
        $query->where(' jre.app_id             = :appid');
        $query->where(' (  (   jre.person_id_1 = :personid'
                .'             AND jre.person_id_2 = :relation'
                .'             ) '
                .'          OR (   jre.person_id_2 =:personid '
                .'             AND jre.person_id_1 = :relation'
                .'             ) '
                .'          ) ');
        // select from settings
        $query->select(' jds.ordering ');
        $query->innerJoin(
            ' #__joaktree_display_settings  jds '
                         .' ON (   jds.code  = jre.code '
                         .'    AND jds.level = '.$this->_db->Quote('relation').' '
                         .'    AND jds.published = true '
                         .'    ) '
        );
        // ORDER
        $query->order(' jds.ordering ');
        $query->order(' jre.orderNumber ');
        // events
        if ((!$this->living) and (!$living)) {
            // both not living
            $query->select(' jre.eventDate ');
            $query->select(' jre.location ');
            $query->select(' jre.value ');
            $query->select(
                ' IF( '.$displayAccess['ENOTrelation']->notLiving.' > 0 '
                          .'   , jre.indNote '
                          .'   , false '
                          .'   ) AS indNote '
            );
            $query->select(' false AS indAltNote ');
            $query->select(
                ' IF( '.$displayAccess['ESOUrelation']->notLiving.' > 0 '
                          .'   , jre.indCitation '
                          .'   , false '
                          .'   ) AS indCitation '
            );
            $query->select(' false AS indAltSource ');
            $query->where(' jds.access IN '.$this->_levels.' ');
        } else {
            // at least one is still living
            $query->select(
                ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                          .'   , '.$this->_db->Quote(Text::_('JT_ALTERNATIVE')).' '
                          .'   , jre.eventDate '
                          .'   ) AS eventDate '
            );
            $query->select(
                ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                          .'   , NULL '
                          .'   , jre.location '
                          .'   ) AS location '
            );
            $query->select(
                ' IF( jds.accessLiving NOT IN '.$this->_levels.' '
                          .'   , NULL '
                          .'   , jre.value '
                          .'   ) AS value '
            );
            $query->select(
                ' IF( '.$displayAccess['ENOTrelation']->living.' > 0 '
                          .'   , jre.indNote '
                          .'   , false '
                          .'   ) AS indNote '
            );
            $query->select(
                ' IF( '.$displayAccess['ENOTrelation']->living.' = 1 '
                          .'   , true '
                          .'   , false '
                          .'   ) AS indAltNote '
            );
            $query->select(
                ' IF( '.$displayAccess['ESOUrelation']->living.' > 0 '
                          .'   , jre.indCitation '
                          .'   , false '
                          .'   ) AS indCitation '
            );
            $query->select(
                ' IF( '.$displayAccess['ESOUrelation']->living.' = 1 '
                          .'   , true '
                          .'   , false '
                          .'   ) AS indAltSource '
            );
            $query->where(
                ' (  jds.accessLiving IN '.$this->_levels.' '
                         . ' OR jds.altLiving    IN '.$this->_levels .' '
                         . ' ) '
            );
        }
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
        $query->bind(':relation', $relation_id, \Joomla\Database\ParameterType::STRING);

        $this->_db->setquery($query);

        $this->partnerEvents  = $this->_db->loadObjectList();
        return $this->partnerEvents;
    }

    private function retrieveSources($wheres)
    {
        $query =  $this->_db->getquery(true);
        // select from citations
        $query->select('DISTINCT jcn.* ');
        $query->from(' #__joaktree_citations      jcn ');
        // select from sources
        $query->select(' jse.title ');
        $query->select(' jse.author ');
        $query->select(' jse.publication ');
        $query->select(' jse.information ');
        $query->leftJoin(
            ' #__joaktree_sources        jse '
                           .' ON (   jse.app_id = jcn.app_id '
                        .'    AND jse.id     = jcn.source_id '
                        .'    ) '
        );
        $query->select('jnote.value as note_value');
        $query->leftJoin(
            ' #__joaktree_notes  jnote '
                           .' ON (   jnote.app_id = jse.app_id '
                        .'    AND jnote.id     = TRIM(BOTH "@" FROM jcn.note) '
                        .'    ) '
        );
        // select from repositories
        $query->select(' jry.name AS repository ');
        $query->select(' jry.website ');
        $query->leftJoin(
            ' #__joaktree_repositories   jry '
                        .' ON (   jry.app_id = jse.app_id '
                        .'    AND jry.id     = jse.repo_id '
                        .'    ) '
        );
        $query->leftJoin(
            ' #__joaktree_person_events   jevtp '
                        .' ON ( jcn.objectType = "personEvent"'
                        .'    AND jevtp.app_id = jse.app_id '
                        .'    AND jevtp.person_id     = jcn.person_id_1 '
                        .'    AND jevtp.orderNumber     = jcn.orderNumber '
                        .'    ) '
        );
        $query->leftJoin(
            ' #__joaktree_relation_events   jevtr '
                        .' ON ( jcn.objectType = "relationEvent"'
                        .'    AND jevtr.app_id = jse.app_id '
                        .'    AND jevtr.person_id_1     = jcn.person_id_1 '
                        .'    AND jevtr.orderNumber     = jcn.orderNumber '
                        .'    ) '
        );
        $query->select('jevtp.code as pcode,jevtp.type as ptype');
        $query->select('jevtr.code as rcode,jevtr.type as rtype');
        $query->select('jds.published,jds.accessLiving');
        $query->leftJoin(
            ' #__joaktree_display_settings  jds '
                         .' ON (   (   (jds.code = jevtp.code  AND jds.level = "person")'
                         .'         OR (jds.code = jevtr.code AND jds.level = "relation") )'
                         .'    AND jds.published = true '
                         .'    ) '
        );
        // WHERE and GROUP BY
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }
        $this->_db->setquery($query);
        $sources = $this->_db->loadObjectList();
        return $sources;
    }

    public function getSources($type, $orderNumber, $pid2)
    {
        $wheres = array();
        // depending on type, set the parameters
        if ($orderNumber) {
            $filter = '= '.$orderNumber.' ';
        } else {
            $filter = 'IS NULL ';
        }
        $wheres[] = ' jcn.app_id         = '.$this->app_id.' ';
        switch ($type) {
            case "personAll":
                $wheres[] = ' (  jcn.person_id_1 = '.$this->_db->Quote($this->id).' '
                           .' OR jcn.person_id_2 = '.$this->_db->Quote($this->id).' '
                           .' ) '
                           .' AND jcn.objectType = '.$this->_db->Quote("personEvent");
                break;
            case "person":
                $wheres[] = ' jcn.person_id_1 = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jcn.person_id_2 = '.$this->_db->Quote('EMPTY').' ';
                $wheres[] = ' jcn.objectType  = '.$this->_db->Quote('person').' ';
                break;
            case "pevent":
                $wheres[] = ' jcn.person_id_1 = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jcn.person_id_2 = '.$this->_db->Quote('EMPTY').' ';
                $wheres[] = ' jcn.objectType  = '.$this->_db->Quote('personEvent').' ';
                $wheres[] = ' jcn.objectOrderNumber '.$filter.' ';
                break;
            case "name":
                $wheres[] = ' jcn.person_id_1 = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jcn.person_id_2 = '.$this->_db->Quote('EMPTY').' ';
                $wheres[] = ' jcn.objectType  = '.$this->_db->Quote('personName').' ';
                $wheres[] = ' jcn.objectOrderNumber '.$filter.' ';
                break;
            case "note":
                $wheres[] = ' jcn.person_id_1 = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jcn.person_id_2 = '.$this->_db->Quote('EMPTY').' ';
                $wheres[] = ' jcn.objectType  = '.$this->_db->Quote('personNote').' ';
                $wheres[] = ' jcn.objectOrderNumber '.$filter.' ';
                break;
            case "relation":
                $wheres[] = ' ( (   jcn.person_id_1 = '.$this->_db->Quote($this->id).' '
                           .'   AND jcn.person_id_2 = '.$this->_db->Quote($pid2).' '
                           .'   ) OR '
                           .'   (   jcn.person_id_1 = '.$this->_db->Quote($pid2).' '
                           .'   AND jcn.person_id_2 = '.$this->_db->Quote($this->id).' '
                           .'   ) '
                           .' ) ';
                break;
            case "revent":
                $wheres[] = ' ( (   jcn.person_id_1 = '.$this->_db->Quote($this->id).' '
                           .'   AND jcn.person_id_2 = '.$this->_db->Quote($pid2).' '
                           .'   ) OR '
                           .'   (   jcn.person_id_1 = '.$this->_db->Quote($pid2).' '
                           .'   AND jcn.person_id_2 = '.$this->_db->Quote($this->id).' '
                           .'   ) '
                           .' ) ';
                $wheres[] = ' jcn.objectType        = '.$this->_db->Quote('relationEvent').' ';
                $wheres[] = ' jcn.objectOrderNumber '.$filter.' ';
                break;
        }
        $sources = $this->retrieveSources($wheres);
        return $sources;
    }

    private function retrieveNotes($level, $wheres)
    {
        $params		 = JoaktreeHelper::getJTParams();
        $titleLength = (int) $params->get('notetitlelength', 0);
        $query =  $this->_db->getquery(true);
        if ($level == 'person') {
            $tab  		= 'jpn';
            $tableName 	= '#__joaktree_person_notes '.$tab;
            $objectOrder = 'IFNULL(jpn.nameOrderNumber, jpn.eventOrderNumber) AS objectOrderNumber';
        } elseif ($level == 'relation') {
            $tab = 'jrn';
            $tableName 	= '#__joaktree_relation_notes '.$tab;
            $objectOrder = 'jrn.eventOrderNumber AS objectOrderNumber';
        }
        // prepare title
        // 1. Trim the title to "titlelength" after first space and remove , (if present)
        $tmp = ' TRIM( TRAILING '.$this->_db->Quote(',').' FROM '
              .'       RTRIM( SUBSTRING( IFNULL( jne.value '
              .'                               , '.$tab.'.value '
              .'                               ) '
              .'                       , 1 '
              .'                       , LOCATE( '.$this->_db->Quote(' ').' '
              .'                               , IFNULL( jne.value '
              .'                                       , '.$tab.'.value '
              .'                                       ) '
              .'                               , '.$titleLength.' '
              .'                               ) '
              .'                       ) '
              .'            ) '
              .'     ) ';
        // 2. Concatenat the trimmed text with ....
        $attribs = array();
        $attribs[] = $tmp;
        $attribs[] = $this->_db->Quote(' ...');
        $concat = $query->concatenate($attribs);
        // end prepare title
        // start selection
        $query->select(' '.$tab.'.orderNumber ');
        $query->select(' '.$objectOrder.' ');
        $query->select(
            ' IF( '.$titleLength.' = 0 '
                      .'   , '.$this->_db->Quote(Text::_('JT_NOTE')).' '
                      .'   , '.$concat.' '
                      .'   ) AS title '
        );
        $query->select(' IFNULL( jne.value, '.$tab.'.value ) AS text ');
        $query->select(' '.$tab.'.note_id  AS note_id ');
        $query->from(' '.$tableName.' ');
        $query->leftJoin(
            ' #__joaktree_notes              jne '
                        .' ON (   jne.app_id = '.$tab.'.app_id '
                        .'    AND jne.id     = '.$tab.'.note_id '
                        .'    ) '
        );
        // WHERE and ORDER BY
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }
        $query->order(' '.$tab.'.orderNumber ');
        $this->_db->setquery($query);
        $notes = $this->_db->loadObjectList();
        return $notes;
    }

    public function getMaxReference()
    {
        $query =  $this->_db->getquery(true);
        $query->select(' MAX(jcn.orderNumber) ');
        $query->from(' #__joaktree_citations  jcn ');
        $query->where(' jcn.app_id      = '.$this->app_id.' ');
        $query->where(' jcn.person_id_1 = '.$this->_db->Quote($this->id).' ');
        $query->where(' jcn.person_id_2 = '.$this->_db->Quote('EMPTY').' ');
        $this->_db->setquery($query);
        $maxNumber = $this->_db->loadResult();
        return (int) $maxNumber;
    }

    public function getMaxNote()
    {
        $query =  $this->_db->getquery(true);
        $query->select(' MAX(jpn.orderNumber) ');
        $query->from(' #__joaktree_person_notes  jpn ');
        $query->where(' jpn.app_id    = '.$this->app_id.' ');
        $query->where(' jpn.person_id = '.$this->_db->Quote($this->id).' ');
        $this->_db->setquery($query);
        $maxNumber = $this->_db->loadResult();
        return (int) $maxNumber;
    }

    public function getNotes($type, $orderNumber, $pid2)
    {
        $wheres = array();
        // depending on type, set the parameters
        if ($orderNumber) {
            $filter = '= '.$orderNumber.' ';
        } else {
            $filter = 'IS NULL ';
        }
        switch ($type) {
            case "person":
                $code 	= 'NOTE';
                $level 	= 'person';
                $wheres[] = ' jpn.app_id           = '.$this->app_id.' ';
                $wheres[] = ' jpn.person_id        = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jpn.nameOrderNumber  IS NULL ';
                $wheres[] = ' jpn.eventOrderNumber IS NULL ';
                break;
            case "pevent":
                $code 	= 'ENOT';
                $level 	= 'person';
                $wheres[] = ' jpn.app_id           = '.$this->app_id.' ';
                $wheres[] = ' jpn.person_id        = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jpn.nameOrderNumber  IS NULL ';
                $wheres[] = ' jpn.eventOrderNumber '.$filter.' ';
                break;
            case "name":
                $code 	= 'NOTE';
                $level 	= 'person';
                $wheres[] = ' jpn.app_id           = '.$this->app_id.' ';
                $wheres[] = ' jpn.person_id        = '.$this->_db->Quote($this->id).' ';
                $wheres[] = ' jpn.nameOrderNumber  '.$filter.' ';
                $wheres[] = ' jpn.eventOrderNumber IS NULL ';
                break;
            case "relation": // relation
                $code 	= 'NOTE';
                $level 	= 'relation';
                $wheres[] = ' jrn.app_id           = '.$this->app_id.' ';
                $wheres[] = ' ( (   jrn.person_id_1 = '.$this->_db->Quote($this->id).' '
                           .'   AND jrn.person_id_2 = '.$this->_db->Quote($pid2).' '
                           .'   ) OR '
                           .'   (   jrn.person_id_1 = '.$this->_db->Quote($pid2).' '
                           .'   AND jrn.person_id_2 = '.$this->_db->Quote($this->id).' '
                           .'   ) '
                           .' ) ';
                $wheres[] = ' jrn.eventOrderNumber IS NULL ';
                break;
            case "revent":
                $code 	= 'ENOT';
                $level 	= 'relation';
                $wheres[] = ' jrn.app_id           = '.$this->app_id.' ';
                $wheres[] = ' ( (   jrn.person_id_1 = '.$this->_db->Quote($this->id).' '
                           .'   AND jrn.person_id_2 = '.$this->_db->Quote($pid2).' '
                           .'   ) OR '
                           .'   (   jrn.person_id_1 = '.$this->_db->Quote($pid2).' '
                           .'   AND jrn.person_id_2 = '.$this->_db->Quote($this->id).' '
                           .'   ) '
                           .' ) ';
                $wheres[] = ' jrn.eventOrderNumber '.$filter.' ';
                break;
        }
        $notes = $this->retrieveNotes($level, $wheres);
        return $notes;
    }

    public function getLineage()
    {
        // initialize
        static $lineageArray = array();
        // get parameters
        $params         	= JoaktreeHelper::getJTParams();
        $lineageSetting		= (int) $params->get('lineage');
        $i = 0;
        $reference_fam_name = '';
        if (($lineageSetting != 0) and (count($lineageArray) == 0)) {
            $elements  = explode(" ", $this->lineage ?? '');
            foreach ($elements as $element_num => $lineage_id) {
                if ($lineage_id != null) {
                    $id[ 'app_id' ]		= $this->app_id;
                    $id[ 'person_id' ]	= $lineage_id;
                    $id[ 'tree_id' ]	= $this->tree_id ;
                    //$tmp = new joaktreeperson($id);
                    $tmp = new Person($id, 'full');
                    $lineageArray[ $i ][ 'app_id' ]  	= $this->app_id;
                    $lineageArray[ $i ][ 'person_id' ]  = $lineage_id;
                    $lineageArray[ $i ][ 'tree_id' ]    = $tmp->tree_id;
                    $lineageArray[ $i ][ 'menuItemId' ] = $tmp->menuItemId;
                    $firstName = explode("(", $tmp->firstName);
                    $lineageArray[ $i ][ 'firstName' ] = rtrim($firstName[0]);
                    if ($lineageSetting == 1) {
                        // Only first names
                        $lineageArray[ $i ][ 'familyName' ] = null;
                    } elseif ($lineageSetting == 2) {
                        // First names + family names
                        $lineageArray[ $i ][ 'familyName' ] = $tmp->familyName;
                    } elseif ($lineageSetting == 3) {
                        // First names + family names when different than previous
                        if ($tmp->familyName == $reference_fam_name) {
                            $lineageArray[ $i ][ 'familyName' ] = null;
                        } else {
                            $reference_fam_name = $tmp->familyName;
                            $lineageArray[ $i ][ 'familyName' ] = $tmp->familyName;
                        }
                    }
                    $i++;
                }
            }
        }
        return $lineageArray;
    }

    private function getArticlequery()
    {
        $params     = JoaktreeHelper::getJTParams();
        $indArticleLink = (int) $params->get('indArticleLink', 9);
        if ($indArticleLink != 0) {
            $nullDate	= $this->_db->getNullDate();
            $date		= Factory::getDate();
            $now  		= $date->toSql();
            $likes 		= array();
            $keys		= array();
            switch ($indArticleLink) {
                case 1:		// By ID
                    $keys[]		= $this->id;
                    $keys[]		= $this->app_id.'!'.$this->id;
                    break;
                case 2:		// By first name
                    $keys[]		= $this->firstName;
                    break;
                case 3:		// By family name
                    $keys[]		= $this->familyName;
                    break;
                case 4:		// By first + family name
                    $keys[]		= $this->firstName.' '.$this->familyName;
                    break;
                case 5:		// All names
                    $keys[]		= $this->firstName;
                    $keys[]		= $this->familyName;
                    $keys[]		= $this->firstName.' '.$this->familyName;
                    break;
                case 9:		// All options
                    $keys[]		= $this->id;
                    $keys[]		= $this->app_id.'!'.$this->id;
                    $keys[]		= $this->firstName;
                    $keys[]		= $this->familyName;
                    $keys[]		= $this->firstName.' '.$this->familyName;
                    break;
                case 0: 	// No linking
                    // no keys
                default:	// unknown value -> no linking
                    // no keys
                    break;
            }
            // assemble any non-blank word(s)
            foreach ($keys as $key) {
                $key = trim($key);
                if ($key) {
                    // surround with commas so first and last items have surrounding commas
                    $likes[] 	= ',' . $this->_db->escape($key) . ',';
                }
            }
            // process the "likes" - search in keyword of articles
            if (count($likes)) {
                $query 		= $this->_db->getquery(true);
                // prepare concats
                $attribs1 = array();
                $attribs1[] = 'a.id';
                $attribs1[] = 'a.alias';
                $concat1 = $query->concatenate($attribs1, ':');
                $attribs2 = array();
                $attribs2[] = 'cc.id';
                $attribs2[] = 'cc.alias';
                $concat2 = $query->concatenate($attribs2, ':');
                //remove single space after commas in keywords
                $attribs3 = array();
                $attribs3[] = '","';
                $attribs3[] = 'REPLACE( a.metakey, ", ", "," )';
                $attribs3[] = '","';
                $concat3 = $query->concatenate($attribs3, ',');
                // end concats
                // select from content
                $query->select(' a.id ');
                $query->select(' a.title ');
                $query->select(' DATE_FORMAT(a.created, "%Y-%m-%d") AS created ');
                $query->select(' a.catid ');
                $query->select(
                    ' CASE WHEN CHAR_LENGTH(a.alias) '
                              .'      THEN '.$concat1.' '
                              .'      ELSE a.id '
                              .' END  AS slug '
                );
                $query->from(' #__content       AS a ');
                $query->where(' a.state           = 1 ');
                $query->where(' a.access         IN ' .$this->_levels .' ');
                $query->where(
                    ' ( '.$concat3.' LIKE "%'
                               .implode(
                                   '%" OR '.$concat3.' LIKE "%',
                                   $likes
                               ).'%" '
                             . ' ) '
                );
                $query->where(
                    ' (   a.publish_up   =  '.$this->_db->Quote($nullDate).' '
                             .'  OR  a.publish_up   <= '.$this->_db->Quote($now).' '
                             .'  ) '
                );
                $query->where(
                    ' (   a.publish_down =  '.$this->_db->Quote($nullDate).' '
                             .'  OR  a.publish_down >= '.$this->_db->Quote($now).' '
                             .'  ) '
                );
                // Filter by language
                if ($this->languageFilter) {
                    $query->where('a.language in  ('.$this->_db->Quote(Factory::getApplication()->getLanguage()->getTag())
                                                .','.$this->_db->Quote('*')
                                                .') ');
                }
                // select from categories
                $query->select(' cc.access        AS cat_access ');
                $query->select(' cc.published     AS cat_state ');
                $query->select(
                    ' CASE WHEN CHAR_LENGTH(cc.alias) '
                              .'      THEN '.$concat2.' '
                              .'      ELSE cc.id '
                              .' END  AS catslug '
                );
                $query->leftJoin(
                    ' #__categories    AS cc '
                                .' ON (   cc.id        = a.catid '
                                .'    AND cc.published = 1 '
                                .'    AND cc.access    IN ' .$this->_levels .' '
                                .'    ) '
                );
                // ORDER BY
                $query->order(' a.created DESC ');
            } else {
                $query = null;
            }
        } else {
            $query = null;
        }
        return $query;
    }

    public function getArticleCount()
    {
        static $articleCount;
        if (!isset($articleCount)) {
            $articleCount = 0;
            $query = $this->getArticlequery();
            if ($query != null) {
                $this->_db->setquery($query);
                $this->_db->execute(); //$this->_db->query();
                $articleCount = $this->_db->getNumRows();
            }
        }
        return $articleCount;
    }

    public function getArticleList()
    {
        static $related;
        if (!isset($related)) {
            $related	= array();
            $query 		= $this->getArticlequery();
            $userAccess = $this->getUserAccess();
            if ($query != null) {
                $this->_db->setquery($query);
                $temp = $this->_db->loadObjectList();
                if (count($temp)) {
                    foreach ($temp as $row) {
                        if (($row->cat_state == 1 || $row->cat_state == '')
                            &&  (in_array($row->cat_access, $userAccess) || $row->cat_access == '')
                        ) {
                            $related[] = $row;
                        }
                    }
                }
                unset($temp);
            }
        }
        return $related;
    }

    public function getArticle($id, $app_id, $person_id, $type)
    {
        $articleId = (int) $id;
        $app_id    = (int) $app_id;
        $person_id = $this->_db->escape(substr($person_id, 0, (int) JoaktreeHelper::getIdlength()));
        $query 		= $this->_db->getquery(true);
        if ($type == 'article') {
            // select from content
            $query->select(' a.id             AS id ');
            $query->select(' a.title          AS title ');
            $query->select(' a.introtext      AS introtext ');
            $query->select(' a.fulltext       AS \'fulltext\' ');
            $query->select(' DATE_FORMAT( a.modified, "%e %b %Y" ) AS modified  ');
            $query->select(' a.attribs        AS attribs ');
            $query->from(' #__content       AS a ');
            $query->where(' a.state           = 1 ');
            $query->where(' a.access         IN ' .$this->_levels .' ');
            $query->where(' a.id             = :articleid');
            // Filter by language
            if ($this->languageFilter) {
                $query->where('a.language in  ('.$this->_db->Quote(Factory::getApplication()->getLanguage()->getTag())
                                            .','.$this->_db->Quote('*')
                                            .') ');
            }
            // select from categories
            $query->select(' cc.id            AS cat_id ');
            $query->leftJoin(
                ' #__categories    AS cc '
                            .' ON (   cc.id        = a.catid '
                            .'    AND cc.published = 1 '
                            .'    AND cc.access    IN ' .$this->_levels .' '
                            .'    ) '
            );
        } elseif ($type == 'note') {
            $params		 = JoaktreeHelper::getJTParams();
            $titleLength = (int) $params->get('notetitlelength', 0);
            // prepare title
            // 1. Trim the title to "titlelength" after first space and remove , (if present)
            $tmp = ' TRIM( TRAILING '.$this->_db->Quote(',').' FROM '
                  .'       RTRIM( SUBSTRING( IFNULL( jne.value, jpn.value ) '
                  .'                       , 1 '
                  .'                       , LOCATE( '.$this->_db->Quote(' ').' '
                  .'                               , IFNULL( jne.value, jpn.value ) '
                  .'                               , '.$titleLength.' '
                  .'                               ) '
                  .'                       ) '
                  .'            ) '
                  .'     ) ';
            // 2. Concatenat the trimmed text with ....
            $attribs = array();
            $attribs[] = $tmp;
            $attribs[] = $this->_db->Quote(' ...');
            $concat = $query->concatenate($attribs);
            // end prepare title
            $query->select(' jpn.orderNumber                 AS id ');
            $query->select(
                ' IF( '.$titleLength.' = 0 '
                          .'   , '.$this->_db->Quote(Text::_('JT_NOTE')).' '
                          .'   , '.$concat.' '
                          .'   )  AS title '
            );
            $query->select(' IFNULL( jne.value, jpn.value )  AS introtext ');
            $query->select(' NULL                            AS \'fulltext\' ');
            $query->select(' jpn.person_id                   AS cat_id ');
            $query->select(' NULL                            AS modified ');
            $query->from(' #__joaktree_person_notes        AS jpn ');
            $query->leftJoin(
                ' #__joaktree_notes               AS jne '
                            .' ON (   jne.app_id = jpn.app_id '
                            .'    AND jne.id     = jpn.note_id '
                            .'    ) '
            );
            $query->where(' jpn.app_id       = :appid');
            $query->where(' jpn.person_id    = :personid');
            $query->where(' jpn.orderNumber  = :articleid');
        }
        $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $person_id, \Joomla\Database\ParameterType::STRING);
        $query->bind(':articleid', $articleId, \Joomla\Database\ParameterType::INTEGER);

        $this->_db->setquery($query);
        $article = $this->_db->loadObject();
        // Convert parameter fields to objects for article.
        if (isset($article->attribs)) {
            $registry = new Registry();
            $registry->loadString($article->attribs);
            $indIntrotext = $registry->get('show_intro', '1');
        } else {
            $indIntrotext = '0';
        }

        // Are we showing introtext with the article
        if ($indIntrotext == '1') {
            $article->text = $article->introtext. chr(13).chr(13) . $article->fulltext;
        } elseif (!empty($article->fulltext)) {
            $article->text = $article->fulltext;
        } else {
            $article->text = $article->introtext;
        }
        // formating last update date
        if (!empty($article->modified)) {
            $article->modified = JoaktreeHelper::lastUpdateDateTimePerson($article->modified);
        }
        if ($type == 'article') {
            // check content using the content plugin - if it is available
            if (PluginHelper::isEnabled('content', 'joaktree')) {
                $params = array();
                PluginContentJoaktree::onContentPrepare('com_content', $article, $params);
            }
        }

        return $article;
    }

    public function getPictures($all = false)
    {
        static $picArray;
        if (!isset($picArray)) {
            $params			= JoaktreeHelper::getJTParams();
            $sequence		= $params->get('Sequence', 0);
            $docsFromGedcom	= (int) $params->get('indDocuments', 0);
            $ds				= '/';
            $imagesPath		= ComponentHelper::getParams('com_media')->get('image_path', 'images');
            $code = "IMAG";
            $level = "person";
            $published = 1;
            $query 	  = $this->_db->getquery(true);
            $query->select('access,accessLiving')
                  ->from(' #__joaktree_display_settings  jds ')
                  ->where('jds.code  = :code')
                  ->where('jds.level = :level')
                  ->where('jds.published = :published')
                  ->where('jds.access IN '.$this->_levels.' ')
                  ->where('jds.accessLiving IN '.$this->_levels.' ');
            $query->bind(':code', $code, \Joomla\Database\ParameterType::STRING);
            $query->bind(':level', $level, \Joomla\Database\ParameterType::STRING);
            $query->bind(':published', $published, \Joomla\Database\ParameterType::INTEGER);
            $this->_db->setquery($query);
            $result = $this->_db->loadResult();
            // sequence == 0: Disable pictures
            $picArray = array();
            if (!$result) { // not allowed
                $sequence = 0;
            }
            $query 	  = $this->_db->getquery(true);
            if ($sequence != 0) {
                // docsFromGedcom = 1 => get the pictures from the GedCom file (document table)
                if ($docsFromGedcom == 1) {
                    $gedcomroot = $params->get('gedcomDocumentRoot', '');
                    $joomlaroot = $params->get('joomlaDocumentRoot', '');
                    $query->select(' jdt.* ');
                    $query->from(' #__joaktree_documents  jdt ');
                    $query->where(' jdt.app_id = :appid ');
                    $query->where(
                        ' UPPER(jdt.fileformat) IN '
                                  .'   ('.$this->_db->quote('GIF').' '
                                  .'   ,'.$this->_db->quote('JPG').' '
                                  .'   ,'.$this->_db->quote('JPEG').' '
                                  .'   ,'.$this->_db->quote('PNG').' '
                                  .'   ,'.$this->_db->quote('BMP').' '
                                  .'   ,'.$this->_db->quote('WEBP').' '
                                  .'   ) '
                    );
                    $query->innerJoin(
                        ' #__joaktree_person_documents  jpd '
                                     .' ON (   jpd.app_id      = jdt.app_id '
                                     .'    AND jpd.document_id = jdt.id '
                                     .'    AND jpd.person_id   = :personid'
                                     .'    ) '
                    );
                    $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
                    $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

                    $this->_db->setquery($query);
                    $result = $this->_db->loadObjectList();
                    foreach ($result as $pic_i => $picture) {
                        $apic = explode('#', $picture->file); // Joomla 4/5 add # file info to name
                        $picture->file = $apic[0]; // just keep image name
                        if (($gedcomroot) && ($joomlaroot)) {
                            $picture->file = str_replace($gedcomroot, $joomlaroot, $picture->file);
                        } elseif (($gedcomroot) && (!$joomlaroot)) {
                            $picture->file = str_replace($gedcomroot, '', $picture->file);
                        } elseif ((!$gedcomroot) && ($joomlaroot)) {
                            $picture->file = $joomlaroot.'/'.$picture->file;
                        } else {
                            $picture->file = $picture->file;
                        }
                        // remove windows style backslashes
                        $picture->file = str_replace('\\', '/', $picture->file);
                        $position = strpos(str_replace('\\', '/', $joomlaroot), $imagesPath.'/');
                        if (($position === 0) || ($position > 0)) {
                            $picture->base = substr(str_replace('\\', '/', $joomlaroot), ($position + strlen($imagesPath.'/')));
                        } else {
                            $picture->base = str_replace('\\', '/', $joomlaroot);
                        }
                        if ($all) {
                            array_push($picArray, $picture);
                        } else {
                            if (@is_file($picture->file)) {
                                array_push($picArray, $picture);
                            }
                        }
                    }

                } else {
                    // docsFromGedcom = 0 => get the pictures the standard Joaktree way
                    $base 			= $params->get('Directory', $imagesPath.'/'.'joaktree');
                    if (is_dir($base.'/'.$this->app_id.'!'.$this->id)) {
                        $directory 	= $base.'/'.$this->app_id.'!'.$this->id;
                        $appId		= $this->app_id;
                    } elseif (is_dir($base.'/'.$this->id)) {
                        $directory = $base.'/'.$this->id;
                        $appId	= null;
                    } else {
                        $query->select(' name ');
                        $query->select(' app_id ');
                        $query->from(' #__joaktree_trees ');
                        $query->where(' id = :treeid');
                        $query->bind(':treeid', $this->tree_id, \Joomla\Database\ParameterType::INTEGER);

                        $this->_db->setquery($query);
                        $result				= $this->_db->loadObject();
                        $directory = $result->name;
                        $directory = $base.'/'.$directory;
                        $appId	= $result->app_id;
                        if (!is_dir($directory)) {
                            // if no directories are found, take the default directory with pictures
                            $directory = 'images'.'/'.'joaktree'.'/'.'jt-images';
                            $appId	= null;
                        }
                    }
                    if (is_dir($directory)) {
                        if ($dh = opendir($directory)) {
                            while ($file = readdir($dh)) {
                                $uprFile = strtoupper($file);
                                if ($uprFile != '.' && $uprFile != '..') {
                                    if (strpos($uprFile, '.GIF', 1)
                                       || strpos($uprFile, '.JPG', 1)
                                       || strpos($uprFile, '.JPEG', 1)
                                       || strpos($uprFile, '.PNG', 1)
                                       || strpos($uprFile, '.BMP', 1)
                                       || strpos($uprFile, '.WEBP', 1)
                                    ) {
                                        $picture 				= new \StdClass();
                                        $position = strpos(str_replace('\\', '/', $base), $imagesPath.'/');
                                        if (($position === 0) || ($position > 0)) {
                                            $picture->base = substr(str_replace('\\', '/', $base), ($position + strlen($imagesPath.'/')));
                                        } else {
                                            $picture->base = str_replace('\\', '/', $base);
                                        }
                                        $position = strpos(str_replace('\\', '/', $directory), $imagesPath.'/');
                                        if (($position === 0) || ($position > 0)) {
                                            $picture->directory = substr(str_replace('\\', '/', $directory), ($position + strlen($imagesPath.'/')));
                                        } else {
                                            $picture->directory = str_replace('\\', '/', $directory);
                                        }
                                        $picture->app_id 		= $appId;
                                        $picture->id			= null;
                                        $picture->indCitation	= false;
                                        $picture->note_id 		= null;
                                        $picture->note 			= null;
                                        $picture->file  		= str_replace('\\', '/', $directory).'/'.$file;
                                        $tmp = explode('.', $file);
                                        $picture->title 		= $tmp[0];
                                        $picture->fileformat 	= strtoupper($tmp[(count($tmp) - 1)]);
                                        unset($tmp);
                                        array_push($picArray, $picture);
                                        unset($picture);
                                    }
                                }
                            }
                            closedir($dh);
                        }
                    }
                }
                if (is_array($picArray) && count($picArray)) {
                    switch ($sequence) {
                        case 1: // Natural sort
                            //	natsort($picArray);
                            //	break;
                        case 2:	// Shuffle at beginning
                        case 3: // Shuffle at beginning (take later the first element only)
                        case 4: // Shuffle at beginning (take later the first element only)
                        default:
                            shuffle($picArray);
                            break;
                    }
                }
            }
        }
        return $picArray;
    }

    public function getStaticMap()
    {
        if ($this->map == 1) {
            $items				= array();
            // find locations in person events
            $events				= $this->getPersonEvents();
            foreach ((array) $events as $event) {
                if ((isset($event->longitude)) && (!empty($event->longitude))
                   && (isset($event->latitude)) && (!empty($event->latitude))
                ) {
                    $tmp     = new \StdClass(); 	//JObject;
                    $tmp->longitude = $event->longitude;
                    $tmp->latitude  = $event->latitude;
                    $tmp->label		= Text::_($event->code);
                    array_push($items, $tmp);
                    unset($tmp);
                }
            }
            if (count($items)) {
                // get parameters
                $params				= JoaktreeHelper::getJTParams();
                $options			= array();
                $options['width']	= (int) $params->get('pxMapWidth', 700);
                $options['height']	= (int) $params->get('pxHeight', 225);
                $options['color']	= $params->get('statMarkerColor');
                $service			= MBJStaticmap::getInstance();
                $map				= $service->_('fetch', $items, $options);
                return $map;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getInteractiveMap()
    {
        $count = 0;
        // find locations in person events
        $events				= $this->getPersonEvents();
        foreach ((array) $events as $event) {
            if ((isset($event->longitude)) && (!empty($event->longitude))
               && (isset($event->latitude)) && (!empty($event->latitude))
            ) {
                $count++;
            }
        }
        if ($count) {
            return 'index.php?option=com_joaktree'
                   .'&tmpl=component'
                   .'&format=raw'
                   .'&view=interactivemap'
                   .'&personId='.$this->app_id.'!'.$this->id;
        } else {
            return false;
        }
    }
}

?>