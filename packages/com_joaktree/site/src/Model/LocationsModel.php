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
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\Input\Cookie;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJInteractivemap;

class LocationsModel extends BaseDatabaseModel
{
    public function getTreeId()
    {
        return JoaktreeHelper::getTreeId();
    }

    public static function getAccess()
    {
        return JoaktreeHelper::getAccessTree();
    }

    public static function getInteractiveMap()
    {
        return MBJInteractivemap::isActivated();
    }

    public function getLocationIndex()
    {
        $app 	= Factory::getApplication('site');
        $index	= $app->getUserState('joaktree.locations.index');

        if (!$index) {
            $index = array();

            // retrieve the index information from the database
            $query = $this->_db->getquery(true);
            $query->select(' DISTINCT indexLoc ');
            $query->from(' #__joaktree_locations ');
            $query->order(' indexLoc ');
            $this->_db->setquery($query);
            $results = $this->_db->loadRowList();

            $params = JoaktreeHelper::getJTParams();
            $groupCountLoc = (int) $params->get('groupCountLoc', '3');
            $i = 0;
            $n = 0;

            foreach ($results as $result) {
                $n++;
                $index[$i][$n] = array_shift($result);
                if (($n == $groupCountLoc) || ($groupCountLoc == 0)) {
                    $i++;
                    $n = 0;
                }
            }

            $app->setUserState('joaktree.locations.index', $index);
        }

        return $index;
    }

    public static function getLocationFilter()
    {
        static $filterId;

        if (!isset($filterId)) {
            $app 		= Factory::getApplication('site');
            $params 	= $app->getParams();
            $indCookie  = $params->get('indCookies', true);
            $input      = $app->input;

            if ($indCookie) {
                // we return the cookie
                $cookie = new Cookie();
                $filterId	= $cookie->get('jt_loc_index', 0, 'int');
            } else {
                // we return 0
                $filterId	= 0;
            }
        }

        return $filterId;
    }

    public static function getMapUrl()
    {
        static $mapUrl;

        if (!isset($mapUrl)) {
            $app 		= Factory::getApplication('site');
            $params 	= $app->getParams();
            $indCookie  = $params->get('indCookies', true);
            $input      = $app->input;
            if ($indCookie) {
                // we return the cookie
                $cookie = new Cookie();
                //$mapUrl	= $_COOKIE['jt_loc_url'];
                $mapUrl	= $cookie->get('jt_loc_url', null, 'string');
            } else {
                // we return 0
                $mapUrl	= null;
            }
        }

        return $mapUrl;
    }

    private function _buildquery()
    {
        $levels			= JoaktreeHelper::getUserAccessLevels();
        $displayAccess	= JoaktreeHelper::getDisplayAccess();
        $treeId     	= intval($this->getTreeId());

        $query	= $this->_db->getquery(true);
        $query->select(' DISTINCT jln.value    AS location ');
        $query->select(' jln.id       AS loc_id ');
        $query->select(' !(ISNULL(jln.latitude) AND ISNULL(jln.longitude))  AS indGeocode ');
        $query->from(' #__joaktree_locations  jln ');

        $query->innerJoin(
            ' ( SELECT jpe.person_id     AS person_id_1 '
                    .'   ,      NULL              AS person_id_2 '
                    .'   ,      jpe.code          AS code '
                    .'   ,      '.$this->_db->Quote('person').' AS level '
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
                    .'   UNION '
                    .'   SELECT jre.person_id_1   AS person_id_1 '
                    .'   ,      jre.person_id_2   AS person_id_2 '
                    .'   ,      jre.code          AS code '
                    .'   ,      '.$this->_db->Quote('relation').' AS level '
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
                    .'   ) AS iv_event '
                    .' ON (   iv_event.loc_id = jln.id ) '
        );

        $query->innerJoin(
            ' #__joaktree_tree_persons jtp '
                         .' ON (   jtp.app_id    = iv_event.app_id '
                         .'    AND jtp.tree_id   = '.(int) $treeId.' '
                         .'    AND jtp.person_id IN (iv_event.person_id_1, iv_event.person_id_2) '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_trees      jte '
                         .' ON (   jte.app_id    = jtp.app_id '
                         .'    AND jte.id        = jtp.tree_id '
                         .'    AND jte.published = true '
                         .'    AND jte.access    IN ' .$levels.' '
                         .'    ) '
        );

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

        // Get the WHERE clauses for the query + GROUP BY
        $query      	=  $this->_buildContentWhere($query);
        $query->order(' jln.value ');

        return $query;
    }

    private function _buildContentWhere($query)
    {
        $app 		= Factory::getApplication('site');

        // always from the request
        $tmp		= $app->input->get('filter', null, 'string');

        // if nothing found, check the cookie -- only when we use cookies
        if (!isset($tmp)) {
            $params 	= $app->getParams();

            if ($params->get('indCookies', true)) {
                $cookie = new Cookie();
                $tmp = $cookie->get('jt_loc_index', 0, 'int');
            }
        }

        // if nothing found, we use 0
        $filterId = (!isset($tmp)) ? 0 : (int) $tmp;
        $index  	= $this->getLocationIndex();
        $filters	= $index[$filterId];

        $query->where(' jln.indDeleted = 0 ');

        if ($filters) {
            if (count($filters) == 1) {
                $query->where(' jln.indexLoc =  "'.array_shift($filters).'" ');
            } else {
                $query->where(' jln.indexLoc IN ("'.implode('","', $filters).'" ) ');
            }
        }

        return $query;
    }

    public function getLocationlist()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_locationlist)) {
            $query = $this->_buildquery();
            $this->_locationlist = $this->_getList($query);
        }

        return $this->_locationlist;
    }

    public function getTreeinfo()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_treeinfo)) {
            $query	= $this->_db->getquery(true);
            $query->select(' * ');
            $query->from(' #__joaktree_trees ');
            $query->where(' id = :treeid');
            $query->where(' access IN ' . JoaktreeHelper::getUserAccessLevels().' ');
            $treeid = $this->getTreeId();
            $query->bind(':treeid', $treeid, \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setquery($query);
            $this->_treeinfo = $this->_db->loadObject();
        }

        return $this->_treeinfo;
    }

    public function getMenus()
    {
        static $_menuTreeId 	= array();

        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId) == 0) {
            $_menuTreeId = JoaktreeHelper::getMenus('list');
        }

        return $_menuTreeId;
    }

    public function getLastUpdate()
    {
        return JoaktreeHelper::lastUpdateDateTime();
    }
}
