<?php
/**
 * Joomla! component Joaktree
 * file		view maps - view.html.php
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

namespace Joaktree\Component\Joaktree\Site\View\Map;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView; //replace JViewLegacy

/**
 * HTML View class for the Joaktree component
 */
class RawView extends HtmlView
{
    public function display($tpl = null)
    {
        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $this->lists	= array();
        $this->map 		= $this->get('map');

        if ($this->map->params['service'] == 'staticmap') {
            // Get data from the model
            $this->mapview 				= $this->get('mapView');
            $this->lists['userAccess']	= ($this->mapview) ? true : false;
        }

        parent::display($tpl);
    }
}
