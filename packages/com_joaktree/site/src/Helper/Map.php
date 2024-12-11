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

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;

class Map extends \StdClass
{
    // member declaration
    protected $mapId;
    public $params;
    protected $service;
    protected $provider;

    public function __construct($id)
    {
        $this->_db	= Factory::getContainer()->get(DatabaseInterface::class);
        $query = $this->_db->getquery(true);

        if (isset($id['map']) && !empty($id['map'])) {
            // select from maps
            $query->select(' jmp.* ');
            $query->from(' #__joaktree_maps jmp ');
            $query->where(' jmp.id = :id');
            $query->bind(':id', $id['map'], \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setquery($query);
            $tmp 	= $this->_db->loadAssoc();

            if ($tmp) {
                $params	= (array) json_decode($tmp['params']);
                unset($tmp['params']);

                $this->mapId	= $id['map'];
                $this->params 	= array_merge($tmp, $params);
            }

        } elseif (isset($id['location']) && !empty($id['location'])) {
            // select from locations
            $query->clear();
            $query->select(' jln.latitude, jln.longitude ');
            $query->from(' #__joaktree_locations jln ');
            $query->where(' jln.id     = :id');
            $query->bind(':id', $id['location'], \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setquery($query);
            $this->params 	= $this->_db->loadAssoc();

            // select from trees
            $query->clear();
            $query->select(' jte.app_id ');
            $query->from(' #__joaktree_trees jte ');
            $query->where(' jte.id     = :id');
            $query->bind(':id', $id['tree'], \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setquery($query);
            $this->params['app_id'] 	= $this->_db->loadResult();

            $this->params['selection'] 	= 'location';
            $this->params['service'] 	= 'interactivemap';
            $this->params['tree_id'] 	= $id['tree'];
            $this->params['loc_id'] 	= $id['location'];
            $this->params['distance'] 	= $id['distance'];

            // zoom
            switch ($this->params['distance']) {
                case 0:		// continue
                case 1: 	// continue
                case 2: 	// continue
                case 5: 	$this->params['zoomlevel'] = 11;
                    break;
                case 10:	$this->params['zoomlevel'] = 10;
                    break;
                case 20:	$this->params['zoomlevel'] = 9;
                    break;
                case 50:	// continue
                default:	$this->params['zoomlevel'] = 8;
                    break;
            }

        } elseif (isset($id['person']) && !empty($id['person'])) {
            // select from persons admin
            $query->select(' jan.* ');
            $query->from(' #__joaktree_admin_persons jan ');
            $query->where(' jan.app_id = :appid');
            $query->where(' jan.id     = :personid');
            $query->bind(':appid', $id['app'], \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $id['person'], \Joomla\Database\ParameterType::STRING);

            $this->_db->setquery($query);
            $this->params 	= $this->_db->loadAssoc();

            $this->params['selection'] = 'person';
            $this->params['service'] =  ($this->params['map'] == 1)
                                            ? 'staticmap'
                                            : (
                                                ($this->params['map'] == 2)
                                                 ? 'interactivemap'
                                                 : ''
                                            );
            unset($this->params['map']);

            $this->params['tree_id'] = $this->params['default_tree_id'];
            unset($this->params['default_tree_id']);

            $this->params['person_id'] = $this->params['id'];
            unset($this->params['id']);

            $this->params['relations'] = 0;
            $this->params['period_start'] = 0;
            $this->params['period_end'] = 0;

            unset($this->params['published']);
            unset($this->params['access']);
            unset($this->params['living']);
            unset($this->params['page']);
            unset($this->params['robots']);
        }

        if (isset($this->params)) {
            $this->service 	= MBJService::getInstance($this->params);
        }

        // parent::__construct();
    }

    public static function getMapId($optional = false)
    {
        static $mapId;

        if (!isset($mapId)) {
            $input	= Factory::getApplication()->input;
            $tmp 	= $input->get('mapId', null, 'int');

            if (empty($tmp)) {
                // no map id in request, try the parameters.
                $app 	= Factory::getApplication('site');
                $params = $app->getParams();
                $tmp 	= $params->get('mapId');
            }

            if (empty($tmp)) {
                // still nothing found
                if ($optional) {
                    // it is an optional parameter - we just continue
                    $mapId = null;
                } else {
                    // it is a required parameter - we stop
                    die('wrong request');
                }
            } else {
                $mapId = (int) $tmp;
            }
        }

        return $mapId;
    }

    public static function getLocationId($optional = false)
    {
        static $_locId;

        if (!isset($_locId)) {
            $input = Factory::getApplication()->input;
            $tmp   = $input->get('locId', null, 'int');

            if (empty($tmp)) {
                // no location Id is given in request
                if ($optional) {
                    $_locId = null;
                } else {
                    die('wrong request');
                }
            } else {
                $_locId = (int) $tmp;
            }
        }

        return $_locId;
    }

    public static function getDistance($optional = false)
    {
        $app 	 = Factory::getApplication('site');
        $tmp	 = $app->getUserStateFromRequest('com_joaktree.map.distance', 'distance', 0, 'int');

        if (empty($tmp)) {
            // no distance is given in request
            if ($optional) {
                $_distance = null;
            } else {
                die('wrong request');
            }
        } else {
            $_distance = (int) $tmp;
        }
        return $_distance;
    }

    public function getService()
    {
        // function for interactive maps
        return $this->service;
    }

    public function getStyleDeclaration()
    {
        // function for interactive maps
        return $this->service->_('getStyleDeclaration');
    }

    public function getToolkit()
    {
        // Toolkit for interactive maps
        return $this->service->_('getToolkit');
    }

    public function getMapScript()
    {
        // function for interactive maps

        // get the theme colors
        $theme = $this->getTheme();
        $this->params['color'] = $theme->get('dynMarkerIcons');

        $items 		= $this->getMapItems();
        $mapScript 	= $this->service->_('fetch', $items, $this->params);

        return $mapScript;
    }

    public function getMapView()
    {
        // function for static maps

        // get the theme colors
        $theme = $this->getTheme();
        $this->params['color'] = $theme->get('statMarkerColor');

        $items 		= $this->getMapItems();
        $mapView 	= $this->service->_('fetch', $items, $this->params);

        return $mapView;
    }

    private function checkOptions()
    {
        // look for options
        $input = Factory::getApplication()->input;
        $tmp   = $input->get('options', null, 'string');

        if (!$tmp) {
            return false;
        } else {
            // there are options
            $options = explode("|", $tmp);

            // first options is the token
            $token = Session::getFormToken();
            if ($token != array_shift($options)) {
                die('wrong request');
            }

            // we are ok
            $tmp = array_shift($options);
            $person = explode("!", $tmp);
            $tmp2   = array_shift($person);
            $this->params['app_id']		= empty($tmp2) ? $this->params['app_id'] : $tmp2;
            $tmp2   = array_shift($person);
            $this->params['person_id']	= empty($tmp2) ? $this->params['person_id'] : $tmp2;

            $this->params['relations'] 	= array_shift($options);
            $this->params['subject'] 	= array_shift($options);
            $this->params['period_start'] = array_shift($options);
            $this->params['period_end'] = array_shift($options);

            $this->params['includeEvents'] = array_shift($options);
            unset($this->params['excludePersonEvents']);
            unset($this->params['excludeRelationEvents']);

            $this->params['distance'] = array_shift($options);

            return true;
        }
    }

    private function getMaxInfo()
    {
        $query = 'SHOW VARIABLES LIKE '.$this->_db->quote('group_concat_max_len').' ';
        $this->_db->setquery($query);
        $maxInfo = $this->_db->loadRow();
        return (($maxInfo) ? round((int) array_pop($maxInfo), -2) : 1000);
    }

    private function getMapItems()
    {
        // check for overriding options
        $this->checkOptions();

        // get info about database setting
        $maxInfo = $this->getMaxInfo();

        switch ($this->params['selection']) {
            case "person":	$query = self::getqueryPerson();
                break;
            case "tree":	// continue
            case "location":	// continue
            default:	$query = self::getqueryTree();
                break;
        }

        $this->_db->setquery($query);
        $mapItems = $this->_db->loadObjectList();

        // prepare information for interactive map
        if ($this->params['service'] == 'interactivemap') {
            $menus    = JoaktreeHelper::getMenus('joaktree');
            $linkBase =  'index.php?option=com_joaktree'
                        .'&view=joaktree'
                        .'&tech='.JoaktreeHelper::getTechnology()
                        .'&Itemid='.$menus[$this->params['tree_id']];
            $robot = (JoaktreeHelper::getTechnology() == 'a') ? '' : 'rel="noindex, nofollow"';

            for ($i = 0; $i < count($mapItems); $i++) {
                $indMore = 	(strlen($mapItems[$i]->information) > $maxInfo);
                $tmps1 = explode('|', $mapItems[$i]->information);

                if ($indMore) {
                    // remove the last element
                    array_pop($tmps1);
                }

                $info  = array();
                foreach ($tmps1 as $tmp1) {
                    $tmps2 = explode('#', $tmp1);

                    $name  = (count($tmps2)) ? htmlspecialchars(array_shift($tmps2), ENT_QUOTES) : '....';
                    $id    = (count($tmps2)) ? array_shift($tmps2) : null;
                    $event = (count($tmps2)) ? Text::_(array_shift($tmps2)) : '';
                    //$dateRaw = (count($tmps2)) ? array_shift($tmps2) : null);
                    $date  = (count($tmps2)) ? '&nbsp;'.JoaktreeHelper::displayDate(array_shift($tmps2)) : '';
                    $def_tree = (count($tmps2)) ? array_shift($tmps2) : null;

                    if (($id) && (($this->params['selection'] == 'tree') || ($this->params['selection'] == 'location'))) {
                        $href  = Route::_($linkBase.'&treeId='.$this->params['tree_id'].'&personId='.$this->params['app_id'].'!'.$id);
                        $info[] = '<a href="'.$href.'" target="_top" '.$robot.' >'.$name.'</a>&nbsp;('.$event.$date.')';
                    } elseif (($id)
                              && ($this->params['selection'] == 'person')
                              && ($def_tree)
                    ) {
                        $href  = Route::_($linkBase.'&treeId='.(int)$def_tree.'&personId='.$this->params['app_id'].'!'.$id);
                        $info[] = '<a href="'.$href.'" target="_top" '.$robot.' >'.$name.'</a>&nbsp;('.$event.$date.')';
                    } else {
                        $info[] = $name.'&nbsp;('.$event.$date.')';
                    }
                }

                if ($indMore) {
                    // add text
                    $info[] = Text::_('JT_ANDMORE');
                }

                $mapItems[$i]->information = implode('<br />', $info);
                unset($info);
            }
        }

        return $mapItems;
    }

    private function getqueryTree($selectThesePersonsOnly = array())
    {
        $levels			= JoaktreeHelper::getUserAccessLevels();
        $displayAccess	= JoaktreeHelper::getDisplayAccess();
        $query			= $this->_db->getquery(true);

        if (isset($this->params['excludePersonEvents']) && !empty($this->params['excludePersonEvents'])) {
            $tmp = (array) json_decode($this->params['excludePersonEvents']);
            $excludePersonEvents  = 'AND jpe.code NOT IN ("'.implode('","', $tmp).'") ';
        } else {
            $excludePersonEvents  = '';
        }

        if (isset($this->params['excludeRelationEvents']) && !empty($this->params['excludeRelationEvents'])) {
            $tmp = (array) json_decode($this->params['excludeRelationEvents']);
            $excludeRelationEvents  = 'AND jre.code NOT IN ("'.implode('","', $tmp).'") ';
        } else {
            $excludeRelationEvents  = '';
        }

        if (isset($this->params['includeEvents']) && !empty($this->params['includeEvents'])) {
            $tmp = (array) json_decode($this->params['includeEvents']);
            $includePersonEvents    = 'AND jpe.code IN ("'.implode('","', $tmp).'") ';
            $includeRelationEvents  = 'AND jre.code IN ("'.implode('","', $tmp).'") ';
        } else {
            $includePersonEvents    = '';
            $includeRelationEvents  = '';
        }

        $query->select(' jln.id ');
        $query->select(' jln.value ');
        $query->select(' jln.longitude ');
        $query->select(' jln.latitude ');
        $query->select(' COUNT( iv_event.code ) AS label ');
        $query->select(' GROUP_CONCAT( '
                      .' DISTINCT CONCAT_WS('.$this->_db->Quote('#').' '
                      .'                   , CONCAT_WS('.$this->_db->Quote(' ')
                      .'                              , jpn1.firstName '
                      .'                              , jpn1.namePreposition '
                      .'                              , jpn1.familyName '
                      .'                              , IF((ISNULL(jpn2.firstName) && ISNULL(jpn2.familyName )), '.$this->_db->Quote('').', '.$this->_db->Quote('+').')'
                      .'                              , jpn2.firstName '
                      .'                              , jpn2.namePreposition '
                      .'                              , jpn2.familyName '
                      .'                              ) '
                      .'                   , jpn1.id '
                      .'                   , iv_event.code '
                      .'                   , iv_event.eventDate '
                      .'                   , IFNULL(jan2.default_tree_id, jan1.default_tree_id) '
                      .'                   ) '
                      .' ORDER BY SUBSTR( RTRIM(iv_event.eventDate), -4 ) '
                      .' SEPARATOR '.$this->_db->Quote('|').' '
                      .' ) AS information ');
        $query->from(
            ' ( SELECT jpe.person_id     AS person_id_1 '
                    .'   ,      NULL              AS person_id_2 '
                    .'   ,      jpe.code          AS code '
                    .'   ,      '.$this->_db->Quote('person').' AS level '
                    .'   ,      jpe.eventDate     AS eventDate '
                    .'   ,      jpe.loc_id        AS loc_id '
                    .'   ,      jpe.app_id        AS app_id '
                    .'   ,      jdsp.access       AS access '
                    .'   ,      jdsp.accessLiving AS accessLiving '
                    .'   FROM   #__joaktree_person_events  jpe '
                    .'   INNER JOIN #__joaktree_display_settings  jdsp '
                    .'   ON (    jdsp.code        = jpe.code '
                    .'      AND  jdsp.level       = '.$this->_db->Quote('person').' '
                    .'      AND  jdsp.published   = true '
                    .'      ) '
                    .'   WHERE  jpe.location      IS NOT NULL '
                    .    (count($selectThesePersonsOnly) ? $selectThesePersonsOnly['personEvents'] : '')
                    .    $excludePersonEvents
                    .    $includePersonEvents
                    .'   UNION '
                    .'   SELECT jre.person_id_1   AS person_id_1 '
                    .'   ,      jre.person_id_2   AS person_id_2 '
                    .'   ,      jre.code          AS code '
                    .'   ,      '.$this->_db->Quote('relation').' AS level '
                    .'   ,      jre.eventDate     AS eventDate '
                    .'   ,      jre.loc_id        AS loc_id '
                    .'   ,      jre.app_id        AS app_id '
                    .'   ,      jdsr.access       AS access '
                    .'   ,      jdsr.accessLiving AS accessLiving '
                    .'   FROM   #__joaktree_relation_events  jre '
                    .'   INNER JOIN #__joaktree_display_settings  jdsr '
                    .'   ON (    jdsr.code        = jre.code '
                    .'      AND  jdsr.level       = '.$this->_db->Quote('relation').' '
                    .'      AND  jdsr.published   = true '
                    .'      ) '
                    .'   WHERE  jre.location     IS NOT NULL '
                    .    (count($selectThesePersonsOnly) ? $selectThesePersonsOnly['relationEvents'] : '')
                    .    $excludeRelationEvents
                    .    $includeRelationEvents
                    .'   ) AS iv_event '
        );

        if (($this->params['selection'] == 'tree') || ($this->params['selection'] == 'location')) {
            $query->innerJoin(
                ' #__joaktree_tree_persons jtp '
                             .' ON (   jtp.app_id    = iv_event.app_id '
                             .'    AND (  jtp.person_id = iv_event.person_id_1 '
                             .'        OR jtp.person_id = IFNULL(iv_event.person_id_2, iv_event.person_id_1) '
                             .'        ) '
                             .'    ) '
            );
            $query->innerJoin(
                ' #__joaktree_trees        jte '
                             .' ON (   jte.app_id    = jtp.app_id '
                             .'    AND jte.id        = jtp.tree_id '
                             .'    AND jte.published = true '
                             .'    AND jte.access    IN ' .$levels.' '
                             .'    ) '
            );
            $query->where(' jtp.tree_id = :treeid');
            $query->bind(':treeid', $this->params['tree_id'], \Joomla\Database\ParameterType::INTEGER);
        }

        $query->innerJoin(
            ' #__joaktree_admin_persons     jan1 '
                         .' ON (   jan1.app_id    = iv_event.app_id '
                         .'    AND jan1.id        = iv_event.person_id_1 '
                         .'    AND jan1.published = true '
                         // privacy filter
                         .'    AND (  (   jan1.living = false '
                         .'           AND '.$displayAccess['NAMEname']->notLiving.' > 1 '
                         .'           AND iv_event.access IN '.$levels.' '
                         .'           ) '
                         .'        OR (   jan1.living = true  '
                         .'           AND '.$displayAccess['NAMEname']->living.'    > 1 '
                         .'           AND iv_event.accessLiving IN '.$levels.' '
                         .'           ) '
                         .'        ) '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_admin_persons     jan2 '
                         .' ON (   jan2.app_id    = iv_event.app_id '
                         .'    AND jan2.id        = IFNULL(iv_event.person_id_2, iv_event.person_id_1) '
                         .'    AND jan2.published = true '
                         .'    AND jan2.app_id = ' .$this->params['app_id'] // RRG restriction à la source GedCom de l'individu
                         // privacy filter
                         .'    AND (  (   jan2.living = false '
                         .'           AND '.$displayAccess['NAMEname']->notLiving.' > 1 '
                         .'           AND iv_event.access IN '.$levels.' '
                         .'           ) '
                         .'        OR (   jan2.living = true  '
                         .'           AND '.$displayAccess['NAMEname']->living.'    > 1 '
                         .'           AND iv_event.accessLiving IN '.$levels.' '
                         .'           ) '
                         .'        ) '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_locations  jln '
                         .' ON (   jln.id         = iv_event.loc_id '
                         .'    AND jln.latitude   IS NOT NULL '
                         .'    AND jln.latitude   <> 0 '
                         .'    AND jln.longitude  IS NOT NULL '
                         .'    AND jln.longitude  <> 0 '
                         .'    AND jln.indDeleted = 0 '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_persons jpn1 '
                         .' ON (   jpn1.app_id = iv_event.app_id '
                         .'    AND jpn1.id     = iv_event.person_id_1 '
                         .'    ) '
        );
        $query->leftJoin(
            '  #__joaktree_persons jpn2 '
                         .' ON (   jpn2.app_id = iv_event.app_id '
                         .'    AND jpn2.id     = iv_event.person_id_2 '
                         .'    ) '
        );


        if (($this->params['selection'] == 'tree') && ($this->params['relations'])) {
            $query->where(' jtp.lineage IS NOT NULL ');
        }

        if ($this->params['selection'] == 'location') {
            if (isset($this->params['distance']) && ($this->params['distance'] > 0)) {
                // earth's mean radius in km: 6371
                // x = (lon2-lon1) * cos((lat1+lat2)/2)
                // y = (lat2-lat1)
                // d = sqrt(x*x + y*y) * R
                $query->where(' (SQRT( (   (RADIANS(jln.longitude) - RADIANS('.$this->params['longitude'].')) '
                             .'          * COS( (RADIANS('.$this->params['latitude'].') + RADIANS(jln.latitude)) / 2 ) '
                             .'          * (RADIANS(jln.longitude) - RADIANS('.$this->params['longitude'].')) '
                             .'          * COS( (RADIANS('.$this->params['latitude'].') + RADIANS(jln.latitude)) / 2 ) '
                             .'        ) '
                             .'      + (   (RADIANS(jln.latitude) - RADIANS('.$this->params['latitude'].')) '
                             .'          * (RADIANS(jln.latitude) - RADIANS('.$this->params['latitude'].')) '
                             .'        )'
                             .'      ) * 6371) < '.$this->params['distance'].' ');
            } else {
                $query->where(' jln.id = :locid');
                $query->bind(':locid', $this->params['loc_id'], \Joomla\Database\ParameterType::INTEGER);

            }
        }

        if (isset($this->params['subject']) && !empty($this->params['subject'])) {
            $query->where(
                '(  UPPER(jpn1.familyName) LIKE UPPER('.$this->_db->quote('%'.$this->params['subject'].'%').') '
                         .'OR UPPER(jpn2.familyName) LIKE UPPER('.$this->_db->quote('%'.$this->params['subject'].'%').') '
                         .')'
            );
        }

        if (isset($this->params['period_start']) && !empty($this->params['period_start']) && ($this->params['period_start'] > 0)) {
            $query->where('SUBSTR( RTRIM(iv_event.eventDate), -4 ) >= '.$this->params['period_start'].' ');
        }

        if (isset($this->params['period_end']) && !empty($this->params['period_end']) && ($this->params['period_end'] > 0)) {
            $query->where('SUBSTR( RTRIM(iv_event.eventDate), -4 ) <= '.$this->params['period_end'].' ');
        }

        $query->group(' jln.id ');
        $query->group(' jln.value ');
        $query->group(' jln.longitude ');
        $query->group(' jln.latitude ');
        $query->order(' COUNT( iv_event.code ) ');
        $query->order(' jln.value ');

        return $query;

    }

    private function getqueryPerson()
    {
        $selectThesePersonsOnly = array();
        $relations = array();

        // which set of persons do we need
        switch ($this->params['relations']) {
            case 1:	// first degree relations
                $relations = $this->getFirstDegreeRelations();
                break;
            case 2:	// descendants
                $relations = $this->getDescendants();
                break;
            case 3:	// ancestors
                $relations = $this->getAncestors();
                break;
            case 0:	 // continue
            default: // just the person self
                break;
        }

        // add the perons self to the array
        $relations[] = $this->params['person_id'];

        $selectThesePersonsOnly['personEvents']
            = ' AND jpe.person_id IN ("'.implode('","', $relations).'") ';
        $selectThesePersonsOnly['relationEvents']
            = ' AND (  jre.person_id_1 IN ("'.implode('","', $relations).'") '
             .'     OR jre.person_id_2 IN ("'.implode('","', $relations).'") '
             .'     ) ';

        return self::getqueryTree($selectThesePersonsOnly);
    }

    private function getFirstDegreeRelations()
    {
        $query = // children
                 'SELECT jrn.person_id_1 '
                .'FROM   #__joaktree_relations  jrn '
                .'WHERE  jrn.app_id      =  '.(int)$this->params['app_id']
                .'AND    jrn.person_id_2 =  '.$this->_db->quote($this->params['person_id'])
                .'AND    jrn.type        <> '.$this->_db->quote('partner').' '
                .'UNION ' // parents
                .'SELECT jrn.person_id_2 '
                .'FROM   #__joaktree_relations  jrn '
                .'WHERE  jrn.app_id      =  :appid'
                .'AND    jrn.person_id_1 =  :personid'
                .'AND    jrn.type        <> '.$this->_db->quote('partner').' '
                .'UNION ' // siblings
                .'SELECT jrn.person_id_1 '
                .'FROM   #__joaktree_relations  jrn '
                .'WHERE  jrn.app_id      =  :appid'
                .'AND    jrn.person_id_2 IN '
                .'( SELECT jrn2.person_id_2 '
                .'  FROM   #__joaktree_relations jrn2 '
                .'  WHERE  jrn2.app_id      =  jrn.app_id '
                .'  AND    jrn2.person_id_1 =  :personid'.$this->_db->quote($this->params['person_id']).' '
                .'  AND    jrn.type        <> '.$this->_db->quote('partner').' '
                .') '
                .'AND    jrn.type        <> '.$this->_db->quote('partner').' ';

        $this->_db->setquery($query);
        return $this->_db->loadColumn();
    }

    private function getDescendants()
    {
        // initiate
        $indDescendants = true;
        $persons 		= array($this->params['person_id']);
        $descendants 	= array();

        while ($indDescendants) {
            $query = // children
                     'SELECT jrn.person_id_1 '
                    .'FROM   #__joaktree_relations  jrn '
                    .'WHERE  jrn.app_id      =  '.(int)$this->params['app_id']
                    .'AND    jrn.person_id_2 IN ("'.implode('","', $persons).'") '
                    .'AND    jrn.type        <> '.$this->_db->quote('partner').' ';
            $this->_db->setquery($query);
            $result = $this->_db->loadColumn();

            if (count($result)) {
                // set up persons for the next loop
                $persons = $result;

                // add the result to the descendants
                $descendants = array_merge($descendants, $result);
            } else {
                $indDescendants = false;
            }
        }

        return $descendants;
    }

    private function getAncestors()
    {
        // initiate
        $indAncestors	= true;
        $persons 		= array($this->params['person_id']);
        $ancestors 		= array();

        while ($indAncestors) {
            $query = // ancestors
                     'SELECT jrn.person_id_2 '
                    .'FROM   #__joaktree_relations  jrn '
                    .'WHERE  jrn.app_id      =  '.(int)$this->params['app_id']
                    .'AND    jrn.person_id_1 IN ("'.implode('","', $persons).'") '
                    .'AND    jrn.type        <> '.$this->_db->quote('partner').' ';

            $this->_db->setquery($query);
            $result = $this->_db->loadColumn();

            if (count($result)) {
                // set up persons for the next loop
                $persons = $result;

                // add the result to the descendants
                $ancestors = array_merge($ancestors, $result);
            } else {
                $indAncestors = false;
            }
        }

        return $ancestors;
    }

    private function getTree()
    {
        $query = $this->_db->getquery(true);

        $query->select(' jte.* ');
        $query->select(JoaktreeHelper::getConcatenatedFullName(false).' AS orig_ancestor ');
        $query->from(' #__joaktree_trees  jte ');
        $query->leftJoin(
            ' #__joaktree_persons  jpn '
                        .' ON (   jpn.app_id = jte.app_id '
                        .'    AND jpn.id     = jte.root_person_id '
                        .'    ) '
        );
        $query->leftJoin(
            ' #__joaktree_admin_persons  jan '
                        .' ON (   jan.app_id = jpn.app_id '
                        .'    AND jan.id     = jpn.id '
                        .'    ) '
        );
        $query->where(' jte.id      =  :treeid');
        $query->bind(':treeid', $this->params['tree_id'], \Joomla\Database\ParameterType::INTEGER);

        $this->_db->setquery($query);
        $result = $this->_db->loadObject();

        return $result;
    }

    private function getPerson()
    {
        $query = $this->_db->getquery(true);

        $query->select(JoaktreeHelper::getConcatenatedFullName(false).' AS name ');
        $query->from(' #__joaktree_persons  jpn ');
        $query->where(' jpn.app_id =  :appid');
        $query->where(' jpn.id     =  :personid');
        $query->bind(':appid', $this->params['app_id'], \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->params['person_id'], \Joomla\Database\ParameterType::STRING);

        $this->_db->setquery($query);
        $result = $this->_db->loadObject();

        return $result;
    }

    private function getHtmlEvents()
    {
        $query = $this->_db->getquery(true);
        $html  = array();

        // select the events
        $query->select(' code ');
        $query->from(' #__joaktree_display_settings ');
        $query->where(' level IN ( '.$this->_db->quote('person').', '.$this->_db->quote('relation').') ');
        $query->where(' published = true ');
        $query->where(' code NOT IN ('.$this->_db->quote('NOTE').','.$this->_db->quote('ENOT').','.$this->_db->quote('SOUR').','.$this->_db->quote('ESOU').') ');
        $query->order(' level, ordering ');

        $this->_db->setquery($query);
        $events = $this->_db->loadColumn();

        // create an array of excluded events
        $excludeEvents = array_merge(
            (array) json_decode($this->params['excludePersonEvents']),
            (array) json_decode($this->params['excludeRelationEvents'])
        );

        // loop through the events
        foreach ($events as $event) {
            $html[] = '<option '.((array_search($event, $excludeEvents) === false) ? 'selected="selected" ' : '')
                     .' value="'.$event.'">'
                     .Text::_($event)
                     .'</option>';
        }

        return $html;
    }

    private function getTheme()
    {
        static $theme;

        if (!isset($theme)) {
            $query = $this->_db->getquery(true);
            $query->select(' jth.name AS theme ');
            $query->select(' jth.params ');
            $query->from(' #__joaktree_trees   jte ');
            $query->innerJoin(
                ' #__joaktree_themes  jth '
                             .' ON (jth.id = jte.theme_id) '
            );
            $query->where(' jte.id   = :treeid');
            $query->bind(':treeid', $this->params['tree_id'], \Joomla\Database\ParameterType::INTEGER);

            // retrieve the name
            $this->_db->setquery($query);
            $tmp = $this->_db->loadObject();

            $theme = new Registry();
            // load parameters into registry object
            $theme->loadString($tmp->params, 'JSON');
            unset($tmp->params);

            // load the rest of the object into registry object
            $theme->loadObject($tmp);
        }

        return $theme;
    }

    public function getUIControl($mapHtmlId)
    {
        if (isset($this->params['ui_control']) && !empty($this->params['ui_control'])) {
            $script = array();
            $html   = array();

            $document	= Factory::getApplication()->getDocument();
            $document->addScript(JoaktreeHelper::joaktreejs('jtmap.js'));

            // set up style sheets and javascript files
            HTMLHelper::stylesheet('media/com_joaktree/css/joaktree.map.css');

            //HTMLHelper::_('behavior.formvalidation');
            HTMLHelper::_('behavior.formvalidator');
            HTMLHelper::_('bootstrap.modal', 'a.modal_person');

            if ($this->params['service'] == 'interactivemap') {
                $basicUrl = 'index.php?option=com_joaktree&amp;tmpl=component&amp;format=raw&amp;view=interactivemap&amp;mapId='.$this->mapId.'&amp;options='.Session::getFormToken();
                $mapfunction = 'jtRefreshDynMap(\''.$basicUrl.'\', \''.$mapHtmlId.'\');';
            } else {
                $theme = $this->getTheme();
                $basicUrl = 'index.php?option=com_joaktree&amp;format=raw&amp;tmpl=component&amp;view=map&amp;layout=staticraw&amp;mapId='.$this->mapId.'&amp;options='.Session::getFormToken();
                $loadingUrl = Uri::base().'components/com_joaktree/themes/'.$theme->get('theme').'/images/ajax-loader.gif';
                $mapfunction = 'jtRefreshStatMap(\''.$basicUrl.'\', \''.$loadingUrl.'\',\''.$mapHtmlId.'\');';
            }

            $html[] = '<div>';
            $html[] = '<div id="jtmap-but-showui" style="display: block;">';
            $html[] = '  <input type="submit" class="button" onclick="showui();" value="'.Text::_('JTMAP_UICONTROL').'">';
            $html[] = '  <input id="jtmap-id" name="map_id" type="hidden" value="'.$this->mapId.'" />';
            $html[] = '</div>';

            $html[] = '<div id="jtmap-uidisplay" style="display:none;">';
            $html[] = '<fieldset class="jtmap-fieldset">';
            $html[] = '<legend class="jtmap-legend">'.Text::_('JTMAP_SELECTIONS').'</legend>';

            $html[] = '  <div class="jtmap-div-button">';
            $html[] = '    <input type="submit" class="button" onclick="'.$mapfunction.'" value="'.Text::_('JTMAP_UIEXECUTE').'">';
            $html[] = '    <input type="submit" class="button" onclick="hideui();" value="'.Text::_('JLIB_HTML_BEHAVIOR_CLOSE').'">';
            $html[] = '  </div>';

            // select another person
            if ($this->params['selection'] == 'person') {
                $person = $this->getPerson();
                $link   = Route::_('index.php?option=com_joaktree&amp;view=list&amp;tmpl=component&amp;layout=select&amp;treeId='.$this->params['tree_id'].'&amp;action=select');

                $html[] = '<p>';
                $html[] = '<label for="person" title="'.Text::_('JTMAP_DESC_PERSON').'">'.Text::_('JTMAP_PERSON').'</label>';
                $html[] = '<input id="jtmap-person" name="person" class="inputbox readonly" type="text" value="'.$person->name.'" disabled="disabled" />';
                $html[] = '<input id="jtmap-person_id" name="person_id" type="hidden" value="'.$this->params['app_id'].'!'.$this->params['person_id'].'" />';
                $html[] = '<a class="modal_person button" style="float: left; padding: 3px; margin-left: 4px;" '
                          .'href="'.$link.'" '
                          .'rel="{handler: \'iframe\', size: {x: 800, y: 500}}">'
                          .Text::_('JTMAP_SELECTPERSON')
                          .'</a>';
                $html[] = '</p>';
            }

            // include relations for person
            if ($this->params['selection'] == 'person') {
                $html[] = '<p>';
                $html[] = '<label for="relations" title="'.Text::_('JTMAP_DESC_RELATIONS').'">'.Text::_('JTMAP_RELATIONS').'</label>';
                $html[] = '<select id="jtmap-relations" name="relations" class="inputbox" size="1">';
                $html[] = '<option '.(($this->params['relations'] == 0) ? 'selected="selected" ' : '').' value="0">'.Text::_('JNO').'</option>';
                $html[] = '<option '.(($this->params['relations'] == 1) ? 'selected="selected" ' : '').' value="1">'.Text::_('JTMAP_VALUE_FIRSTDEGREE').'</option>';
                $html[] = '<option '.(($this->params['relations'] == 2) ? 'selected="selected" ' : '').' value="2">'.Text::_('JTMAP_VALUE_DESCENDANTS').'</option>';
                $html[] = '<option '.(($this->params['relations'] == 3) ? 'selected="selected" ' : '').' value="3">'.Text::_('JTMAP_VALUE_ANCESTORS').'</option>';
                $html[] = '</select>';
                $html[] = '</p>';
            }

            // include relations for tree which holds descendants
            if (($this->params['selection'] == 'tree') || ($this->params['selection'] == 'location')) {
                $tree = $this->getTree();
                if ($tree->holds == 'descendants') {
                    $html[] = '<p>';
                    $html[] = '<label for="relations" title="'.Text::_('JTMAP_DESC_DESCENDANTS').'">'.Text::_('JTMAP_RELATIONS').'</label>';
                    $html[] = '<select id="jtmap-relations" name="relations" class="inputbox" size="1">';
                    $html[] = '<option '.(($this->params['relations'] == 0) ? 'selected="selected" ' : '').' value="0">'.Text::_('JTMAP_VALUE_ALL').'</option>';
                    $html[] = '<option '.(($this->params['relations'] <> 0) ? 'selected="selected" ' : '').' value="1">'.Text::sprintf('JTMAP_VALUE_DESCENDANTSOF', $tree->orig_ancestor).'</option>';
                    $html[] = '</select>';
                    $html[] = '</p>';
                }
            }

            // filter on a family name
            if (($this->params['selection'] == 'tree') || ($this->params['selection'] == 'location')) {
                $html[] = '<p>';
                $html[] = '<label for="familyname" title="'.Text::_('JTMAP_DESC_FAMILYNAME').'">'.Text::_('JT_LABEL_FAMILYNAME').'</label>';
                $html[] = '<input id="jtmap-familyname" name="familyname" class="inputbox" type="text" value="'.$this->params['subject'].'" />';
                $html[] = '</p>';
            }

            // filter on distance
            if ($this->params['selection'] == 'location') {
                if (isset($this->params['center']) && !empty($this->params['center'])) {
                    $center = Text::_('JTMAP_FROM').'&nbsp;'.$this->params['center'];
                } elseif ((isset($this->params['longitude'])) && (!empty($this->params['longitude']))
                          && (isset($this->params['latitude']))  && (!empty($this->params['latitude']))
                ) {
                    $center = Text::_('JTMAP_FROM').'&nbsp;'.'( '.$this->params['latitude'].', '.$this->params['longitude'].' )';
                } else {
                    $center = Text::_('JTMAP_NOCENTER');
                }

                $html[] = '<p>';
                $html[] = '<label for="distance" title="'.Text::_('JTMAP_DESC_DISTANCE').'">'.Text::_('JTMAP_DISTANCE').'</label>';
                $html[] = '<input id="jtmap-distance" name="distance" class="inputbox validate-numeric" type="text" value="'.$this->params['distance'].'" maxlength="4" size="4"/>';
                $html[] = '<span>&nbsp;'.$center.'</span>';
                $html[] = '</p>';
            }

            $html[] = '<p>';
            $html[] = '<label for="perstart" title="'.Text::_('JTMAP_DESC_PERIODSTART').'">'.Text::_('JTMAP_PERIODSTART').'</label>';
            $html[] = '<input id="jtmap-perstart" name="perstart" class="inputbox validate-numeric" type="text" value="'.$this->params['period_start'].'" maxlength="4" size="4"/>';
            $html[] = '</p>';

            $html[] = '<p>';
            ;
            $html[] = '<label for="perend" title="'.Text::_('JTMAP_DESC_PERIODEND').'" >'.Text::_('JTMAP_PERIODEND').'</label>';
            $html[] = '<input id="jtmap-perend" name="perend" class="inputbox validate-numeric" type="text" value="'.$this->params['period_end'].'" maxlength="4" size="4"/>';
            $html[] = '</p>';

            $options = $this->getHtmlEvents();
            if (count($options)) {
                $html[] = '<div style="clear:both;"></div>';
                $html[] = '<p>';
                $html[] = '<label for="events" title="'.Text::_('JTMAP_DESC_INCLUDE_EVENTS').'">'.Text::_('JTMAP_INCLUDE_EVENTS').'</label>';
                $html[] = '<select id="jtmap-events" name="events" class="inputbox" multiple="multiple" size="10">';
                $html   = array_merge($html, $options);
                $html[] = '</select>';
                $html[] = '</p>';
            }

            $html[] = '</fieldset>';
            $html[] = '</div>';

            $html[] = '<div style="clear:both;"></div>';
            $html[] = '</div>';

            return implode("\n", array_merge($script, $html));
        } else {
            return false;
        }
    }
}
