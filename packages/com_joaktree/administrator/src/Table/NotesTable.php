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

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class NotesTable extends JoaktreeTable
{
    public $app_id		= null; // PK
    public $id			= null; // PK
    public $value		= null;

    public function __construct(&$db)
    {
        $this->typeAlias = 'com_joaktree.notes';
        $pk = array('app_id', 'id');
        parent::__construct('#__joaktree_notes', $pk, $db);
    }

    public function insert()
    {
        $ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        return $ret;
    }

    public function update()
    {
        $ret = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key);
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
