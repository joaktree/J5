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

class RepositoriesTable extends JoaktreeTable
{
    public $app_id		= null; // PK
    public $id 		= null; // PK
    public $name 		= null;
    public $website	= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.repositories';
        $pk = array('app_id', 'id');
        parent::__construct('#__joaktree_repositories', $pk, $db);
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
            return $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, true);
        } catch (\Exception $e) {
            return $this->setError('update '.$this->_tbl.': Error -> '.$e->getMessage());
        }
    }

    public function check($cit = false)
    {
        // primary key is mandatory
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->id)) {
            return false;
        }

        // Set name - name is mandatory
        $this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);
        if (empty($this->name)) {
            return false;
        }

        // Set website
        $this->website = htmlspecialchars_decode($this->website, ENT_QUOTES);

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
