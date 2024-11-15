<?php
/**
 * Joomla! component Joaktree
 * file		jt_gedcomnotes model - jt_gedcomnotes.php
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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class Gedcomnotes2 extends \StdClass
{
    public function __construct($app_id)
    {
        $this->application = Factory::getApplication();

        // initialize table
        $this->notes = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Notes');

        // set the application id for these tables
        $this->app_id = $app_id;
        $this->notes->set('app_id', $app_id);
    }

    /*
    ** Main function to process all notes from gedcom file.
    */
    public function process(&$note_id, &$row_lines)
    {

        // start every loop with "empty" note record (app_id is not removed)
        $this->notes->set('app_id', $this->app_id);
        $this->notes->set('id', $note_id);

        // loop through lines related to the note
        foreach ($row_lines as $row_line_num => $row_line) {
            switch ($row_line['level']) {
                case "1": switch ($row_line['tag']) {
                    case "TEXT": 	// default action
                    case "CONC":	// default action
                    case "CONT":	// default action
                        $this->notes->set('value', $row_line['value']);
                        break;
                    default:		// no action
                        break;
                }
                    break;
                default: break;
            } // end of level switch
        } // end of loop throuth note lines

        // store record
        $ret = $this->notes->store();

        // if insert or update went ok, continue with next note; else stop
        if (!$ret) {
            $this->application->enqueueMessage(Text::sprintf('JTGEDCOM_MESSAGE_NOSUCNOTE', $note_id), 'notice') ;
        }

        return $ret;
    }
}
