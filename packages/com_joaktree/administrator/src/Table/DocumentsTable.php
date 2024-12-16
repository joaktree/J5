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

class DocumentsTable extends JoaktreeTable
{
    public $id 			= null;
    public $app_id			= null;
    public $file			= null;
    public $fileformat		= null;
    public $title	 		= null;
    public $indCitation	= null;
    public $note_id    	= null;
    public $note 			= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.documents';
        $pk = array('id', 'app_id');
        parent::__construct('#__joaktree_documents', $pk, $db);
    }

    public function store($updateNulls = true)
    {
        $query = $this->_db->getQuery(true);

        if (!isset($this->id)) {
            // Fetch the document primary key with the unique key
            $query->clear();
            $query->select(' id ');
            $query->from(' '.$this->_tbl.' ');
            $query->where(' file = :file');
            $query->where(' app_id = :appid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':file', $this->file, \Joomla\Database\ParameterType::STRING);

            // fetch and save the primary key
            $this->_db->setQuery($query);
            $document_id = $this->_db->loadResult();

            if ($document_id) {
                // record exists, set the primary key for updating the record with store
                $this->id = $document_id;
            } else {
                // new document record
                $query->clear();
                $query->select(' MAX( id ) ');
                $query->from(' '.$this->_tbl.' ');
                $query->where(' app_id = :appid');
                $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);

                // fetch and save the primary key
                $this->_db->setQuery($query);
                $max_id = $this->_db->loadResult();

                $new_id = (int) trim($max_id, 'D');
                $new_id = $new_id + 1;

                // unset the primary key, so the store function will insert a new record
                $this->id = sprintf('D%010s', $new_id);

            }
        }

        // Store the document
        $ret = parent::store();

        if ($ret) {
            // everything went ok
            return $this->id;
        } else {
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
