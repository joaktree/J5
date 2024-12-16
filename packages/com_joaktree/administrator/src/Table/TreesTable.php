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
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class TreesTable extends JoaktreeTable implements VersionableTableInterface
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
