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
namespace Joaktree\Component\Joaktree\Administrator\View\Maps;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;	
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
/**
 * HTML View class for the Joaktree component
 */
class HtmlView extends BaseHtmlView {
	function display($tpl = null) {
	
		$app = Factory::getApplication();				
		HTMLHelper::stylesheet( JoaktreeHelper::joaktreecss() );
		$this->canDo	= JoaktreeHelper::getActions();
				
		// Get data from the model
		$this->items		= $this->get( 'Data' );
		$this->pagination	= $this->get( 'Pagination' );
		$this->mapSettings	= $this->get( 'mapSettings' );
		
		//Filter
		$context			= 'com_joaktree.maps.list.';
		
		$filter_order		= $app->getUserStateFromRequest( $context.'filter_order',		'filter_order',		'jmp.name',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $context.'filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$search				= $app->getUserStateFromRequest( $context.'search',			'search',	'',	'string' );
		$search				= strtolower( $search );
		
		// table ordering
		$this->lists['order_Dir'] = $filter_order_Dir;
		$this->lists['order'] = $filter_order;
		
		// search filter
		$this->lists['search']= $search;
				
		//JoaktreeHelper::addSubmenu('maps');		
		$this->addToolbar();
		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar() {
		$canDo	= JoaktreeHelper::getActions();
		
		ToolbarHelper::title( Text::_( 'JTMAPS_TITLE' ), 'map' );

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew();
		}
		
		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList();
		}

		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList('JT_CONFIRMDELETE');
		}
		
		ToolbarHelper::divider();
		//($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
		ToolbarHelper::custom('locations', 'location', 'JT_SUBMENU_LOCATIONS', 'JT_SUBMENU_LOCATIONS', false);
		ToolbarHelper::divider();
		ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');

		
		// Sidebar
		Sidebar::setAction('index.php?option=com_joaktree&view=maps');
	}	

	protected function getSortFields()
	{
		return array(
			'jmp.name' => Text::_('JTAPPS_LABEL_TITLE'),
			'jmp.period_start' => Text::_('JT_LABEL_PERIODSTART'),
			'jmp.period_end' => Text::_('JT_LABEL_PERIODEND')
		);
	}
}
?>