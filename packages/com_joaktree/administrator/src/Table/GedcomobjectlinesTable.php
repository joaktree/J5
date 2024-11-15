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

class GedcomobjectlinesTable extends Table implements VersionableTableInterface
{
	var $id 		= null;
	var $object_id	= null;
	var $order_nr	= null;
	var $level		= null;
	var $tag		= null;
	var $value 		= null;
	var $subtype	= null;

	function __construct( DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.gedcom_objectlines';
		parent::__construct('#__joaktree_gedcom_objectlines', 'id', $db);
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

