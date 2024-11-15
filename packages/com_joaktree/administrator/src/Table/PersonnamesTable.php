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

class PersonnamesTable extends Table
{
    public $app_id			= null; // PK
    public $person_id		= null; // PK
    public $orderNumber	= null; // PK
    public $code			= null;
    public $indNote		= 0; // pascal
    public $indCitation	= null;
    public $eventDate		= null;
    public $value			= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.person_names';
        $pk = array('app_id', 'person_id', 'orderNumber');
        parent::__construct('#__joaktree_person_names', $pk, $db);
    }

    public function deleteNames($person_id)
    {
        if ($person_id == null) {
            return false;
        } else {
            $query = $this->_db->getQuery(true);
            $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
            $query->where(' app_id    = '.$this->app_id.' ');
            $query->where(' person_id = '.$this->_db->quote($person_id).' ');
            try {
                $this->_db->setQuery($query);
                $result = $this->_db->execute();
            } catch (\Exception $e) {
                $this->setError($e->getMessage());
                return false;
            }
        }
        return true;
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

        if (!$this->checkNotesAndReferences()) {
            return false;
        }

        return true;
    }

    private function checkNotesAndReferences()
    {
        // check for citations
        $query = $this->_db->getQuery(true);
        $query->select(' COUNT(jcn.objectOrderNumber) AS indCit ');
        $query->from(' #__joaktree_citations jcn ');
        $query->where(' jcn.objectType  = '.$this->_db->quote('personName').' ');
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
        $query->where(' jpe.nameOrderNumber  = '.$this->orderNumber.' ');

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
        $this->indNote = ($result) ? 1 : 0; // pascal

        return true;
    }

    public function delete($pk = null)
    {
        // delete citations
        $query = $this->_db->getQuery(true);
        $query->delete(' #__joaktree_citations ');
        $query->where(' objectType  = '.$this->_db->quote('personName').' ');
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
        $query->where(' nameOrderNumber  = '.$this->orderNumber.' ');

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
