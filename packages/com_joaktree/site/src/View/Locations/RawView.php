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

namespace Joaktree\Component\Joaktree\Site\View\Locations;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

/**
 * HTML View class for the Joaktree component
 */
class RawView extends HtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $this->lists 		= array();

        // Load the parameters.
        $this->params 		= JoaktreeHelper::getJTParams();

        $services = json_decode($this->params->get('services'));
        $format = "raw";
        if ($services->interactivemap == "Openstreetmap") {
            $format = "html";
        }
        // Get data from the model
        $this->treeinfo		= $this->get('treeinfo');
        $menus  			= $this->get('menus');

        // Id's and settings
        $this->lists['tree_id']		= $this->get('treeId');
        $this->lists['userAccess'] 	= $this->get('access');
        $this->lists['menuItemId'] 	= $menus[ $this->lists['tree_id'] ];
        $this->lists['interactiveMap'] 	= $this->get('interactiveMap');

        //location list
        $this->lists['columns']		= (int) $this->params->get('columnsLoc', '3');
        $this->locationlist  		= $this->get('locationlist');
        $this->lists['numberRows']	= (int) ceil(count($this->locationlist) /  $this->lists['columns']);

        $this->lists['linkMap'] 	= 'index.php?option=com_joaktree'
                                        .'&view=interactivemap'
                                        .'&tmpl=component'
                                        .'&format='.$format
                                        .'&treeId='.$this->lists['tree_id'];
        $this->lists['linkList'] 	=  'index.php?option=com_joaktree'
                                        .'&view=list'
                                        .'&tmpl=component'
                                        .'&layout=location'
                                        .'&treeId='.$this->lists['tree_id'];
        parent::display($tpl);
    }
}
