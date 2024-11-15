<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_sources.php
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

class SourcesTable extends Table 
{
	var $app_id			= null; // PK
	var $id				= null; // PK
	var $title			= null;
	var $author			= null;
	var $publication	= null;
	var $information	= null;
	var $repo_id		= null;

	function __construct( DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.sources';
		$pk = array('app_id', 'id');
		parent::__construct('#__joaktree_sources', $pk, $db);
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