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
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class RelationsTable extends JoaktreeTable
{
    public $app_id			= null; // PK
    public $person_id_1	= null; // PK
    public $person_id_2	= null; // PK
    public $type			= null;
    public $subtype		= null;
    public $family_id		= null;
    public $indNote		= 0; // pascal
    public $indCitation	= 0; // pascal
    public $orderNumber_1	= null;
    public $orderNumber_2	= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.relations';
        $pk = array('app_id', 'person_id_1', 'person_id_2');
        parent::__construct('#__joaktree_relations', $pk, $db);
    }

    public function check($cit = false)
    {
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

        return true;
    }

    public function deletePersonRelations($person_id)
    {
        if ($person_id == null) {
            return false;
        }
        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
        $query->where(' app_id = :appid');
        $query->where(' (  person_id_1 = :personid OR person_id_2 = :personid )');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $person_id, \Joomla\Database\ParameterType::STRING);
        try {
            $this->_db->setQuery($query);
            return $this->_db->execute(); //$this->_db->query();
        } catch (\Exception $e) {
            return $this->setError('deletePersonRelations '.$this->_tbl.': Error -> '.$e->getMessage());
        }
    }

    public function deleteRelations($person_id_1, $person_id_2)
    {
        if (($person_id_1 == null)
           or ($person_id_2 == null)
           or ($person_id_1 == $person_id_2)) {
            return false;
        }
        if ($person_id_1 < $person_id_2) {
            $pid1 = $person_id_1;
            $pid2 = $person_id_2;
        } else {
            $pid1 = $person_id_2;
            $pid2 = $person_id_1;
        }

        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
        $query->where(' app_id = :appid');
        $query->where(' person_id_1 = :personid1');
        $query->where(' person_id_2 = :personid2');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid1', $pid1, \Joomla\Database\ParameterType::STRING);
        $query->bind(':personid2', $pid2, \Joomla\Database\ParameterType::STRING);
        try {
            $this->_db->setQuery($query);
            return $this->_db->execute();
        } catch (\Exception $e) {
            return $this->setError('deletePersonRelations '.$this->_tbl.': Error -> '.$e->getMessage());
        }
    }

    public function truncTable()
    {
        $query = 'TRUNCATE ' . $this->_tbl;
        $this->_db->setQuery($query);
        $result = $this->_db->execute(); //$this->_db->query();

        if ($result) {
            // everything went fine
            return true;
        } else {
            // something went wrong -> error messages is returned.
            return $this->setError($this->$table->getError()); //$this->_db->getErrorMsg());
        }
    }

    public function truncateApp($app_id)
    {
        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
        $query->where(' app_id = :appid');
        $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
        try {
            $this->_db->setQuery($query);
            return $this->_db->execute();
        } catch (\Exception $e) {
            return $this->setError('deletePersonRelations '.$this->_tbl.': Error -> '.$e->getMessage());
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
