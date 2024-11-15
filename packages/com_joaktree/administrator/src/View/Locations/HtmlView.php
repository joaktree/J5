<?php
/**
 * Joomla! component Joaktree
 * file		view joaktree jt_locations - view.html.php
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
namespace Joaktree\Component\Joaktree\Administrator\View\Locations;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\HTML\Helpers\Sidebar; 		// replace JHTMLSidebar
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;		//replace Htmlview
use Joomla\CMS\Toolbar\ToolbarHelper; 	// replace JToolBarHelper

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
/**
 * HTML View class for the Joaktree component
 */
class HtmlView extends BaseHtmlView {		//JViewLegacy {
	function display($tpl = null) {

		$app = Factory::getApplication();				
		HTMLHelper::stylesheet( JoaktreeHelper::joaktreecss() );
		$this->canDo	= JoaktreeHelper::getActions();
				
		// Get data from the model
		$this->items		= $this->get( 'Data' );
		$this->pagination	= $this->get( 'Pagination' );
		$this->mapSettings	= $this->get( 'mapSettings' );
		
		//Filter
		$context		= 'com_joaktree.locations.list.';
		
		$this->lists['order']	= $app->getUserStateFromRequest( $context.'filter_order',		'filter_order',		'jln.value',	'cmd' );
		$this->lists['order_Dir'] = $app->getUserStateFromRequest( $context.'filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$this->lists['server']	= $app->getUserStateFromRequest( $context.'filter_server',	'filter_server',	'',		'word' );
		$this->lists['status']	= $app->getUserStateFromRequest( $context.'filter_status',	'filter_status',	'',		'word' );
		
		// search filter
		$search					= $app->getUserStateFromRequest( $context.'search',			'search',	'',	'string' );
		$search					= strtolower( $search );
		$this->lists['search']	= $search;
				
//		$select_attr = array();
//		$select_attr['class'] = 'inputbox';
//		$select_attr['size'] = '1';
//		$select_attr['onchange'] = 'submitform( );';
		
		// server filter
		$this->server 		= array();
		$selectObj 			= new \StdClass;
		$selectObj->value 	= 'Y';
		$selectObj->text	= Text::_('JT_FILTER_SERVER_YES');
		$this->server[]		= $selectObj;  ; 			
		unset($selectObj);		
		$selectObj 			= new \StdClass;
		$selectObj->value 	= 'N';
		$selectObj->text	= Text::_('JT_FILTER_SERVER_NO');
		$this->server[]		= $selectObj;  ; 			
		unset($selectObj);		
		
		
		// geocoding status filter
		$this->status 		= array();
		$selectObj 			= new \StdClass;
		$selectObj->value 	= 'N';
		$selectObj->text	= Text::_('JT_FILTER_STATUS_NO');
		$this->status[]		= $selectObj;  ; 			
		unset($selectObj);		
		$selectObj 			= new \StdClass;
		$selectObj->value 	= 'Y';
		$selectObj->text	= Text::_('JT_FILTER_STATUS_YES');
		$this->status[]		= $selectObj;  ; 			
		unset($selectObj);		
		
		JoaktreeHelper::addSubmenu('maps');		
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
		
		ToolbarHelper::title( Text::_( 'JTLOCATIONS_TITLE' ), 'location' );

		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList();
			if (!empty($this->mapSettings->geocode)) {
				ToolbarHelper::custom( 'resetlocation', 'resetlocation', 'resetlocation', Text::_( 'JTLOCATIONS_BUTTON_RESET' ), true );
				ToolbarHelper::divider();
				ToolbarHelper::custom( 'geocode', 'geocode', 'geocode', Text::sprintf( 'JTLOCATIONS_BUTTON_GEOCODE', ucfirst($this->mapSettings->geocode) ), false );
			}
		}

		if ($canDo->get('core.delete')) {
			ToolbarHelper::divider();
			//$bar = JToolBar::getInstance('toolbar');
			// explanation: $bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
			//$bar->appendButton('purge', 'location', 'JTFAMTREE_TASK', 'purgeLocation', false);
			ToolbarHelper::custom( 'purgelocations', 'purgelocations', 'purgelocations', Text::_( 'JTLOCATIONS_BUTTON_PURGE' ), false );			
		}
		
		ToolbarHelper::divider();
		ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
				
		// Sidebar
		Sidebar::setAction('index.php?option=com_joaktree&view=locations');

		Sidebar::addFilter(
			Text::_('JT_FILTER_SERVER'),
			'filter_server',
			HTMLHelper::_('select.options', $this->server, 'value', 'text', $this->lists['server'], true)
		);

		Sidebar::addFilter(
			Text::_('JT_FILTER_STATUS_ALL'),
			'filter_status',
			HTMLHelper::_('select.options', $this->status, 'value', 'text', $this->lists['status'], true)
		);
	}	

	protected function getSortFields()
	{
		return array(
			'jln.value' => Text::_('JT_LABEL_LOCATION'),
			'jln.resultValue' => Text::_('JT_LABEL_GEOCODELOCATION'),
			'jln.latitude' => Text::_('JT_LABEL_LATITUDE'),
			'jln.longitude' => Text::_('JT_LABEL_LONGITUDE'),
			'jln.results' => Text::_('JT_LABEL_RESULTS')
		);
	}
}
?>