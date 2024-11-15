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
namespace Joaktree\Component\Joaktree\Administrator\View\Persons;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $app = Factory::getApplication();
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::script(JoaktreeHelper::jsfile());

        // add script
        $document 		= Factory::getApplication()->getDocument();
        $document->addScriptDeclaration($this->addScript());

        // Get data from the model
        $this->items   		= $this->get('Persons');
        $trees	     		= $this->get('Trees');
        $this->pagination  	= $this->get('Pagination');
        $this->lists['patronym'] 	= $this->get('patronymShowing');
        $this->columns		= $this->get('columnSettings');

        //Filter
        $context			= 'com_joaktree.persons.list.';

        $this->filter['state']		= $app->getUserStateFromRequest($context.'filter_state', 'filter_state', '', 'cmd');
        $this->filter['living']		= $app->getUserStateFromRequest($context.'filter_living', 'filter_living', '', 'word');
        $this->filter['page']		= $app->getUserStateFromRequest($context.'filter_page', 'filter_page', '', 'word');
        $this->filter['map']		= $app->getUserStateFromRequest($context.'filter_map', 'filter_map', '', 'int');
        $this->filter['tree']		= $app->getUserStateFromRequest($context.'filter_tree', 'filter_tree', '', 'int');
        $this->filter['apptitle']	= $app->getUserStateFromRequest($context.'filter_apptitle', 'filter_apptitle', '', 'int');
        $this->filter['robots']		= $app->getUserStateFromRequest($context.'filter_robots', 'filter_robots', '', 'int');
        $this->filter['order']		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jpn.id', 'cmd');
        $this->filter['order_Dir']	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');
        $search1					= $app->getUserStateFromRequest($context.'search1', 'search1', '', 'string');
        $search1					= strtolower($search1);
        $search2					= $app->getUserStateFromRequest($context.'search2', 'search2', '', 'string');
        $search2					= strtolower($search2);
        $search3					= $app->getUserStateFromRequest($context.'search3', 'search3', '', 'string');
        $search3					= strtolower($search3);

        // table ordering
        $this->lists['order_Dir'] 	= $this->filter['order_Dir'] ;
        $this->lists['order'] 		= $this->filter['order'];

        // search filter
        $this->lists['search1'] = $search1;
        $this->lists['search2'] = $search2;
        $this->lists['search3'] = $search3;

        // application filter
        $this->appTitle 	= JoaktreeHelper::getApplications();

        // default family tree filter
        $this->tree = array();
        for ($i = 1; $i <= count($trees); $i++) {
            $selectObj 			= new \StdClass();
            $selectObj->value 	= $trees[$i - 1]->id;
            $selectObj->text	= $trees[$i - 1]->name;
            $this->tree[]	= $selectObj;
            ;
            unset($selectObj);
        }

        // state filter
        $this->state = array( 'published' => 1
                            , 'unpublished' => 1
                            , 'archived' => 0
                            , 'trash' => 0
                            , 'all' => 0
                            );

        // living filter
        $this->living = array();
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 'L';
        $selectObj->text	= Text::_('JT_FILTER_VAL_LIVING');
        $this->living[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 'D';
        $selectObj->text	= Text::_('JT_FILTER_VAL_NOTLIVING');
        $this->living[]	= $selectObj;
        ;
        unset($selectObj);

        // page filter
        $this->page = array();
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 'Y';
        $selectObj->text	= Text::_('JT_FILTER_VAL_PAGE');
        $this->page[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 'N';
        $selectObj->text	= Text::_('JT_FILTER_VAL_NOPAGE');
        $this->page[]	= $selectObj;
        ;
        unset($selectObj);

        // map filter
        $this->map = array();
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 2;
        $selectObj->text	= Text::_('JT_FILTER_VAL_STATMAP');
        $this->map[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 3;
        $selectObj->text	= Text::_('JT_FILTER_VAL_DYNMAP');
        $this->map[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 1;
        $selectObj->text	= Text::_('JT_FILTER_VAL_NOMAP');
        $this->map[]	= $selectObj;
        ;
        unset($selectObj);

        // robots filter
        $this->robots = array();
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 1;
        $selectObj->text	= Text::_('JT_ROBOT_USE_TREE');
        $this->robots[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 2;
        $selectObj->text	= 'index, follow';
        $this->robots[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 3;
        $selectObj->text	= 'noindex, follow';
        $this->robots[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 4;
        $selectObj->text	= 'index, nofollow';
        $this->robots[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 5;
        $selectObj->text	= 'noindex, nofollow';
        $this->robots[]	= $selectObj;
        ;
        unset($selectObj);
        // end of filters

        //JoaktreeHelper::addSubmenu('persons');
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

        ToolbarHelper::title(Text::_('JTPERSONS_TITLE'), 'person');

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::custom('publishAll', 'publish', 'publish', Text::_('JTPERSONS_BUTTON_PUBLISHALL'), true);
            ToolbarHelper::custom('unpublishAll', 'unpublish', 'unpublish', Text::_('JTPERSONS_BUTTON_UNPUBLISHALL'), true);
            ToolbarHelper::divider();
            ToolbarHelper::custom('livingAll', 'living', 'living', Text::_('JTPERSONS_BUTTON_LIVINGALL'), true);
            ToolbarHelper::custom('notLivingAll', 'notliving', 'notliving', Text::_('JTPERSONS_BUTTON_NOTLIVINGALL'), true);
            ToolbarHelper::divider();
            ToolbarHelper::custom('pageAll', 'page', 'page', Text::_('JTPERSONS_BUTTON_PAGEALL'), true);
            ToolbarHelper::custom('noPageAll', 'nopage', 'nopage', Text::_('JTPERSONS_BUTTON_NOPAGEALL'), true);
            ToolbarHelper::divider();
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::custom('mapStatAll', 'statmap', 'statmap', Text::_('JTPERSONS_BUTTON_STATMAPALL'), true);
            ToolbarHelper::custom('mapDynAll', 'dynmap', 'dynmap', Text::_('JTPERSONS_BUTTON_DYNMAPALL'), true);
            ToolbarHelper::custom('noMapAll', 'nomap', 'nomap', Text::_('JTPERSONS_BUTTON_NOMAPALL'), true);
            ToolbarHelper::divider();
        }

        ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');

        // Sidebar
        Sidebar::setAction('index.php?option=com_joaktree&view=persons');

        Sidebar::addFilter(
            Text::_('JT_FILTER_APPLICATION'),
            'filter_apptitle',
            HtmlHelper::_('select.options', $this->appTitle, 'value', 'text', $this->filter['apptitle'], true)
        );

        Sidebar::addFilter(
            Text::_('JT_FILTER_TREE'),
            'filter_tree',
            HtmlHelper::_('select.options', $this->tree, 'value', 'text', $this->filter['tree'], true)
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            HtmlHelper::_('select.options', HtmlHelper::_('jgrid.publishedOptions', $this->state), 'value', 'text', $this->filter['state'], true)
        );

        Sidebar::addFilter(
            Text::_('JT_FILTER_LIVING'),
            'filter_living',
            HtmlHelper::_('select.options', $this->living, 'value', 'text', $this->filter['living'], true)
        );

        Sidebar::addFilter(
            Text::_('JT_FILTER_PAGE'),
            'filter_page',
            HtmlHelper::_('select.options', $this->page, 'value', 'text', $this->filter['page'], true)
        );

        Sidebar::addFilter(
            Text::_('JT_FILTER_MAP'),
            'filter_map',
            HtmlHelper::_('select.options', $this->map, 'value', 'text', $this->filter['map'], true)
        );

        Sidebar::addFilter(
            Text::_('JT_FILTER_ROBOTS'),
            'filter_robots',
            HtmlHelper::_('select.options', $this->robots, 'value', 'text', $this->filter['robots'], true)
        );
    }

    protected function addScript()
    {
        $script = array();
        $params  	= ComponentHelper::getParams('com_joaktree') ;
        $indCookie	= $params->get('indCookies', true);

        $script[] = "function jt_toggle(tag,col) { ";
        $script[] = "  var oEl, i, elements, cEl, num; ";
        $script[] = ($indCookie) ? "  var myCookie; " : " ";
        $script[] = "  elements = document.getElementById('editcell').getElements(tag); ";
        $script[] = "  cEl =  document.getElementById('footer'); ";
        $script[] = "  num = (cEl.getProperty('colspan')).toInt(); ";
        $script[] = "  for (i=0; i < elements.length; i++ ) { ";
        $script[] = "    if($(elements[i])){ ";
        $script[] = "      oEl = $(elements[i]); ";
        $script[] = "      if (oEl.hasClass('jt-hide-'+col)) { ";
        $script[] = "        oEl.classList.remove('jt-hide-'+col); ";
        $script[] = "        oEl.classList.add('jt-show-'+col); ";
        $script[] = "        num = num + 1; ";
        $script[] = ($indCookie) ? "        myCookie = Cookie.write('jt_'+col, '1', {duration: 0}); " : " ";
        $script[] = "      } else if (oEl.hasClass('jt-show-'+col)) { ";
        $script[] = "        oEl.classList.remove('jt-show-'+col); ";
        $script[] = "        oEl.classList.add('jt-hide-'+col); ";
        $script[] = "        num = num - 1; ";
        $script[] = ($indCookie) ? "        myCookie = Cookie.read('jt_'+col); " : " ";
        $script[] = ($indCookie) ? "        if (myCookie == '1') { Cookie.dispose('jt_'+col); } " : " ";
        $script[] = "      } ";
        $script[] = "    } ";
        $script[] = "  } ";
        $script[] = "  return false; ";
        $script[] = "} ";
        $script[] = "";

        return implode("\n", $script);
    }

    protected function getSortFields()
    {
        $fields = array(
            'jpn.id' => Text::_('JT_HEADING_ID'),
            'jpn.firstName' => Text::_('JTPERSONS_HEADING_FIRSTNAME')
        );

        if ($this->lists['patronym']) {
            $fields['jpn.patronym'] = Text::_('JTPERSONS_HEADING_PATRONYM');
        }

        $fields['jpn.familyName'] 	= Text::_('JTPERSONS_HEADING_FAMNAME');
        $fields['13'] 				= Text::_('JTPERSONS_HEADING_PERIOD');

        return $fields;
    }
}
