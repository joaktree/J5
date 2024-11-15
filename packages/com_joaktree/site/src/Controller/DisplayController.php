<?php
/**
 * Joomla! component Joaktree
 * file		front end controller.pjp
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
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
        $input = Factory::getApplication()->input;
        if ($input->get('view') == '') {
            $input->set('view', 'joaktreestart');
        }
        parent::display();
    }
}
