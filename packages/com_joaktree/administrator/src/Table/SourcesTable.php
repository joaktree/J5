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

class SourcesTable extends JoaktreeTable
{
    public $app_id			= null; // PK
    public $id				= null; // PK
    public $title			= null;
    public $author			= null;
    public $publication	= null;
    public $information	= null;
    public $repo_id		= null;
    public $abbr	= null;
    public $media	= null;
    public $note	= null;
    public $www	= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.sources';
        $pk = array('app_id', 'id');
        parent::__construct('#__joaktree_sources', $pk, $db);
    }


    public function insert()
    {
        try {
            return $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        } catch (\Exception $e) {
            return $this->setError('Insert '.$this->_tbl.': Error -> '.$e->getMessage());
        }
    }

    public function update()
    {
        try {
            return $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key);
        } catch (\Exception $e) {
            return $this->setError('Update '.$this->_tbl.': Error -> '.$e->getMessage());
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
