<?php
/**
 * Joomla! component Joaktree
 * file		jt_gedcomsources model - jt_gedcomsources.php
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

namespace Joaktree\Component\Joaktree\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class Gedcomsources2 extends \StdClass
{
    public function __construct($app_id)
    {
        $this->application = Factory::getApplication();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // initialize table
        $this->sources = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Sources');
        $this->logs    = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');

        // set the application id for these tables
        $this->app_id = $app_id;
        $this->sources->set('app_id', $app_id);

        // logs
        $this->logs->set('app_id', $app_id);
        $this->logs->set('object', 'sour');
    }


    /*
    ** Main function to process all sources from gedcom file.
    */
    public function process(&$source_id, &$row_lines)
    {
        static $teller;
        $teller++;

        // start every loop with empty source record
        $this->sources->set('app_id', $this->app_id);
        $this->sources->set('id', $source_id);

        // logs
        $this->logs->set('object_id', $source_id);

        // loop through lines related to the source
        foreach ($row_lines as $row_line_num => $row_line) {
            switch ($row_line['level']) {
                case "1": switch ($row_line['tag']) {
                    case "AUTH":
                        $this->sources->set('author', $row_line['value']);
                        break;
                    case "TITL":
                        $this->sources->set('title', $row_line['value']);
                        break;
                    case "PUBL":
                        $this->sources->set('publication', $row_line['value']);
                        break;
                    case "TEXT":
                        $this->sources->set('information', $row_line['value']);
                        break;
                    case "REPO":
                        if (!is_null($row_line['value'])) {
                            $this->sources->set('repo_id', rtrim(ltrim($row_line['value'], '@'), '@'));
                        }
                        break;
                    default:
                        break;
                }
                    break;
                default: break;
            } // end of level switch
        } // end of loop throuth source lines

        // store record
        $ret = $this->sources->store();

        // log update
        if ($ret) {
            $ret = $this->logs->logChangeDateTime();
        }

        // if insert or update went ok, continue with next source; else stop
        if (!$ret) {
            $this->application->enqueueMessage(Text::sprintf('JTGEDCOM_MESSAGE_NOSUCSOURCE', $source_id), 'notice') ;
        }

        return $ret;
    }
}
