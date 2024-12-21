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
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class TodaymanyyearsagoModel extends BaseDatabaseModel
{
    public $_title;
    public $_sorting;
    public $_buttonText;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->_db 		= $this->_db;
    }

    private function getModuleParams($moduleId)
    {
        static $params;

        if (!isset($params)) {
            $app	  = Factory::getApplication();
            $clientid = (int) $app->getClientId();
            $userAccessLevels = JoaktreeHelper::getUserAccessLevels();

            if (!isset($moduleId) || $moduleId == 0) {
                $moduleId = JoaktreeHelper::getModuleId();
            } else {
                $moduleId = (int) $moduleId;
            }

            $query = $this->_db->getquery(true);
            $query->select(' m.id ');
            $query->select(' m.params ');
            $query->from(' #__modules AS m ');
            $query->where(' m.published =  1 ');
            $query->where(' m.access    IN '.$userAccessLevels.' ');
            $query->where(' m.client_id =  '.$clientid.' ');
            $query->where(' m.module = '.$this->_db->Quote('mod_joaktree_todaymanyyearsago').' ');

            $this->_db->setquery($query);
            $temp = $this->_db->loadObjectList();

            $params = new Registry();
            foreach ($temp as $module) {
                if ($module->id == $moduleId) {
                    $params->loadString($module->params, 'JSON');
                }
            }
        }

        return $params;
    }


    private function getYears($todayYear)
    {
        static $listOfYears;

        if (!isset($listOfYears)) {
            // get the list of years
            $params = $this->getModuleParams(null);
            $years = explode(',', $params->get('listofyears'));
            foreach ($years as $key => $year) {
                $years[$key] = $this->_db->quote($todayYear - (int) trim($year));
            }
            $listOfYears = '('.implode(',', $years).')';
        }

        return $listOfYears;
    }

    private function getExcludedEvents()
    {
        static $listOfEvents;

        if (!isset($listOfEvents)) {
            $params = $this->getModuleParams(null);
            $displayAccess = JoaktreeHelper::getDisplayAccess();
            $events = array();

            foreach ($displayAccess as $row) {
                $eventCode = $params->get($row->gedcomtag);
                if ($eventCode) {
                    $events[] = $this->_db->Quote($row->gedcomtag);
                }
            }

            if (count($events) > 0) {
                $listOfEvents = 'jet.code NOT IN ('.implode(',', $events).') ';
            }
        }

        return $listOfEvents;
    }

    private function _buildContentWhere($day, $month, $todayYear)
    {
        $params 		= $this->getModuleParams(null);
        $periodType 	= $params->get('periodType', 1);

        $where = array();


        // get the abbreviation of the month name
        switch ($month) {
            case 1:	$monthDesc = 'JAN';
                break;
            case 2:	$monthDesc = 'FEB';
                break;
            case 3:	$monthDesc = 'MAR';
                break;
            case 4:	$monthDesc = 'APR';
                break;
            case 5:	$monthDesc = 'MAY';
                break;
            case 6:	$monthDesc = 'JUN';
                break;
            case 7:	$monthDesc = 'JUL';
                break;
            case 8:	$monthDesc = 'AUG';
                break;
            case 9:	$monthDesc = 'SEP';
                break;
            case 10:	$monthDesc = 'OCT';
                break;
            case 11:	$monthDesc = 'NOV';
                break;
            case 12:	$monthDesc = 'DEC';
                break;
            default:	$monthDesc = 'JAN';
                break;
        }

        switch ($periodType) {
            case 0:	// continue
            case 1: // search for specific day
                if ((isset($day)) || ($day > 0)) {
                    // show "this day" and search for specific day
                    $where[] = 'UPPER( jet.eventdate ) LIKE '.$this->_db->quote('%'.$day.' '.$monthDesc.'%').' ';

                    if (isset($day) && (int) $day < 10) {
                        $where[] = 'UPPER( jet.eventdate ) NOT LIKE '.$this->_db->quote('%1'.$day.' '.$monthDesc.'%').' ';
                        $where[] = 'UPPER( jet.eventdate ) NOT LIKE '.$this->_db->quote('%2'.$day.' '.$monthDesc.'%').' ';
                        $where[] = 'UPPER( jet.eventdate ) NOT LIKE '.$this->_db->quote('%3'.$day.' '.$monthDesc.'%').' ';
                    }
                    break;
                } else {
                    // day is not set .. we continue as were it a search for a month
                    // so no break
                }

                // no break
            case 2: // continue
            case 3: // search for a specific month
                $where[] = 'UPPER( jet.eventdate ) LIKE '.$this->_db->quote('%'.$monthDesc.'%').' ';
                break;

            case 4: // continue
            case 5: // search for a specific week
                $daydate = strtotime(sprintf("%4s-%02s-%02s", $todayYear, $month, $day));
                $week 	 = date('W', $daydate);
                $weekdays = array();
                for ($i = -7; $i < 15; $i++) {
                    $checkdate = $daydate + ($i * 24 * 60 * 60);
                    if ($week == date('W', $checkdate)) {
                        $weekdays[] = date('md', $checkdate);
                    }
                }

                $where[] = 'DATE_FORMAT(STR_TO_DATE( jet.eventdate, "%d %M %Y" ), "%m%d") IN ('.implode(',', $weekdays).') ';
                break;
            default: break;
        }

        switch ($periodType) {
            case 0:	// continue
            case 2: // continue
            case 4: // search only within the defined set of years
                $listOfYears = $this->getYears($todayYear);
                $where[] = 'SUBSTR( RTRIM(jet.eventdate), -4 ) IN '.$listOfYears.' ';
                break;

            case 1: // continue
            case 3: // continue
            case 5: // continue
            default: break;
        }

        // limit to selected gedcom's
        $appIds = (array) $params->get('appId');
        if (count($appIds)) {
            foreach ($appIds as $appId) {
                $where[] = 'jet.app_id = '.$appId.' ';
            }
        }

        // exludes certain events
        $tmp = $this->getExcludedEvents();
        if ($tmp) {
            $where[] = $tmp;
        }

        // exludes periode and description dates
        $where[] = 'STR_TO_DATE( jet.eventdate, "%d %M %Y" ) IS NOT NULL ';

        // we are done
        $wheres	= (count($where) ? ' WHERE '. implode(' AND ', $where) : '');
        return $wheres;
    }

    private function _buildOrderBy()
    {
        $params 	= $this->getModuleParams(null);
        $orderType 	= $params->get('sorting', 0);
        $this->_sorting = $orderType;

        switch ($orderType) {
            case 1: $order = 'ORDER BY  sortingdate ASC '
                            .',         eventyear   ASC '
                            .',         familyName  ASC '
                            .',         name        ASC ';
                break;
            case 0:	// continue
            default:
                $order = 'ORDER BY  eventyear   ASC '
                        .',         sortingdate ASC '
                        .',         familyName  ASC '
                        .',         name        ASC ';
                break;
        }

        return $order;
    }


    public function getDay()
    {
        $day    = JoaktreeHelper::getDay();

        if (!isset($day) || ($day == 0)) {
            $today = getdate();
            $day   = $today['mday'];
        }

        return $day;
    }

    public function getMonth()
    {
        $month  = JoaktreeHelper::getMonth();

        if (!isset($month) || ($month == 0)) {
            $today = getdate();
            $month = $today['mon'];
        }

        return $month;
    }

    public function getList($moduleId)
    {
        $moduleId   		= (int) $moduleId;
        $params 			= $this->getModuleParams($moduleId);
        $periodType 		= $params->get('periodType');
        $limit  			= (int) $params->get('searchlimit', 10);
        $userAccessLevels 	= JoaktreeHelper::getUserAccessLevels();
        $personStatus 		= $params->get('personStatus', 0);
        $showTitle			= $params->get('showHeading', 1);
        $today 				= getdate();

        if ($params->get('freeChoice')) {
            // only when user is allowed to enter a day/month we are checking these values.
            $day    = JoaktreeHelper::getDay();
            $month  = JoaktreeHelper::getMonth();
        }

        // for titles we want to know if we have day and/or months from the front end
        $indUI = ((!isset($day) && !isset($month)) || ($day == 0 && $month == 0)) ? false : true;

        // if day and/or month is not set, we use today's info
        $day   = (!isset($day)   || ($day == 0)) ? $today['mday'] : $day;
        $month = (!isset($month) || ($month == 0)) ? $today['mon'] : $month;

        if ($showTitle == 1) {
            if (!$indUI) {
                // nothing from front end -> use standard titles
                switch ($periodType) {
                    case 2:	// continue
                    case 3: $this->_title = Text::_('JTMOD_TMYA_HEADING_THISMONTH');
                        break;
                    case 4:	// continue
                    case 5: $this->_title = Text::_('JTMOD_TMYA_HEADING_THISWEEK');
                        break;
                    case 0:	// continue
                    case 1: // continue
                    default: $this->_title = Text::_('JTMOD_TMYA_HEADING_TODAY');
                        break;
                }
            } else {
                // use information from front end
                switch ($periodType) {
                    case 2:	// continue
                    case 3: $this->_title = Text::_($this->getMonthName($month));
                        break;
                    case 4:	// continue
                    case 5: // continue
                    case 0:	// continue
                    case 1: // continue
                    default: $this->_title = $day.'&nbsp;'.Text::_($this->getMonthName($month));
                        break;
                }
            }
        } else {
            $this->_title = null;
        }

        switch ($periodType) {
            case 2:	// continue
            case 3: $this->_buttonText = Text::_('JTMOD_TMYA_HEADING_THISMONTH');
                break;
            case 4:	// continue
            case 5: $this->_buttonText = Text::_('JTMOD_TMYA_HEADING_THISWEEK');
                break;
            case 0:	// continue
            case 1: // continue
            default: $this->_buttonText = Text::_('JTMOD_TMYA_HEADING_TODAY');
                break;
        }


        $result = array();
        $displayAccess = JoaktreeHelper::getDisplayAccess();
        $userAccess    = JoaktreeHelper::getUserAccess();
        $where		   = $this->_buildContentWhere($day, $month, $today['year']);
        $order		   = $this->_buildOrderBy();

        // retrieve person events
        $query = '( SELECT  CONCAT_WS('.$this->_db->quote(' ').' '
                .'                   , jpn.firstName '
                .'                   , jpn.namePreposition '
                .'                   , jpn.familyName '
                .'                   )               AS name '
                .',         jet.app_id               AS appId '
                .',         jet.person_id            AS personId '
                .',         jan.default_tree_id      AS treeId '
                .',			IF ( ( jet.code = '.$this->_db->Quote('EVEN').' ) '
                .'             , IFNULL( jet.type, jet.code ) '
                .'             , jet.code '
                .'             )                     AS code '
                .',         jet.eventdate '
                .',         DATE_FORMAT(STR_TO_DATE(eventdate, "%d %M %Y"), "%m%d") AS sortingdate '
                .',         SUBSTR( RTRIM(jet.eventdate), -4 ) AS eventyear '
                //.',         DATE_FORMAT(STR_TO_DATE(eventdate, "%d %M %Y"), "%e %M") AS eventday '
                .',         jpn.familyName           AS familyName '
                .'FROM      #__joaktree_person_events     jet '
                .'JOIN      #__joaktree_persons           jpn '
                .'ON        (   jpn.app_id = jet.app_id '
                .'          AND jpn.id     = jet.person_id '
                .'          ) '
                .'JOIN      #__joaktree_admin_persons     jan '
                .'ON        (   jan.app_id    = jpn.app_id '
                .'          AND jan.id        = jpn.id '
                .'          AND jan.published = true '
                .           (($personStatus == 0) ? 'AND jan.living = false ' : ' ')
                .           (($personStatus == 1) ? 'AND jan.living = true ' : ' ')
                // privacy filter
                .'          AND (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' = 2 ) '
                .'              OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    = 2 ) '
                .'              ) '
                .'          ) '
                .'JOIN      #__joaktree_trees             jte '
                .'ON        (   jte.app_id    = jan.app_id '
                .'          AND jte.id        = jan.default_tree_id '
                .'          AND jte.published = true '
                .'          AND jte.access    IN '.$userAccessLevels.' '
                .'          ) '
                .'JOIN      #__joaktree_display_settings  jds '
                .'ON        (   jds.code = jet.code '
                .'          AND jds.level = '.$this->_db->quote('person').' '
                .'          AND jds.published = true '
                .'          AND (  (jan.living = false AND jds.access       IN '.$userAccessLevels.') '
                .'              OR (jan.living = true  AND jds.accessLiving IN '.$userAccessLevels.') '
                .'              ) '
                .'          ) '
                .$where.' ) '
                .'UNION '
                // select relation events
                .'( SELECT  CONCAT_WS('.$this->_db->quote(' + ').' '
                .'                   , CONCAT_WS('.$this->_db->quote(' ').' '
                .'                              , jpn1.firstName '
                .'                              , jpn1.namePreposition '
                .'                              , jpn1.familyName '
                .'                              ) '
                .'                   , CONCAT_WS('.$this->_db->quote(' ').' '
                .'                              , jpn2.firstName '
                .'                              , jpn2.namePreposition '
                .'                              , jpn2.familyName '
                .'                              ) '
                .'                   )               AS name '
                .',         jet.app_id               AS appId '
                .',         jet.person_id_1          AS personId '
                .',         jan1.default_tree_id     AS treeId '
                .',			IF ( ( jet.code = '.$this->_db->Quote('EVEN').' ) '
                .'             , IFNULL( jet.type, jet.code ) '
                .'             , jet.code '
                .'             )                     AS code '
                .',         jet.eventdate '
                .',         DATE_FORMAT(STR_TO_DATE(eventdate, "%d %M %Y"), "%m%d") AS sortingdate '
                .',         SUBSTR( RTRIM(jet.eventdate), -4 ) AS eventyear '
                //.',         DATE_FORMAT(STR_TO_DATE(eventdate, "%d %M %Y"), "%e %M") AS eventday '
                .',         jpn1.familyName          AS familyName '
                .'FROM      #__joaktree_relation_events   jet '
                // first partner
                .'JOIN      #__joaktree_persons           jpn1 '
                .'ON        (   jpn1.app_id = jet.app_id '
                .'          AND jpn1.id     = jet.person_id_1 '
                .'          ) '
                .'JOIN      #__joaktree_admin_persons     jan1 '
                .'ON        (   jan1.app_id    = jpn1.app_id '
                .'          AND jan1.id        = jpn1.id '
                .'          AND jan1.published = true '
                .           (($personStatus == 0) ? 'AND jan1.living = false ' : ' ')
                .           (($personStatus == 1) ? 'AND jan1.living = true ' : ' ')
                // privacy filter first partner
                .'          AND (  (jan1.living = false AND '.$displayAccess['NAMEname']->notLiving.' = 2 ) '
                .'              OR (jan1.living = true  AND '.$displayAccess['NAMEname']->living.'    = 2 ) '
                .'              ) '
                .'          ) '
                .'JOIN      #__joaktree_trees             jte1 '
                .'ON        (   jte1.app_id    = jan1.app_id '
                .'          AND jte1.id        = jan1.default_tree_id '
                .'          AND jte1.published = true '
                .'          AND jte1.access    IN '.$userAccessLevels.' '
                .'          ) '
                // second partner
                .'JOIN      #__joaktree_persons           jpn2 '
                .'ON        (   jpn2.app_id = jet.app_id '
                .'          AND jpn2.id     = jet.person_id_2 '
                .'          ) '
                .'JOIN      #__joaktree_admin_persons     jan2 '
                .'ON        (   jan2.app_id    = jpn2.app_id '
                .'          AND jan2.id        = jpn2.id '
                .'          AND jan2.published = true '
                .           (($personStatus == 0) ? 'AND jan2.living = false ' : ' ')
                .           (($personStatus == 1) ? 'AND jan2.living = true ' : ' ')
                // privacy filter second partner
                .'          AND (  (jan2.living = false AND '.$displayAccess['NAMEname']->notLiving.' = 2 ) '
                .'              OR (jan2.living = true  AND '.$displayAccess['NAMEname']->living.'    = 2 ) '
                .'              ) '
                .'          ) '
                .'JOIN      #__joaktree_trees             jte2 '
                .'ON        (   jte2.app_id    = jan2.app_id '
                .'          AND jte2.id        = jan2.default_tree_id '
                .'          AND jte2.published = true '
                .'          AND jte2.access    IN '.$userAccessLevels.' '
                .'          ) '
                .'JOIN      #__joaktree_display_settings  jds '
                .'ON        (   jds.code = jet.code '
                .'          AND jds.level = '.$this->_db->quote('relation').' '
                .'          AND jds.published = true '
                .'          AND (  (   jan1.living = false '
                .'                 AND jan2.living = false '
                .'                 AND jds.access  IN '.$userAccessLevels.' '
                .'                 ) '
                .'              OR (   (  jan1.living = true '
                .'                     OR jan2.living = true '
                .'                     ) '
                .'                 AND jds.accessLiving IN '.$userAccessLevels.' '
                .'                 ) '
                .'              ) '
                .'          ) '
                .$where.' ) '
                .$order.' ';

        $this->_db->setquery($query, 0, $limit);
        $temp = $this->_db->loadObjectList();

        if (count($temp)) {
            // get menuId & technology
            $menus		= JoaktreeHelper::getMenus('joaktree');
            $technology	= JoaktreeHelper::getTechnology();

            $linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$technology.'';
            $robot = ($technology == 'a') ? '' : 'rel="noindex, nofollow"';

            foreach ($temp as $row) {
                $menuItemId		= $menus[$row->treeId];
                $row->route		= Route::_($linkBase.'&Itemid='.$menuItemId.'&treeId='.$row->treeId.'&personId='. $row->appId.'!'.$row->personId);
                $row->robot		= $robot;
                $row->yearsago 	= $today['year'] - (int) $row->eventyear;

                $tmp = ucwords(strtolower($row->eventdate));
                $row->eventday	= substr(JoaktreeHelper::convertDateTime($tmp), 0, -4);
                $row->eventdate	= JoaktreeHelper::displayDate($row->eventdate);

                $row->code		= Text::_($row->code);
                $result[] = $row;
            }
        }
        unset($temp);

        return $result;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getSorting()
    {
        return $this->_sorting;
    }

    public function getButtonText()
    {
        return $this->_buttonText;
    }

    public function getDays()
    {
        $days = array();
        for ($i = 1;$i <= 31;$i++) {
            if ($i == 0) {
                $description = '&nbsp;';
            } else {
                $description = $i;
            }

            $days[] = HTMLHelper::_('select.option', $i, $description);
        }

        return $days;
    }

    private function getMonthName($monthNumber)
    {
        $strTime = mktime(1, 1, 1, $monthNumber, 1, date("Y"));
        return date("F", $strTime);
    }

    public function getMonths()
    {
        $months = array();
        for ($i = 1;$i <= 12;$i++) {
            $monthName = Text::_($this->getMonthName($i));
            $months[]  = HTMLHelper::_('select.option', $i, $monthName);
        }

        return $months;
    }

    public function getThemeName()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // retrieve the name of the default theme
        $query = 'SELECT jth.name AS theme '
                .'FROM   #__joaktree_themes  jth '
                .'WHERE  jth.home   = true ';

        $db->setquery($query);
        $theme = $db->loadResult();

        return $theme;
    }
}
