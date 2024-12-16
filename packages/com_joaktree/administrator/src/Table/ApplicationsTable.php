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
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class ApplicationsTable extends JoaktreeTable implements VersionableTableInterface
{
    public $id              = null;
    public $db              = null;
    public $asset_id        = null;
    public $title           = null;
    public $description     = null;
    public $programName     = null;
    public $params          = null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.applications';
        $this->db = $db;
        parent::__construct('#__joaktree_applications', 'id', $db);
    }

    /**
     * Overloaded bind function
     *
     * @param	array		$hash named array
     * @return	null|string	null is operation was satisfactory, otherwise returns an error
     * @see Table:bind
     * @since 1.5
     */
    public function bind($array, $ignore = array())
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);

            $array['params'] = (string)$registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $actions = array();
            $tmp 	 = array();
            $tmp[0]  = '';

            foreach ($array['rules'] as $action => $identities) {
                $identities = array_diff($identities, $tmp);
                $actions[$action] = $identities;
            }

            $rules = new Rules($actions);
            $this->setRules($rules);
        }
        // // dumpVar($array, 'array');
        return parent::bind($array, $ignore);
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
        return 'com_joaktree.application.'.(int) $this->id;
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
