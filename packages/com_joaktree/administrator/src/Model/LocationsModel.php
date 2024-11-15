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
namespace Joaktree\Component\Joaktree\Administrator\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;

class LocationsModel extends ListModel
{
    public $_data;
    public $_pagination 	= null;
    public $_total         = null;

    public function __construct()
    {
        parent::__construct();

        $app = Factory::getApplication();

        $context			= 'com_joaktree.locations.list.';
        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    private function _buildquery()
    {
        $query = $this->_db->getQuery(true);

        $query->select(' jln.* ');
        $query->from(' #__joaktree_locations  jln ');

        // WHERE, GROUP BY and ORDER BY clauses for the query
        $wheres     =  $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }

        $query->order(' '.$this->_buildContentOrderBy().' ');

        return $query;
    }

    private function _buildContentWhere()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.locations.list.';
        $filter_server	= $app->getUserStateFromRequest($context.'filter_server', 'filter_server', '', 'word');
        $filter_status	= $app->getUserStateFromRequest($context.'filter_status', 'filter_status', '', 'word');
        $search			= $app->getUserStateFromRequest($context.'search', 'search', '', 'string');
        $search			= strtolower($search);

        $where = array();

        // exclude locations in table which are marked "Deleted"
        $where[] = 'jln.indDeleted = 0 ';

        switch ($filter_server) {
            case "N" :	$where[] =   'jln.indServerProcessed = 0 ';
                break;
            case "Y" :	$where[] =   'jln.indServerProcessed = 1 ';
                break;
            default: 	break;
        }

        switch ($filter_status) {
            case "N" :	$where[] =   ' (  jln.latitude   IS NULL '
                                    .' OR jln.latitude   = 0 '
                                    .' OR jln.longitude  IS NULL '
                                    .' OR jln.longitude  = 0 '
                                    .' ) ';
                break;
            case "Y" :	$where[] =   ' (   jln.latitude   IS NOT NULL '
                                    .' AND jln.longitude  IS NOT NULL '
                                    .' ) ';
                break;
            default: 	break;
        }

        if ($search) {
            $where[] =   '(  LOWER(jln.value) LIKE '.$this->_db->Quote('%'.$search.'%').' '
                        .'OR LOWER(jln.resultValue) LIKE '.$this->_db->Quote('%'.$search.'%').' '
                        .') ';
        }

        return $where;
    }

    private function _buildContentOrderBy()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.locations.list.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jln.value', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order) {
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' ';
        } else {
            $orderby 	= ' jln.value '.$filter_order_Dir.' ';
        }

        return $orderby;
    }

    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildquery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
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
            jimport('joomla.html.pagination');
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    public function getMapSettings()
    {
        $settings = MBJService::getKeys();
        MBJService::setLanguage();

        // count locations without coordinates
        $query	= $this->_db->getQuery(true);

        $query->select(' COUNT(id) ');
        $query->from(' #__joaktree_locations ');
        $this->_db->setQuery($query);
        $total = $this->_db->loadResult();

        $query->clear();
        $query->select(' COUNT(id) ');
        $query->from(' #__joaktree_locations ');
        $query->where(' latitude  IS NOT NULL ');
        $query->where(' latitude  <> 0 ');
        $query->where(' longitude IS NOT NULL ');
        $query->where(' longitude <> 0 ');
        $this->_db->setQuery($query);
        $valid = $this->_db->loadResult();

        $settings->valid     = (int) $valid;
        $settings->total     = (int) $total;
        $settings->invalid   = $settings->total - $settings->valid;
        $settings->validpc   = ($settings->total) ? round(100 * ($settings->valid / $settings->total), 0) : 0;
        $settings->invalidpc = ($settings->total) ? round(100 * ($settings->invalid / $settings->total), 0) : 0;

        return $settings;
    }

    public function geocode()
    {
        $service = MBJGeocode::getInstance();
        $size = $service->getMaxLoadSize();

        // Fetch addresses
        $query = $this->_db->getQuery(true);
        $query->select(' jln.* ');
        $query->from(' #__joaktree_locations  jln ');
        $query->where('  indServerProcessed = 0 ');
        $query->where(
            '  (  jln.latitude   IS NULL '
                     .'  OR jln.latitude   = 0 '
                     .'  OR jln.longitude  IS NULL '
                     .'  OR jln.longitude  = 0 '
                     .'  ) '
        );

        $this->_db->setQuery($query, 0, (int) $size);
        $locations = $this->_db->loadObjectList();

        $status = $service->_('findLocationBulk', $locations);

        // Iterate through the rows, geocoding each address
        foreach ($locations as $location) {
            $query->clear();
            $query->update(' #__joaktree_locations ');

            if (isset($location->longitude) && !empty($location->longitude)
               && isset($location->latitude)  && !empty($location->latitude)
            ) {
                $query->set(' longitude = '.$this->_db->escape($location->longitude).' ');
                $query->set(' latitude  = '.$this->_db->escape($location->latitude).' ');
            }

            $query->set(' results  = '.(int) $location->results.' ');
            $query->set(' resultValue  = '.(!empty($location->result_address) ? $this->_db->quote($location->result_address) : 'NULL').' ');
            $query->set(' indServerProcessed = 1 ');
            $query->where(' id = '.$location->id.' ');
            $this->_db->setQuery($query);
            $this->_db->execute(); //$this->_db->query();
        }
        // 		dumpVar($service->getLog());
        return implode("<br />", $service->getLog());
    }

    public function resetlocation()
    {
        $canDo	= JoaktreeHelper::getActions();

        if ($canDo->get('core.edit')) {
            $cids	= Factory::getApplication()->input->get('cid', null, 'array');
            $query 	= $this->_db->getQuery(true);

            foreach ($cids as $cid_num => $cid) {
                $query->clear();
                $query->update(' #__joaktree_locations ');
                $query->set(' latitude    = NULL ');
                $query->set(' longitude   = NULL ');
                $query->set(' indServerProcessed = 0 ');
                $query->set(' results     = NULL ');
                $query->set(' resultValue = NULL ');
                $query->where(' id     = '.$cid.' ');

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = '';
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    /*
    ** function for clearing the tables of unused locations
    ** of the admin table
    */
    public function purgeLocations()
    {
        $canDo	= JoaktreeHelper::getActions();

        if ($canDo->get('core.delete')) {
            $msg = '';
            $update_queries = array();

            $update_queries[] =
                 'DELETE jln.* '
                .'FROM   #__joaktree_locations  jln '
                .'WHERE  NOT EXISTS '
                .'( SELECT 1 '
                .'  FROM   #__joaktree_person_events jpt '
                .'  WHERE  jpt.loc_id = jln.id '
                .'  UNION '
                .'  SELECT 1 '
                .'  FROM   #__joaktree_relation_events jrt '
                .'  WHERE  jrt.loc_id = jln.id '
                .') ';

            $update_queries[] =
                 'INSERT INTO #__joaktree_locations (indexLoc , value) '
                .'SELECT SUBSTRING(TRIM(location), 1, 1) '
                .',      TRIM(location) '
                .'FROM   #__joaktree_person_events '
                .'WHERE  loc_id IS NULL '
                .'AND    location IS NOT NULL '
                .'UNION '
                .'SELECT SUBSTRING(TRIM(location), 1, 1) '
                .',      TRIM(location) '
                .'FROM   #__joaktree_relation_events '
                .'WHERE  loc_id IS NULL '
                .'AND    location IS NOT NULL ';

            $update_queries[] =
                 'UPDATE '
                .'  #__joaktree_person_events jpe '
                .', #__joaktree_locations     jln '
                .'SET    jpe.loc_id         = jln.id '
                .'WHERE  TRIM(jpe.location) = TRIM(jln.value) '
                .'AND    jpe.loc_id         IS NULL ';

            $update_queries[] =
                 'UPDATE '
                .'  #__joaktree_relation_events jre '
                .', #__joaktree_locations     jln '
                .'SET    jre.loc_id         = jln.id '
                .'WHERE  TRIM(jre.location) = TRIM(jln.value) '
                .'AND    jre.loc_id         IS NULL ';

            // Perform all queries - we don't care if it fails
            foreach ($update_queries as $query) {
                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = $msg;

        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

}
