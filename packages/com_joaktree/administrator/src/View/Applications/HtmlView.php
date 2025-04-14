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

namespace Joaktree\Component\Joaktree\Administrator\View\Applications;

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\Jtoolbarcustom;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $app = Factory::getApplication();
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseScript('jtjs',JoaktreeHelper::jsfile());

        $this->canDo	= JoaktreeHelper::getActions();

        // Get data from the model
        $items			= $this->get('Data');
        $pagination		= $this->get('Pagination');

        //Filter
        $context		= 'com_joaktree.applications.list.';

        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'japp.id', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');
        $search				= $app->getUserStateFromRequest($context.'search', 'search', '', 'string');
        $search				= strtolower($search);

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        // search filter
        $lists['search'] = $search;

        $this->items = $items;

        $this->items = $items ;
        $this->pagination = $pagination;
        $this->lists = $lists;

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
        ToolBarHelper::title(Text::_('JTAPPS_TITLE'), 'application');

        if ($this->canDo->get('core.create')) {
            ToolBarHelper::addNew('add', 'JTOOLBAR_NEW');
        }

        if ($this->canDo->get('core.edit')) {
            ToolBarHelper::editList('edit', 'JTOOLBAR_EDIT');
        }

        if ($this->canDo->get('core.delete')) {
            ToolBarHelper::deleteList('JT_CONFIRMDELETE', 'delete', 'JTOOLBAR_DELETE');
        }

        if (($this->canDo->get('core.create')) && ($this->canDo->get('core.edit'))) {
            // ToolBarHelper instead of JToolbarHelper
            ToolBarHelper::divider();
            ToolBarHelper::custom('import', 'importgedcom', 'importgedcom', Text::_('JTPERSONS_BUTTON_PROCESSGEDCOM'), true);
            ToolBarHelper::custom('export', 'exportgedcom', 'exportgedcom', Text::_('JTPERSONS_BUTTON_EXPORTGEDCOM'), true);
        }

        if ($this->canDo->get('core.delete')) {
            Jtoolbarcustom::custom('deleteGedCom', 'deletegedcom', Text::_('JTPERSONS_BUTTON_DELETEGEDCOM'), 'JT_CONFIRMDELETE', true);
        }
        ToolbarHelper::inlinehelp();
        ToolBarHelper::divider();
        ToolBarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
    }

    protected function getSortFields()
    {
        return array(
            'japp.title' => Text::_('JTAPPS_HEADING_TITLE'),
            'japp.description' => Text::_('JTAPPS_HEADING_DESCRIPTION'),
            'japp.programName' => Text::_('JTAPPS_HEADING_PROGRAM'),
            'NumberOfPersons' => Text::_('JTAPPS_HEADING_PERSONS'),
        );
    }

}
