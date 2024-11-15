<?php
/**
 * Joomla! component Joaktree
 * file		administrator default view - view.html.php
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
namespace Joaktree\Component\Joaktree\Administrator\View\Default;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\HTML\HTMLHelper;		//replace JHtml
use Joomla\CMS\Toolbar\ToolbarHelper; 	// replace JToolBarHelper
use Joomla\CMS\HTML\Helpers\Sidebar; 		// replace JHTMLSidebar
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; //replace JViewLegacy

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;

class HtmlView extends BaseHtmlView {	//JViewLegacy {
	function display($tpl = null) {
		$this->lists = array();
		
		HTMLHelper::stylesheet( JoaktreeHelper::joaktreecss() );
		
		//load the language file
		MBJService::setLanguage();
		
		$this->lists['version']   = JoaktreeHelper::getJoaktreeVersion();
		$this->lists['providers'] = $this->get('providers');

		//JoaktreeHelper::addSubmenu('default');		
		$this->addToolbar();
		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo	= JoaktreeHelper::getActions();
		
		ToolbarHelper::title( Text::_( 'COM_JOAKTREE_CONTROL_PANEL' ), 'joaktree' );
		
		if ($canDo->get('core.admin')) {
			ToolbarHelper::preferences('com_joaktree', '460');
		}
        ToolbarHelper::inlinehelp();
		ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');		
	}
	
}
?>