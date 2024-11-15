<?php
/**
 * Joomla! component Joaktree
 * file		view joaktree person - view.html.php
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

namespace Joaktree\Component\Joaktree\Site\View\Ancestors;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; //replace JViewLegacy
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

/**
 * HTML View class for the Joaktree component
 */
class RawView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $params			= JoaktreeHelper::getJTParams();

        // Access
        $lists['userAccess'] 	= $this->get('access');
        $lists['treeId'] 		= $this->get('treeId');
        $lists['technology'] 	= $this->get('technology');

        // Person + generations
        $personId	 			= array();
        $this->person			= $this->get('person');
        $personId[]		 		= $this->person->id.'|1';
        $lists[ 'ancestorLevel'] = $params->get('ancestorlevel', 1);
        $lists[ 'startGenNum' ]	= 1;
        $lists[ 'endGenNum' ]	= $lists[ 'ancestorLevel'] + 4;
        $lists[ 'app_id' ]		= $this->person->app_id;

        // show dates
        $lists[ 'showDates'] 	= $params->get('ancestordates', 0);

        // last update
        $lists[ 'lastUpdate' ]	= Text::_('JT_LASTUPDATED').': '.JoaktreeHelper::convertDateTime($this->person->lastUpdateDate);

        // copyright
        $lists[ 'CR' ]		= JoaktreeHelper::getJoaktreeCR();

        //$this->assignRef( 'personId', $personId);
        //$this->assignRef( 'lists',	  $lists);
        $this->personId = $personId;
        $this->lists = $lists;

        parent::display($tpl);
    }
}
