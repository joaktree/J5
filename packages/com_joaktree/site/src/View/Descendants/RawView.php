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

namespace Joaktree\Component\Joaktree\Site\View\Descendants;

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

        $params					= JoaktreeHelper::getJTParams();

        // Access
        $lists['userAccess'] 	= $this->get('access');
        $lists['treeId'] 		= $this->get('treeId');
        $lists['technology'] 	= $this->get('technology');

        // Person + generations
        $personId	 			= array();
        $this->person			= $this->get('person');

        $personId[]		 		= $this->person->id.'|1';

        $lists[ 'startGenNum' ]	= 1;
        $lists[ 'endGenNum' ]	= (int) $params->get('descendantlevel', 20);
        $lists[ 'app_id' ]		= $this->person->app_id;

        $this->personId = $personId;
        $this->lists = $lists;
        parent::display($tpl);
    }


}
