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

use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class PersondocumentsTable extends Table
{
	var $app_id			= null; // PK
	var $person_id		= null; // PK
	var $document_id	= null; // PK

	function __construct( DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.person_documents';
		$pk = array('app_id', 'person_id', 'document_id');
		parent::__construct('#__joaktree_person_documents', $pk, $db);
	}

	function deletedocuments($person_id) {
		if ($person_id == null) {
			return false;
		} else {
			$query = $this->_db->getQuery(true);
			$query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
			$query->where( ' app_id    = :appid');
			$query->where( ' person_id = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $person_id, \Joomla\Database\ParameterType::STRING);
			
			$this->_db->setQuery( $query );
			$result = $this->_db->execute(); //$this->_db->query();       
		}

		if ($result) {
			return true;
		} else {
			return $this->setError($this->$table->getError()); //$this->_db->getErrorMsg());
		}
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