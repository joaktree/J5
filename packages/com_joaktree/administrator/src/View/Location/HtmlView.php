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
namespace Joaktree\Component\Joaktree\Administrator\View\Location;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
/**
 * HTML View class for the Joaktree component
 */
class HtmlView extends BaseHtmlView {

	protected $form;
	protected $item;
	protected $state;
	protected $geoCodeSet;
	
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	
		HTMLHelper::stylesheet( JoaktreeHelper::joaktreecss() );
				
		// Initialiase variables.
		$this->form			= $this->get('Form');
		$this->item			= $this->get('Item');		
		$this->state		= $this->get('State');		
		$this->geoCodeSet	= $this->get('GeocodeResultSet');		
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			//JError::raiseError(500, implode("\n", $errors));
			Factory::getApplication()->enqueueMessage(	500, implode("\n", $errors),'warning');	
			return false;
		}

		$this->canDo	= JoaktreeHelper::getActions();
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		ToolbarHelper::title(Text::_('JTLOC_TITLE_EDIT'), 'location');

		// If not checked out, can save the item.
		if ($this->canDo->get('core.edit')) {
			ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('save', 'JTOOLBAR_SAVE');
		}

		if (empty($this->item->id))  {
			ToolbarHelper::cancel('locations.cancel','JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel('locations.cancel', 'JTOOLBAR_CLOSE');
		}
        ToolbarHelper::inlinehelp();
		ToolbarHelper::divider();
		ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');		
	}
}