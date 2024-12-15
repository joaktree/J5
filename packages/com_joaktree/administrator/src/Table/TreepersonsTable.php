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
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class TreepersonsTable extends Table implements VersionableTableInterface
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

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * Table instance.
     *
     * @param	boolean True to update fields even if they are null.
     * @return	boolean	True on success.
     * @since	1.0
     * @link	http://docs.joomla.org/Table/store
     */
    public function store($updateNulls = false)
    {
        // always an insert
        try {
            $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        } catch (\Exception $e) {
            $msg = Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $e->getMessage());
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
