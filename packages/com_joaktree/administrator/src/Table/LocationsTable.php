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
namespace Joaktree\Component\Joaktree\Administrator\Table;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;

class LocationsTable extends Table implements VersionableTableInterface
{
    public $id 				= null;
    public $indexLoc           = null;
    public $value				= null;
    public $latitude			= null;
    public $longitude			= null;
    public $indServerProcessed = null;
    public $indDeleted			= 0; // pascal
    public $results			= null;
    public $resultValue		= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.locations';
        parent::__construct('#__joaktree_locations', 'id', $db);
    }

    protected function getSettings()
    {
        static $settings;
        if (!isset($settings)) {
            $settings =  MBJGeocode::getKeys();
            if (isset($settings->geocode)) {
                $geocodeAPIkey	= $settings->geocode.'APIkey';
                if ((empty($settings->geocode))
                   || (
                       !empty($settings->geocode)
                      && isset($settings->$geocodeAPIkey)
                      && empty($settings->$geocodeAPIkey)
                   )
                ) {
                    $settings->indGeocode = false;
                } else {
                    $settings->indGeocode = true;
                }
            } else {
                $settings = new \stdClass();
                $settings->indGeocode = false;
            }
        }

        return $settings;
    }

    public function checkLocation($value)
    {
        if (!isset($value) || empty($value)) {
            // no location -> no location id
            return 0; // pascal
        }
        // check for locations
        $query = $this->_db->getQuery(true);
        $query->select(' jln.id ');
        $query->from(' #__joaktree_locations jln ');
        $query->where(' jln.value       = '.$this->_db->quote($value).' ');
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
        if (!$result) {
            // we are inserting the location
            $query->clear();
            $query->insert(' #__joaktree_locations ');
            $query->set(' value       = '.$this->_db->quote($value).' ');
            $query->set(' indexLoc    = '.$this->_db->quote(mb_strtoupper(mb_substr($value, 0, 1))).' ');
            // check for coordinates
            $settings = self::getSettings();
            if ($settings->indGeocode) {
                $data		 = new \StdClass();
                $data->value = $value;
                $service 	 = MBJGeocode::getInstance();
                $status 	 = $service->_('findLocation', $data);
                if ($status == 'found') {
                    $query->set(' latitude       	= '.$data->latitude.' ');
                    $query->set(' longitude       	= '.$data->longitude.' ');
                    $query->set(' indServerProcessed	= '.$data->indServerProcessed.' ');
                    $query->set(' results      		= '.$data->results.' ');
                    $query->set(' resultValue      	= '.$this->_db->quote($data->result_address).' ');
                } else {
                    $query->set(' indServerProcessed	= '.$data->indServerProcessed.' ');
                    $query->set(' results      		= '.$data->results.' ');
                }
            }
            $this->_db->setQuery($query);
            $this->_db->execute(); //$this->_db->query();
            // ... and retrieving the new id
            $query->clear();
            $query->select(' jln.id ');
            $query->from(' #__joaktree_locations jln ');
            $query->where(' jln.value       = '.$this->_db->quote($value).' ');
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        }
        return $result;
    }
    public function bind($src, $ignore = array())
    {
        $src['latitude'] = (isset($src['latitude']) && !empty($src['latitude']))
                             ? $src['latitude']
                             : null;
        $src['longitude'] = (isset($src['longitude']) && !empty($src['longitude']))
                             ? $src['longitude']
                             : null;
        $src['resultValue'] = (isset($src['resultValue']) && !empty($src['resultValue']))
                             ? $src['resultValue']
                             : null;
        $src['indServerProcessed'] = (
            isset($src['latitude']) && !empty($src['latitude'])
            &&  isset($src['longitude']) && !empty($src['longitude'])
        ) ? true : false;
        // when the item is indicated as being deleted, the indication server processed is overriden.
        $src['indServerProcessed'] = ($src['indDeleted']) ? true : $src['indServerProcessed'];
        return parent::bind($src);
    }
    public function store($updateNulls = false)
    {
        if (!empty($this->value)) {
            $this->indexLoc = mb_strtoupper(mb_substr($this->value, 0, 1));
        }
        return parent::store($updateNulls);
    }
    /**
     * Get the type alias for the table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
