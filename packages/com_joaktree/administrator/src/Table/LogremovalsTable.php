<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_logremovals.php
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
use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class LogremovalsTable extends Table implements VersionableTableInterface
{
	var $id 				= null;
	var $app_id				= null;
	var $object_id			= null;
	var $object				= null;
	var $description		= null;
	
	function __construct( DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.logremovals';
		parent::__construct('#__joaktree_logremovals', 'id', $db);
	}
	
	public function store($updateNulls = false) {
		$params = JoaktreeHelper::getJTParams($this->app_id);
		$indLogging = $params->get('indLogging');
		
		if ($indLogging) {
			// Logging is switched on
			$ret = parent::store();
		} else {
			// Logging is switched off
			$ret = true;	
		}
		
		return $ret;
	}
	
	public function check() {
		// mandatory fields
		if (empty($this->app_id)) {
			return false;
		}
		if (empty($this->object_id)) {
			return false;
		}
		if (empty($this->object)) {
			return false;
		}
		
		if (empty($this->description)) {
			return false;
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
?>