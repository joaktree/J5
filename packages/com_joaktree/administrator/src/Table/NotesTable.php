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

class NotesTable extends Table
{
	var $app_id		= null; // PK
	var $id			= null; // PK
	var $value		= null;

	function __construct( &$db) {
        $this->typeAlias = 'com_joaktree.notes';
		$pk = array('app_id', 'id');
		parent::__construct('#__joaktree_notes', $pk, $db);
	}

	function insert() {
		$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		return $ret;
	}
	
	function update() {
		$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key ); 
		return $ret;
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