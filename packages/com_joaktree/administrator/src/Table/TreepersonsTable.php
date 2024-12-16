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
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class TreepersonsTable extends JoaktreeTable implements VersionableTableInterface
{
    public $id			= null;
    public $app_id		= null;
    public $tree_id	= null;
    public $person_id	= null;
    public $type 		= null;
    public $lineage 	= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.tree_persons';
        parent::__construct('#__joaktree_tree_persons', 'id', $db);
    }

    public function truncate($app_id = null)
    {
        if ($app_id) {
            $query = $this->_db->getQuery(true);
            $query->delete(' '.$this->_db->quoteName($this->_tbl).' ');
            $query->where(' app_id = :appid');
            $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
        } else {
            $query = 'TRUNCATE ' . $this->_tbl;
        }

        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }

    public function store($updateNulls = false)
    {
        // always an insert
        try {
            $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        } catch (\Exception $e) {
            $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $e->getMessage()));
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
