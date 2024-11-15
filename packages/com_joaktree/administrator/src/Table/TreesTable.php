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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class TreesTable extends Table implements VersionableTableInterface
{
    public $id 				= null;
    public $app_id				= null;
    public $asset_id           = null;
    public $root_person_id		= null;
    public $published 			= null;
    public $name 				= null;
    public $theme_id			= null;
    public $indGendex			= null;
    public $indPersonCount		= null;
    public $indMarriageCount	= null;
    public $access				= null;
    public $holds				= null;
    public $robots				= null;
    public $catid          	= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.trees';
        parent::__construct('#__joaktree_trees', 'id', $db);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return  string
     */
    protected function _getAssetName()
    {
        return 'com_joaktree.application.'.(int) $this->app_id.'.tree.'.(int) $this->id;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   Table	A Table object for the asset parent.
     *
     * @return  integer
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        $asset	= Table::getInstance('Asset');
        //$asset	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Assets');
        $asset->loadByName('com_joaktree.application.'.(int) $this->app_id);
        $parentId = empty($asset->id) ? 1 : $asset->id;
        return $parentId;
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
