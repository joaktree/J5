<?php
/**
 * Joomla! component Joaktree
 * file		administrator jt_theme view - view.html.php
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
namespace Joaktree\Component\Joaktree\Administrator\View\Theme;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;		//replace Htmlview
use Joomla\CMS\Toolbar\ToolbarHelper; 	// replace JToolBarHelper

use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView {

	protected $form;
	protected $item;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	
		HTMLHelper::stylesheet( JoaktreeHelper::joaktreecss() );
				
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');		
		$this->state	= $this->get('State');		

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			// JError::raiseError(500, implode("\n", $errors));
			Factory::getApplication()->enqueueMessage('500 '. implode("\n", $errors),'error');
			return false;
		}

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
		$isNew		= ($this->item->id == 0);
		$canDo		= JoaktreeHelper::getActions();

		ToolbarHelper::title($isNew ? Text::_('JTTHEME_TITLE_NEW') : Text::_('JTTHEME_TITLE_EDIT'), 'theme');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit')) {
			ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('save', 'JTOOLBAR_SAVE');
		}

		if (empty($this->item->id))  {
			ToolbarHelper::cancel('themes.cancel','JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel('themes.cancel', 'JTOOLBAR_CLOSE');
		}
        ToolbarHelper::inlinehelp();
		ToolbarHelper::divider();
		ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');		
	}
}
