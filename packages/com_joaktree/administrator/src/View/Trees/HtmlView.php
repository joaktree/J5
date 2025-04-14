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

namespace Joaktree\Component\Joaktree\Administrator\View\Trees;

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\String\StringHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Button\AssignftButton;

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
        $this->items		= $this->get('Data');
        $this->pagination	= $this->get('Pagination');

        //Filter
        $context		= 'com_joaktree.trees.list.';

        $this->filter['state']		= $app->getUserStateFromRequest($context.'filter_state', 'filter_state', '', 'cmd');
        $this->filter['apptitle']	= $app->getUserStateFromRequest($context.'filter_apptitle', 'filter_apptitle', '', 'int');
        $this->filter['gendex']		= $app->getUserStateFromRequest($context.'filter_gendex', 'filter_gendex', '', 'int');
        $this->filter['order']		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jte.id', 'cmd');
        $this->filter['order_Dir']	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');
        $search						= $app->getUserStateFromRequest($context.'search', 'search', '', 'string');
        //$search						= JString::strtolower( $search );
        $search						= StringHelper::strtolower($search);

        // table ordering
        $this->lists['order_Dir'] 	= $this->filter['order_Dir'];
        $this->lists['order'] 		= $this->filter['order'];

        // search filter
        $this->lists['search'] = $search;

        // state filter
        $this->state = array( 'published' => 1
                            , 'unpublished' => 1
                            , 'archived' => 0
                            , 'trash' => 0
                            , 'all' => 0
                            );

        // application filter
        $this->appTitle 	= JoaktreeHelper::getApplications();

        // gendex filter
        $this->gendex = array();
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 1;
        $selectObj->text	= Text::_('JNO');
        $this->gendex[]	= $selectObj;
        ;
        unset($selectObj);
        $selectObj 			= new \StdClass();
        $selectObj->value 	= 2;
        $selectObj->text	= Text::_('JYES');
        $this->gendex[]	= $selectObj;
        ;
        unset($selectObj);

        $this->lists['jsscript'] 	= $this->getJTscript();
        $this->lists['action']	= $this->get('action');
        if ($this->lists['action'] == 'assign') {
            $this->lists['act_treeId'] = $this->get('treeId');
        }

        JoaktreeHelper::addSubmenu('trees');
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
        ToolBarHelper::title(Text::_('JTFAMTREE_TITLE'), 'familytree');

        if ($this->canDo->get('core.create')) {
            ToolBarHelper::addNew();
            //ToolBarHelper::addNew('add', 'JTOOLBAR_NEW');
        }

        if ($this->canDo->get('core.edit')) {
            ToolBarHelper::editList();
            //ToolBarHelper::editList('edit','JTOOLBAR_EDIT');
        }

        if ($this->canDo->get('core.delete')) {
            ToolBarHelper::deleteList('JT_CONFIRMDELETE');
        }

        if ($this->canDo->get('core.edit')) {
            ToolBarHelper::divider();
            $bar = $this->getDocument()->getToolbar();
            $button = (new AssignftButton())
                    ->text('JTFAMTREE_TASK')
                    ->name('Assignft')
                    ->icon('icon-map-signs')
                    ->listCheck(true);
            $bar->appendButton($button);
        }

        ToolBarHelper::divider();
        ToolBarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');


        // Sidebar
        Sidebar::setAction('index.php?option=com_joaktree&view=trees');

        Sidebar::addFilter(
            Text::_('JT_FILTER_APPLICATION'),
            'filter_apptitle',
            HtmlHelper::_('select.options', $this->appTitle, 'value', 'text', $this->filter['apptitle'], true)
        );

        Sidebar::addFilter(
            Text::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            HtmlHelper::_('select.options', HtmlHelper::_('jgrid.publishedOptions', $this->state), 'value', 'text', $this->filter['state'], true)
        );

        Sidebar::addFilter(
            Text::_('JT_FILTER_GENDEX'),
            'filter_gendex',
            HtmlHelper::_('select.options', $this->gendex, 'value', 'text', $this->filter['gendex'], true)
        );

    }

    private function getJTscript()
    {
        $script  = array();
        $title1  = addslashes(Text::_('JTFAMTREE_TASK'));
        $title2  = addslashes(Text::_('JTPROCESS_MSG'));
        $start   = addslashes(Text::_('JTPROCESS_START'));
        $current = addslashes(Text::_('JTPROCESS_CURRENT'));
        $end     = addslashes(Text::_('JTPROCESS_END'));
        $button  = addslashes(Text::_('JTPROCESS_DONE'));

        $script[] = "function assignftInit(trid) { ";
        $script[] = "  var form = document.adminForm; ";
        $script[] = "if (form.boxchecked.value!=1 && !trid) {return;}";
        $script[] = "  var treeid = ''; ";

        $script[] = "  if (trid) { ";
        $script[] = "    treeid = trid + '!'; ";
        $script[] = "  } else { ";
        $script[] = "    for (var i = 0; true; i++) { ";
        $script[] = "      var cbx = form['cb'+i]; ";
        $script[] = "      if (!cbx) break; ";
        $script[] = "      if (cbx.checked == true) { ";
        $script[] = "        treeid = treeid + cbx.value + '!'; ";
        $script[] = "      } ";
        $script[] = "    } ";
        $script[] = "  } ";
        $script[] = "   ";
        $script[] = "   ";

        $script[] = "  var container = document.getElementById('system-message-container'); ";
        $script[] = "  if (document.getElementById('ftlft')) container.removeChild(document.getElementById('ftlft'))";
        $script[] = "  if (document.getElementById('ftrht')) container.removeChild(document.getElementById('ftrht'))";
        $script[] = "  if (document.getElementById('ftend')) container.removeChild(document.getElementById('ftend'))";
        $script[] = "  var lft = document.createElement('div'); lft.id='ftlft';lft.classList ='w-40';lft.style.float='left';";
        $script[] = "  var fldlft = document.createElement('fieldset');fldlft.classList ='adminform'; ";
        $script[] = "  var leglft = document.createElement('legend');leglft.innerText = '$title1'; ";
        $script[] = "  var ullft  = document.createElement('ul'); ullft.classList = 'adminformlist'; ";
        $script[] = "  var lista  = document.createElement('li'); ";
        $script[] = "  var licur  = document.createElement('li'); ";
        $script[] = "  var liend  = document.createElement('li'); ";
        $script[] = "  var labst  = document.createElement('label');labst.innerText = '$start'; labst.style.width='50%';";
        $script[] = "  var labcur = document.createElement('label');labcur.innerText ='$current';labcur.style.width='50%'; ";
        $script[] = "  var labend = document.createElement('label');labend.innerText = '$end'; labend.style.width='50%';";
        $script[] = "  var inpst  = document.createElement('input'); inpst.id = 'start';inpst.type = 'text';inpst.classList ='readonly'; ";
        $script[] = "  var inpcur = document.createElement('input'); inpcur.id = 'current';inpcur.type = 'text';inpcur.classList ='readonly'; ";
        $script[] = "  var inpend = document.createElement('input');inpend.id = 'end';inpend.type = 'text';inpend.classList = 'readonly'; ";
        $script[] = "  container.appendChild(lft); ";
        $script[] = "  lft.appendChild(fldlft); ";
        $script[] = "  fldlft.appendChild(leglft); ";
        $script[] = "  fldlft.appendChild(ullft); ";
        $script[] = "  ullft.appendChild(lista); ";
        $script[] = "  lista.appendChild(labst); ";
        $script[] = "  lista.appendChild(inpst); ";
        $script[] = "  ullft.appendChild(licur); ";
        $script[] = "  licur.appendChild(labcur); ";
        $script[] = "  licur.appendChild(inpcur); ";
        $script[] = "  ullft.appendChild(liend); ";
        $script[] = "  liend.appendChild(labend); ";
        $script[] = "  liend.appendChild(inpend); ";

        $script[] = "  var rht = document.createElement('div');rht.id='ftrht'; rht.classList = 'width-50'; ";
        $script[] = "  var fldrht = document.createElement('fieldset');fldrht.classList ='adminform';fldrht.style.minHeight = '92px'; ";
        $script[] = "  var legrht = document.createElement('legend');legrht.innerText = '$title2'; ";
        $script[] = "  var divrht = document.createElement('div');divrht.id ='procmsg'; ";
        $script[] = "  var butrht = document.createElement('button');butrht.id = 'butprocmsg';butrht.innerText= '$button';butrht.style.display='none';butrht.onclick= function(){Joomla.submitform();};butrht.style.marginLeft= '10px';butrht.style.marginBottom= '10px'; ";
        $script[] = "  var end = document.createElement('div');end.id='ftend'; end.style.clear='both';";

        $script[] = "  container.appendChild(rht); ";
        $script[] = "  rht.appendChild(fldrht); ";
        $script[] = "  fldrht.appendChild(legrht); ";
        $script[] = "  fldrht.appendChild(divrht); ";
        $script[] = "  rht.appendChild(butrht); ";

        $script[] = "  container.appendChild(end); ";

        $script[] = "  var url = 'index.php?option=com_joaktree&view=trees&format=raw&tmpl=component&init=1&treeId=' + treeid; ";
        $script[] = "  assignft(url); ";
        $script[] = "} ";
        $script[] = " ";

        return implode("\n", $script);
    }

    protected function getSortFields()
    {
        return array(
            'jte.name' => Text::_('JTFAMTREE_HEADING_TREE'),
            'japp.title' => Text::_('JTFAMTREE_HEADING_APPTITLE'),
            'jte.indGendex' => Text::_('JTFAMTREE_HEADING_GENDEX'),
            'access_level' => Text::_('JT_HEADING_ACCESS'),
            'theme' => Text::_('JT_HEADING_THEME')
        );
    }
}
