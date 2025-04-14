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
namespace Joaktree\Component\Joaktree\Administrator\View\Themes;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView {
	function display($tpl = null) {
		$app = Factory::getApplication();				

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('jquery');
	
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseScript('jtjs',JoaktreeHelper::jsfile());

		$this->canDo	= JoaktreeHelper::getActions();
				
		// Get data from the model
		$this->items	= $this->get( 'Data' );
		$this->pagination = $this->get( 'Pagination' );
		
		//Filter
		$context		= 'com_joaktree.themes.list.';
		
		$filter_order		= $app->getUserStateFromRequest( $context.'filter_order',		'filter_order',		'jtmp.id',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $context.'filter_order_Dir',	'filter_order_Dir',	'',	'word' );
		$search			= $app->getUserStateFromRequest( $context.'search',			'search',	'',	'string' );
		$search			= strtolower( $search );
		
		// table ordering
		$this->lists['order_Dir'] = $filter_order_Dir;
		$this->lists['order'] = $filter_order;
		
		// search filter
		$this->lists['search']= $search;
				
		//JoaktreeHelper::addSubmenu('themes');		
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
		ToolbarHelper::title( Text::_( 'JTTHEMES_TITLE' ), 'theme' );

		if ($this->canDo->get('core.create')) {
			ToolbarHelper::addNew('add', 'JTOOLBAR_NEW');
		}
		
		if ($this->canDo->get('core.edit')) {
			ToolbarHelper::editList('edit','JTOOLBAR_EDIT');
		}

		if ($this->canDo->get('core.delete')) {
			ToolbarHelper::deleteList('JT_CONFIRMDELETE', 'delete','JTOOLBAR_DELETE');
		}

		if ($this->canDo->get('core.edit.state')) {
			ToolbarHelper::divider();
			ToolbarHelper::makeDefault('setDefault', 'JTTHEME_TOOLBAR_SET_HOME');
		}

		if ($this->canDo->get('core.edit')) {
			ToolbarHelper::divider();
			ToolbarHelper::custom('edit_css', 'editcss', 'editcss', 'JTOOLBAR_EDIT_CSS', true);
		}
		
		ToolbarHelper::divider();
		ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');		
	}
	
	protected function getSortFields()
	{
		return array(
			'jtmp.name' => Text::_('JTTHEMES_HEADING_THEME'),
			'jtmp.home' => Text::_('JTTHEMES_HEADING_DEFAULT'),
			'jtmp.id'   => Text::_('JT_HEADING_ID')
		);
	}
	
}
?>