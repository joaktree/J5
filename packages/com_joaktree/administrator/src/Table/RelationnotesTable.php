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
use Joomla\Database\DatabaseDriver;

class RelationnotesTable extends Table
{
	var $app_id			= null; // PK
	var $person_id_1	= null; // PK
	var $person_id_2	= null; // PK
	var $orderNumber	= null; // PK
	var $indCitation	= null;
	var $eventOrderNumber	= null;
	var $note_id	= null;
	var $value		= null;

	function __construct( DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.relation_notes';
        $pk = array('app_id', 'person_id_1', 'person_id_2', 'orderNumber');
		parent::__construct('#__joaktree_relation_notes', $pk, $db);
	}
	
	public function check($cit=false) {
		// mandatory fields
		if (empty($this->app_id)) {
			return false;
		}
		if (empty($this->person_id_1)) {
			return false;
		}
		if (empty($this->person_id_2)) {
			return false;
		}
		if (empty($this->orderNumber)) {
			return false;
		}
		
		// for future use
		//if (!$this->checkNotesAndReferences()) {
		//	return false;
		//}
			
		return true;
	}
	
	function deletePersonNotes($person_id) {
		if ($person_id == null) {
			return false;
		} else {
			$query = $this->_db->getQuery(true);
			$query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
			$query->where( ' app_id = :appid');
			$query->where( ' (  person_id_1 = :personid OR person_id_2 = :personid )' );
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

	function deleteNotes($person_id_1, $person_id_2) {
		if (  ($person_id_1 == null) 
		   or ($person_id_2 == null) 
		   or ($person_id_1 == $person_id_2) ) {
			return false;
		} else {
			if ($person_id_1 < $person_id_2) {
				$pid1 = $person_id_1;
				$pid2 = $person_id_2;
			} else {
				$pid1 = $person_id_2;
				$pid2 = $person_id_1;				
			}

			$query = $this->_db->getQuery(true);
			$query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
			$query->where( ' app_id = :appid');
			$query->where( ' person_id_1 = :personid1');
			$query->where( ' person_id_2 = :personid2');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid1', $pid1, \Joomla\Database\ParameterType::STRING);
            $query->bind(':personid2', $pid2, \Joomla\Database\ParameterType::STRING);
			$this->_db->setQuery( $query );
			$result = $this->_db->execute(); //$this->_db->query();       
		}

		if ($result) {
			return true;
		} else {
			return $this->setError($this->$table->getError()); //$this->_db->getErrorMsg());
		}
	}
	
	function truncateApp($app_id) {
		$query = $this->_db->getQuery(true);
		$query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
		$query->where( ' app_id = :appid');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
		$this->_db->setQuery( $query );
		$result = $this->_db->execute(); //$this->_db->query();       

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