<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_maps.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */

namespace Joaktree\Component\Joaktree\Administrator\Table;

defined('_JEXEC') or die('Restricted access');
use Joomla\Registry\Registry; //replace JRegistry
use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class MapsTable extends Table implements VersionableTableInterface
{
    public $id 				= null;
    public $name				= null;
    public $selection			= null;
    public $service			= null;
    public $app_id				= null;
    public $tree_id	 		= null;
    public $person_id	 		= null;
    public $subject	 		= null;
    public $relations          = null;
    public $period_start 		= null;
    public $period_end	 		= null;
    public $excludePersonEvents   = null;
    public $excludeRelationEvents = null;
    public $params				= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.maps';
        parent::__construct('#__joaktree_maps', 'id', $db);
    }

    /**
     * Overloaded bind function
     *
     * @param	array		$hash named array
     * @return	null|string	null is operation was satisfactory, otherwise returns an error
     * @see Table:bind
     * @since 1.5
     */
    public function bind($array, $ignore = array())
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);

            $array['params'] = (string)$registry;
        }

        return parent::bind($array, $ignore);
    }

    public function check()
    {
        // mandatory fields
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->name)) {
            return false;
        }
        if (empty($this->selection)) {
            return false;
        }
        if (empty($this->service)) {
            return false;
        }
        /*if (empty($this->events)) { //20241024
            return false;
        }*/
        if (!empty($this->period_start) && ((int) $this->period_start > 9999)) {
            return false;
        } else {
            $this->period_start = (int) $this->period_start;
        }
        if (!empty($this->period_end) && ((int) $this->period_end > 9999)) {
            return false;
        } else {
            $this->period_end = (int) $this->period_end;
        }

        return true;
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
