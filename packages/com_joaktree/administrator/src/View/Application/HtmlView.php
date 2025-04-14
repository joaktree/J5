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

namespace Joaktree\Component\Joaktree\Administrator\View\Application;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());

        // Initialiase variables.
        $this->form		= $this->get('Form');
        $this->item		= $this->get('Item');
        $this->state	= $this->get('State');

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript('jtuploadadmin','media/com_joaktree/js/jtupload_admin.js');

        // use cookies
        $params  			= ComponentHelper::getParams('com_joaktree') ;
        $this->indCookie	= $params->get('indCookies', true);

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

        $isNew		= ($this->item->id == 0);

        // If not checked out, can save the item.
        if ($this->canDo->get('core.edit')) {

            ToolBarHelper::title($isNew ? Text::_('JTAPPS_TITLE_NEW') : Text::_('JTAPPS_TITLE_EDIT'), 'application');
            ToolbarHelper::divider();
            ToolBarHelper::save('application.apply', 'JTOOLBAR_APPLY');
            ToolBarHelper::save('application.save', 'JTOOLBAR_SAVE');
        }
        ToolBarHelper::cancel('application.cancel', 'JTOOLBAR_CANCEL');
        ToolbarHelper::inlinehelp();
        ToolBarHelper::divider();
        ToolBarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
    }
}
