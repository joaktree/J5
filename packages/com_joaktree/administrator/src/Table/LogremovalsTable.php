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
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeTable;

class LogremovalsTable extends JoaktreeTable implements VersionableTableInterface
{
    public $id 				= null;
    public $app_id				= null;
    public $object_id			= null;
    public $object				= null;
    public $description		= null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.logremovals';
        parent::__construct('#__joaktree_logremovals', 'id', $db);
    }

    public function store($updateNulls = false)
    {
        $params = JoaktreeHelper::getJTParams($this->app_id);
        $indLogging = $params->get('indLogging');

        if ($indLogging) {
            // Logging is switched on
            $ret = parent::store();
        } else {
            // Logging is switched off
            $ret = true;
        }

        return $ret;
    }

    public function check()
    {
        // mandatory fields
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->object_id)) {
            return false;
        }
        if (empty($this->object)) {
            return false;
        }

        if (empty($this->description)) {
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
