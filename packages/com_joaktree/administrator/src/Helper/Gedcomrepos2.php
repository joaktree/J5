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

namespace Joaktree\Component\Joaktree\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class Gedcomrepos2 extends \StdClass
{
    public function __construct($app_id)
    {
        $this->application = Factory::getApplication();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // initialize table
        $this->repos    = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Repositories');
        $this->logs     = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');

        // set the application id for these tables
        $this->app_id = $app_id;
        $this->repos->set('app_id', $app_id);

        // logs
        $this->logs->set('app_id', $app_id);
        $this->logs->set('object', 'repo');
    }

    /*
    ** Main function to process all repositories from gedcom file.
    */
    public function process(&$repo_id, &$row_lines)
    {
        // start every loop with empty repository record
        $this->repos->set('app_id', $this->app_id);
        $this->repos->set('id', $repo_id);

        // logs
        $this->logs->set('object_id', $repo_id);

        // loop through lines related to the repository
        foreach ($row_lines as $row_line_num => $row_line) {
            switch ($row_line['level']) {
                case "1": switch ($row_line['tag']) {
                    case "NAME":
                        $this->repos->set('name', $row_line['value']);
                        $this->repos->set('website', ''); //new name :  reset website
                        break;
                    case "WWW":
                        $this->repos->set('website', $row_line['value']);
                        break;
                    default:
                        break;
                }
                    break;
                default:  break;
            } // end of level switch
        } // end of loop throuth repository lines

        // store  record
        $ret = $this->repos->store();

        // log update
        if ($ret) {
            $ret = $this->logs->logChangeDateTime();
        }

        // if insert or update went ok, continue with next source; else stop
        if (!$ret) {
            $this->application->enqueueMessage(Text::sprintf('JTGEDCOM_MESSAGE_NOSUCREPO', $repo_id), 'notice') ;
        }

        return $ret;
    }
}
