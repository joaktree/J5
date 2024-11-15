<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_person_events.php
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

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class PersoneventsTable extends Table
{
    public $app_id			= null; // PK
    public $person_id		= null; // PK
    public $orderNumber	= null; // PK
    public $code			= null;
    public $indNote		= 0; // pascal
    public $indCitation	= null;
    public $type			= null;
    public $eventDate		= null;
    public $loc_id			= null;
    public $location		= null;
    public $value			= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.person_events';
        $pk = array('app_id', 'person_id', 'orderNumber');
        parent::__construct('#__joaktree_person_events', $pk, $db);
    }

    public function deleteEvents($person_id)
    {
        if ($person_id == null) {
            return false;
        } else {
            $query = $this->_db->getQuery(true);
            $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
            $query->where(' app_id    = '.$this->app_id.' ');
            $query->where(' person_id = '.$this->_db->quote($person_id).' ');

            $this->_db->setQuery($query);
            $result = $this->_db->execute(); //$this->_db->query();
        }

        if ($result) {
            return true;
        } else {
            return $this->setError($this->$table->getError()); //$this->_db->getErrorMsg());
        }
    }

    public function check($cit = false)
    {
        // mandatory fields
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->person_id)) {
            return false;
        }
        if (empty($this->orderNumber)) {
            return false;
        }
        if (empty($this->code)) {
            return false;
        }

        if (!$this->checkLocation()) {
            return false;
        }

        if (!$this->checkNotesAndReferences()) {
            return false;
        }

        return true;
    }

    public function checkLocation()
    {
        // check for locations
        //$this->loc_id = TableJoaktree_locations::checkLocation($this->location);
        // $table = Table::getInstance('LocationsTable','Joaktree\\Component\\Joaktree\\Administrator\\Table\\', array('dbo' => $this->_db));
        $table	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Locations');

        $this->loc_id = $table->checkLocation($this->location);
        return true;
    }

    private function checkNotesAndReferences()
    {
        // check for citations
        $query = $this->_db->getQuery(true);
        $query->select(' COUNT(jcn.objectOrderNumber) AS indCit ');
        $query->from(' #__joaktree_citations jcn ');
        $query->where(' jcn.objectType  = '.$this->_db->quote('personEvent').' ');
        $query->where(' jcn.objectOrderNumber = '.$this->orderNumber.' ');
        $query->where(' jcn.app_id      = '.$this->app_id.' ');
        $query->where(' jcn.person_id_1 = '.$this->_db->quote($this->person_id).' ');

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
        $this->indCitation = ($result) ? 1 : 0; // pascal

        // check for notes
        $query->clear();
        $query->select(' COUNT(jpe.orderNumber) AS indNot ');
        $query->from(' #__joaktree_person_notes jpe ');
        $query->where(' jpe.app_id     = '.$this->app_id.' ');
        $query->where(' jpe.person_id  = '.$this->_db->quote($this->person_id).' ');
        $query->where(' jpe.eventOrderNumber  = '.$this->orderNumber.' ');

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
        $this->indNote = ($result) ? 1 : 0; // pascal

        return true;
    }

    public function delete($pks = null)
    {
        // delete citations
        $query = $this->_db->getQuery(true);
        $query->delete(' #__joaktree_citations ');
        $query->where(' objectType  = '.$this->_db->quote('personEvent').' ');
        $query->where(' objectOrderNumber = '.$this->orderNumber.' ');
        $query->where(' app_id      = '.$this->app_id.' ');
        $query->where(' person_id_1 = '.$this->_db->quote($this->person_id).' ');

        $this->_db->setQuery($query);
        $result = $this->_db->execute(); //$this->_db->query();

        // deletenotes
        $query->clear();
        $query->delete(' #__joaktree_person_notes ');
        $query->where(' app_id     = '.$this->app_id.' ');
        $query->where(' person_id  = '.$this->_db->quote($this->person_id).' ');
        $query->where(' eventOrderNumber  = '.$this->orderNumber.' ');

        $this->_db->setQuery($query);
        $result = $this->_db->execute(); //$this->_db->query();

        // ready to delete
        $ret = parent::delete();
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
