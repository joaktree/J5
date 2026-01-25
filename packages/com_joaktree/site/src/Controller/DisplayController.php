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

namespace Joaktree\Component\Joaktree\Site\Controller;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Joaktree Component Controller
 */
class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = array())
    {
        // Make sure we have a default view
        // create an input object
        $input = Factory::getApplication()->getInput();
        if ($input->get('view') == '') {
            $input->set('view', 'joaktreestart');
        }
        if (($input->get('view') == 'interactivetree') && ($input->get('what') != null) && ($input->get('what') == 'full')) {
            // cache only interactive tree full information
            $urlparams = [];
            $urlparams = ['personId' => $input->get('personId'),
                          'lang' => $input->get('lang')];
            parent::display(true, $urlparams); // use cache
        } else { // no cache
            parent::display();
        }
    }
}
