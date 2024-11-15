<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_tree_persons.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */

namespace Joaktree\Component\Joaktree\Administrator\Table;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Access\Rules;		//replace JRules
use Joomla\CMS\Language\Text;		// replace JText
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
            $query->where(' app_id = '.(int) $app_id.' ');
        } else {
            $query = 'TRUNCATE ' . $this->_tbl;
        }

        $this->_db->setQuery($query);
        $this->_db->execute(); //$this->_db->query();

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
        $stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);

        // If the store failed return false.
        if (!$stored) {
            $e = new \Exception(Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
            $this->setError($e);
            return false;
        }

        // If the table is not set to track assets return true.
        if (!$this->_trackAssets) {
            return true;
        }

        if ($this->_locked) {
            $this->_unlock();
        }

        //
        // Asset Tracking
        //

        $parentId	= $this->_getAssetParentId();
        $name		= $this->_getAssetName();
        $title		= $this->_getAssetTitle();

        $asset	= Table::getInstance('Asset');
        //$asset	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Asset');

        $asset->loadByName($name);

        // Check for an error.
        if ($error = $asset->getError()) {
            $this->setError($error);
            return false;
        }

        // Specify how a new or moved node asset is inserted into the tree.
        if (empty($this->asset_id) || $asset->parent_id != $parentId) {
            $asset->setLocation($parentId, 'last-child');
        }

        // Prepare the asset to be stored.
        $asset->parent_id	= $parentId;
        $asset->name		= $name;
        $asset->title		= $title;
        if ($this->_rules instanceof Rules) {
            $asset->rules = (string) $this->_rules;
        }

        if (!$asset->check() || !$asset->store($updateNulls)) {
            $this->setError($asset->getError());
            return false;
        }

        if (empty($this->asset_id)) {
            // Update the asset_id field in this table.
            $this->asset_id = (int) $asset->id;

            $query = $this->_db->getQuery(true);
            $query->update($this->_db->quoteName($this->_tbl));
            $query->set('asset_id = '.(int) $this->asset_id);
            $query->where($this->_db->quoteName($k).' = '.(int) $this->$k);
            $this->_db->setQuery($query);

            if (!$this->_db->execute()) { //$this->_db->query())
                $e = new \Exception(Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED_UPDATE_ASSET_ID', $this->_db->getErrorMsg()));
                $this->setError($e);
                return false;
            }
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
