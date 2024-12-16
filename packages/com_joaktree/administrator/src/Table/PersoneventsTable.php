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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class PersoneventsTable extends JoaktreeTable
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
        }
        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
        $query->where(' app_id    = :appid');
        $query->where(' person_id = :personid');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $person_id, \Joomla\Database\ParameterType::STRING);
        try {
            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (\Exception $e) {
            $msg = Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage());
            JoaktreeHelper::addLog($msg, 'JoaktreeTable') ;
            $this->setError($msg);
            return false;
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
        $query->where(' jcn.objectOrderNumber = :ordernumber');
        $query->where(' jcn.app_id      = :appid');
        $query->where(' jcn.person_id_1 = :personid');
        $query->bind(':ordernumber', $this->orderNumber, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->person_id, \Joomla\Database\ParameterType::STRING);
        $result = false;
        try {
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }
        $this->indCitation = ($result) ? 1 : 0; // pascal
        // check for notes
        $query->clear();
        $query->select(' COUNT(jpe.orderNumber) AS indNot ');
        $query->from(' #__joaktree_person_notes jpe ');
        $query->where(' jpe.app_id     = :appid');
        $query->where(' jpe.person_id  = :personid');
        $query->where(' jpe.eventOrderNumber  = :ordernumber');
        $query->bind(':ordernumber', $this->orderNumber, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->person_id, \Joomla\Database\ParameterType::STRING);
        $result = false;
        try {
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }
        $this->indNote = ($result) ? 1 : 0; // pascal

        return true;
    }

    public function delete($pks = null)
    {
        // delete citations
        $query = $this->_db->getQuery(true);
        $query->delete(' #__joaktree_citations ');
        $query->where(' objectType  = '.$this->_db->quote('personEvent').' ');
        $query->where(' objectOrderNumber = :ordernumber');
        $query->where(' app_id      = :appid');
        $query->where(' person_id_1 = :personid');
        $query->bind(':ordernumber', $this->orderNumber, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->person_id, \Joomla\Database\ParameterType::STRING);
        try {
            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }
        // deletenotes
        $query->clear();
        $query->delete(' #__joaktree_person_notes ');
        $query->where(' app_id     = :appid');
        $query->where(' person_id  = :personid');
        $query->where(' eventOrderNumber  = :ordernumber');
        $query->bind(':ordernumber', $this->orderNumber, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->person_id, \Joomla\Database\ParameterType::STRING);
        try {
            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }
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
