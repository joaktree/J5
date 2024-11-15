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
 */

namespace Joaktree\Component\Joaktree\Administrator\Table;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class RegistryitemsTable extends Table implements VersionableTableInterface
{
    public $id 		= null;
    public $regkey		= null;
    public $value 		= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.registry_items';
        parent::__construct('#__joaktree_registry_items', 'id', $db);
    }

    public function storeUK()
    {

        if (isset($this->regkey) && isset($this->value)) {
            $query = $this->_db->getQuery(true);
            $query->select(' id ');
            $query->from(' '.$this->_tbl.' ');
            $query->where(' regkey = '.$this->_db->quote($this->regkey).' ');

            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) {
                $this->id = $result;
                $res = $this->store();
            } else {
                $this->id = null;
                $res = $this->store();
            }
        } else {
            $res = false;
        }

        return $res;
    }

    public function loadUK($uk)
    {
        $query = $this->_db->getQuery(true);
        $query->select(' * ');
        $query->from(' '.$this->_tbl.' ');
        $query->where(' regkey = '.$this->_db->quote($uk).' ');

        $this->_db->setQuery($query);
        $tmp = $this->_db->loadObject();

        if (is_object($tmp)) {
            $this->id     = $tmp->id;
            $this->regkey = $tmp->regkey;
            $this->value  = $tmp->value;
        } else {
            $this->id     = null;
            $this->regkey = null;
            $this->value  = null;
        }
    }

    public function deleteUK($uk)
    {
        $query = $this->_db->getQuery(true);
        $query->delete(' '.$this->_tbl.' ');
        $query->where(' regkey = '.$this->_db->quote($uk).' ');

        $this->_db->setQuery($query);
        $tmp = $this->_db->execute(); //$this->_db->query();
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
