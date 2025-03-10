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

class AdminpersonsTable extends JoaktreeTable implements VersionableTableInterface
{
    public $app_id      = null; // PK
    public $id          = null; // PK
    public $published   = null;
    public $living      = null;
    public $page        = null;
    public $robots      = null;
    public $map         = null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.admin_persons';
        parent::__construct('#__joaktree_admin_persons', ['id','app_id'], $db);
    }

    public function check($cit = false)
    {
        // mandatory fields
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->id)) {
            return false;
        }

        return true;
    }

    public function person_exists()
    {
        // check whether person exists in admin table
        $query = $this->_db->getQuery(true);

        $query->select(' 1 ');
        $query->from(' '.$this->_tbl.' ');
        $query->where(' app_id = :appid');
        $query->where(' id     = :personid');
        $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

        // execute query and retrieve result
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        // if query has no result, record does not exists yet: function returns FALSE
        // if query has result, record exists yet: function returns TRUE
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function insert()
    {
        $ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys);
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
