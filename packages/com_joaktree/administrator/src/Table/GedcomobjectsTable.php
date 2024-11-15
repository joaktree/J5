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

class GedcomobjectsTable extends Table implements VersionableTableInterface
{
	var $id 	= null;
	var $tag	= null;
	var $value	= null;

	function __construct( DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.gedcom_objects';
		parent::__construct('#__joaktree_gedcom_objects', 'id', $db);
	}

	function truncate() {
		$query = 'TRUNCATE ' . $this->_tbl;
		$this->_db->setQuery( $query );
		$result = $this->_db->loadResult();
		return $result;
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