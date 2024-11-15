<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_logs.php
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

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class LogsTable extends Table implements VersionableTableInterface
{
    public $id 				= null;
    public $app_id				= null;
    public $object_id			= null;
    public $object				= null;
    public $changeDateTime		= null;
    public $logevent	 		= null;
    public $user_id			= null;

    public function __construct(&$db)
    {
        $this->typeAlias = 'com_joaktree.logs';
        $this->_months = array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC");
        $this->_changeDateTime = new \DateTime('0001-01-01');

        $user = Factory::getApplication()->getIdentity();
        ;
        $this->user_id 	= $user->id;

        parent::__construct('#__joaktree_logs', 'id', $db);
    }

    public function log($crud, $changeDateTimeOverride = null)
    {
        if (!$this->check()) {
            return false;
        }

        $params = JoaktreeHelper::getJTParams($this->app_id);
        $indLogging = $params->get('indLogging');

        if ($indLogging) {
            // Logging is switched on
            switch ($crud) {
                case "C":	$this->logevent = 'JT_C_'.strtoupper($this->object);
                    break;
                case "R":	$this->logevent = 'JT_R_'.strtoupper($this->object);
                    break;
                case "U":	$this->logevent = 'JT_U_'.strtoupper($this->object);
                    break;
                case "D":	$this->logevent = 'JT_D_'.strtoupper($this->object);
                    break;
                case "L":	// Time of gedcom load
                    $this->logevent = 'JT_L_'.strtoupper($this->object);
                    break;
                case "I":	// Date/time is imported from GedCom file
                    $this->logevent = 'JT_I_'.strtoupper($this->object);
                    break;
                default:	$this->logevent = 'JT_A_'.strtoupper($this->object);
                    break;
            }

            if (empty($changeDateTimeOverride)) {
                // If no timestamp value is passed to functon, than current time is used.
                $currentdate = Factory::getDate();
                $this->changeDateTime = $currentdate->Format('Y-m-d H:i:s', false, false);
            } else {
                $this->changeDateTime = $changeDateTimeOverride;
            }

            $ret = $this->store();
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

        return true;
    }

    public function setChangeDateTime($gedcomDate)
    {
        $gedcomDate = trim($gedcomDate);
        $dateTime   = explode(' ', $gedcomDate);

        if (count($dateTime) == 1) {
            unset($dateTime);
            $dateTime = explode('-', $gedcomDate);
        }

        $day   = (int) $dateTime[0];
        $month = strtoupper($dateTime[1]);
        $year  = (int) $dateTime[2];

        $validDate = true;
        if (($day  < 1)    || ($day  > 31)) {
            $validDate = false;
        }
        if (($year < 1000) || ($year > 3000)) {
            $validDate = false;
        }
        if (!in_array($month, $this->_months)) {
            $validDate = false;
        }

        if ($validDate) {
            switch ($month) {
                case "JAN": $monthNumber =  1;
                    break;
                case "FEB": $monthNumber =  2;
                    break;
                case "MAR": $monthNumber =  3;
                    break;
                case "APR": $monthNumber =  4;
                    break;
                case "MAY": $monthNumber =  5;
                    break;
                case "JUN": $monthNumber =  6;
                    break;
                case "JUL": $monthNumber =  7;
                    break;
                case "AUG": $monthNumber =  8;
                    break;
                case "SEP": $monthNumber =  9;
                    break;
                case "OCT": $monthNumber = 10;
                    break;
                case "NOV": $monthNumber = 11;
                    break;
                case "DEC": $monthNumber = 12;
                    break;
                default:	$monthNumber = (int) $month;
                    break;
            }

            $validDate = checkdate($monthNumber, $day, $year);
        }

        if ($validDate) {
            $changeDateTime = new \DateTime(sprintf('%1$04d-%2$02d-%3$02d', $year, $monthNumber, $day));
            if ($changeDateTime > $this->_changeDateTime) {
                $this->_changeDateTime = $changeDateTime;
            }
        }
    }

    public function logChangeDateTime()
    {

        // general part of query
        $query = $this->_db->getQuery(true);
        $query->select(
            '  jlg.id '
                      .', DATE_FORMAT( jlg.changeDateTime, '.$this->_db->quote('%Y-%m-%d').' ) AS changeDateTime '
        );
        $query->from(' #__joaktree_logs  jlg ');
        $query->where(' jlg.app_id      = '.$this->app_id.' ');
        $query->where(' jlg.object_id   = '.$this->_db->quote($this->object_id).' ');
        $query->where(' jlg.object      = '.$this->_db->quote($this->object).' ');
        $query->order(' jlg.changeDateTime DESC ');

        if ($this->_changeDateTime->format('Y-m-d') == '0001-01-01') {
            // situation: no change date information in gedcom
            // finish the query and execute it
            $query->where(' jlg.logevent = '.$this->_db->quote('JT_L_'.strtoupper($this->object)).' ');
            $this->_db->setQuery($query);
            $tmp = $this->_db->loadObject();

            if (is_object($tmp)) {
                // previous record found -> this will be updated to the current date time by leaving it empty
                $this->id = $tmp->id;
            } else {
                // no previous record found -> new record will be inserted
                $this->id = null;
            }

            $crud = 'L';
            $override = null;

        } else {
            // situation: change date information found in gedcom
            // finish the query and execute it
            $query->where(' jlg.logevent = '.$this->_db->quote('JT_I_'.strtoupper($this->object)).' ');
            $this->_db->setQuery($query);
            $tmp = $this->_db->loadObject();

            $crud = 'I';
            if (is_object($tmp)) {
                // previous record found -> we are going to compare
                if ($this->_changeDateTime->format('Y-m-d') > $tmp->changeDateTime) {
                    // new date is larger, we are adding a new record
                    $this->id = null;
                    $override = $this->_changeDateTime->format('Y-m-d');
                } else {
                    // we just update the existing record (leave it unchanged)
                    $this->id = $tmp->id;
                    $override =  $tmp->changeDateTime;
                }

            } else {
                // no previous record found -> new record will be inserted
                $this->id = null;
                $override = $this->_changeDateTime->format('Y-m-d');
            }
        }

        $ret = $this->log($crud, $override);

        if (!$ret) {
            $this->errors[] = $this->_db->getError();
            $this->application->enqueueMessage(Text::_('function logChangeDateTime: '.$this->errors[count($this->errors) - 1]), 'notice');
        } else {
            // prepare for next person log
            $this->_changeDateTime->setDate(0001, 1, 1);
            $this->id = null;
        }

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
