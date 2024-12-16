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

define("EMPTY_COLUMN", "EMPTY");
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class CitationsTable extends JoaktreeTable
{
    public $objectType		= null; // PK
    public $objectOrderNumber	= null; // PK
    public $app_id			= null; // PK
    public $person_id_1	= null; // PK
    public $person_id_2	= null; // PK
    public $source_id		= null; // PK
    public $orderNumber	= null; // PK
    public $dataQuality	= null;
    public $page		= null;
    public $quotation		= null;
    public $note		= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.citations';
        $pk = array('objectType', 'objectOrderNumber', 'app_id', 'person_id_1', 'person_id_2', 'source_id', 'orderNumber');
        parent::__construct('#__joaktree_citations', $pk, $db);
    }

    public function check($cit = false)
    {
        // mandatory fields
        if (empty($this->objectType)) {
            return false;
        }
        if (empty($this->objectOrderNumber)) {
            if (!$cit = true) {
                return false;
            }
        }
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->person_id_1)) {
            return false;
        }
        if ((empty($this->person_id_2))) {
            if (!$cit = true) {
                return false;
            }
        }
        if (empty($this->source_id)) {
            return false;
        }
        if (empty($this->orderNumber)) {
            return false;
        }

        return true;
    }

    public function deletePersonCitations($person_id)
    {
        if ($person_id == null) {
            return false;
        }
        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
        $query->where(' app_id      = :appid');
        $query->where(' person_id_1 = :personid');
        $query->where(' person_id_2 = '.$this->_db->quote(EMPTY_COLUMN).' ');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $person_id, \Joomla\Database\ParameterType::STRING);
        try {
            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (\Exception $e) {
            $msg = Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage());
            JoaktreeHelper::addLog($msg, 'JoaktreeTable') ;
            return false;
        }
    }

    public function truncateRelationCitations($app_id)
    {
        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
        $query->where(' app_id      = :appid');
        $query->where(' person_id_2 <> '.$this->_db->quote('EMPTY').' ');
        $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
        try {
            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (\Exception $e) {
            $msg = Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage());
            JoaktreeHelper::addLog($msg, 'JoaktreeTable') ;
            return false;
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
