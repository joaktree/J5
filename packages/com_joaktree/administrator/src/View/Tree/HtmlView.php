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

namespace Joaktree\Component\Joaktree\Administrator\View\Tree;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;
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
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseScript('jtjs',JoaktreeHelper::jsfile());

        // Initialise variables.
        $this->form		= $this->get('Form');
        $this->item		= $this->get('Item');
        $this->state	= $this->get('State');

        // use cookies
        $params  			= ComponentHelper::getParams('com_joaktree') ;
        $this->indCookie	= $params->get('indCookies', true);

        $this->canDo	= JoaktreeHelper::getActions();
        $this->addToolbar();
        parent::display($tpl);
    }


    protected function addToolbar()
    {
        //Factory::getApplication()->input->set('hidemainmenu', true);

        $isNew		= (!is_object($this->item));

        ToolbarHelper::title($isNew ? Text::_('JTTREE_TITLE_NEW') : Text::_('JTTREE_TITLE_EDIT'), 'familytree');

        // If not checked out, can save the item.
        if ($this->canDo->get('core.edit')) {
            ToolbarHelper::title($isNew ? Text::_('JTTREE_TITLE_NEW') : Text::_('JTTREE_TITLE_EDIT'), 'familytree');
            ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
            ToolbarHelper::save('save', 'JTOOLBAR_SAVE');
            ToolbarHelper::divider();
            if (empty($this->item->id))  {
                ToolbarHelper::cancel('cancel','JTOOLBAR_CANCEL');
            } else {
                ToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
            }
            ToolbarHelper::custom('saveassign', 'map-signs', 'saveassign', 'JTSAVE_FAMTREE_TASK', false);
            ToolbarHelper::divider();
            
            ToolbarHelper::inlinehelp();
            ToolbarHelper::divider();
            ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
        }

    }
}
