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

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class PersondocumentsTable extends JoaktreeTable
{
    public $app_id			= null; // PK
    public $person_id		= null; // PK
    public $document_id	= null; // PK

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.person_documents';
        $pk = array('app_id', 'person_id', 'document_id');
        parent::__construct('#__joaktree_person_documents', $pk, $db);
    }

    public function deletedocuments($person_id)
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
            $this->_db->execute(); //$this->_db->query();
        } catch (\Exception $e) {
            $msg = Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage());
            JoaktreeHelper::addLog($msg, 'JoaktreeTable') ;
            return false;
        }
        return true;
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
