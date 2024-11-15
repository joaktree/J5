<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_repositories.php
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

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class RepositoriesTable extends Table
{
	var $app_id		= null; // PK
	var $id 		= null; // PK
	var $name 		= null;
	var $website	= null;

	function __construct(  DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.repositories';
		$pk = array('app_id', 'id');
		parent::__construct('#__joaktree_repositories', $pk, $db);
	}

	function insert() {
		$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		return $ret;
	}
	
	function update() {
		$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, true );
		return $ret;
	}
	
	function check($cit=false)
	{
		jimport('joomla.filter.output');
	
		// primary key is mandatory
		if (empty($this->app_id)) {
			return false;
		}
		if (empty($this->id)) {
			return false;
		}
		
		// Set name - name is mandatory
		$this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);
		if (empty($this->name)) {
			return false;
		}

		// Set website
		$this->website = htmlspecialchars_decode($this->website, ENT_QUOTES);

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