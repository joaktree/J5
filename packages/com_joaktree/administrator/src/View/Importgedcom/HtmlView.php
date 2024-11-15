<?php
/**
 * Joomla! component Joaktree
 * file		administrator jt_import_gedcom view - view.html.php
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
namespace Joaktree\Component\Joaktree\Administrator\View\Importgedcom;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Toolbar\ToolbarHelper; 	// replace JToolBarHelper

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView {

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	
		HTMLHelper::stylesheet( JoaktreeHelper::joaktreecss() );
		HTMLHelper::script( JoaktreeHelper::jsfile() );		
		$this->addToolbar();
		
		$items			= $this->get( 'Data' );
		
		//$this->assignRef( 'items',  $items );
		$this->items=$items;
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		//Factory::getApplication()->input->set('hidemainmenu', true);
		
		ToolbarHelper::title(Text::_('JTIMPORTGEDCOM_TITLE'), 'application');

	}
}
