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

namespace Joaktree\Component\Joaktree\Administrator\View\Settings;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseScript('jtjs',JoaktreeHelper::jsfile());

        // what is the layout
        $this->layout = Factory::getApplication()->getInput()->get('layout');
        // Get data from the model
        if ($this->layout == 'personevent') {
            $this->items		= $this->get('DataPersEvent');
            $this->pagination	= $this->get('personPagination');
        } elseif ($this->layout == 'personname') {
            $this->items		= $this->get('DataPersName');
            $this->pagination	= $this->get('namePagination');
        } elseif ($this->layout == 'relationevent') {
            $this->items		= $this->get('DataRelaEvent');
            $this->pagination	= $this->get('relationPagination');
        } else {
            $this->items		= $this->get('DataPersEvent');
            $this->pagination	= $this->get('personPagination');
        }
        //Filter
        $context		= 'com_joaktree.settings.list.';

        //JoaktreeHelper::addSubmenu($this->layout);
        $this->addToolbar($this->layout);
        $this->sidebar = Sidebar::render();
        $this->html = $this->getHtml();
        parent::display($tpl);
    }



    /**

     * Add the page title and toolbar.

     *

     * @since	1.6

     */

    protected function addToolbar($layout)
    {
        $canDo	= JoaktreeHelper::getActions();

        // Get data from the model
        if ($this->layout == 'personevent') {
            ToolbarHelper::title(Text::_('JTSETTINGS_TITLE_PERSONEVENTS'), 'display1');
        } elseif ($this->layout == 'personname') {
            ToolbarHelper::title(Text::_('JTSETTINGS_TITLE_NAMES'), 'display2');
        } elseif ($this->layout == 'relationevent') {
            ToolbarHelper::title(Text::_('JTSETTINGS_TITLE_RELATIONEVENTS'), 'display3');
        } else {
            ToolbarHelper::title(Text::_('JTSETTINGS_TITLE_PERSONEVENTS'), 'display1');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::save('save', Text::_('JTSETTINGS_HEADING_SAVE'), 'title');
        }

        if ($layout == 'personevent') {
            ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
        } elseif ($layout == 'personname') {
            ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
        } elseif ($layout == 'relationevent') {
            ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
        } else {
            ToolbarHelper::help('JoaktreeManuel', true, 'https://www.joomxtensions.com/download/');
        }

        // Sidebar
        Sidebar::setAction('index.php?option=com_joaktree&view=settings&layout='.$layout);
    }

    private function getHtml()
    {
        $html 	= array();
        $canDo	= JoaktreeHelper::getActions();
        $html[] = '<form action="'.Route::_('index.php?option=com_joaktree').'" method="post" id="adminForm" name="adminForm" >' ;
        $saveOrderingUrl = 'index.php?option=com_joaktree&task=joaktree.saveOrderAjax&tmpl=component';
        $html[] = HTMLHelper::_('sortablelist.sortable', 'articleList', 'adminForm', 'asc', $saveOrderingUrl);
        if (!empty($this->sidebar)) {
            $html[] = '<div id="j-sidebar-container" class="span2">';
            $html[] = $this->sidebar;
            $html[] = '</div>';
            $divClassSpan = 'span10';
        } else {
            $divClassSpan = '';
        }
        $html[] = '<div id="j-main-container" class="'.$divClassSpan.'">';
        $html[] = '<!-- No filter row -->';
        $html[] = '<div class="clearfix"> </div>';
        $html[] = '<!--  table -->';
        $html[] = '<table class="table table-striped" id="articleList">';
        $html[] = '  <thead>';
        $html[] = '    <tr>';
        $html[] = '      <th width="1%" class="nowrap center hidden-phone">'.Text::_('JT_HEADING_NUMBER').'</th>';
        $html[] = '      <th width="1%" class="hidden-phone">';
        $html[] = '        <input type="checkbox" name="checkall-toggle" value="" title="'.Text::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />';
        $html[] = '      </th>';
        $html[] = '      <th class="nowrap hidden-phone">';
        $html[] = HtmlHelper::_('image', 'admin/icon-16-notice-note.png', null, 'title="'.Text::_('JTSETTINGS_HEADING_EXPLANATION').'"', true);
        $html[] = '      </th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JTSETTINGS_HEADING_FIELD').'</th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JTSETTINGS_HEADING_ORDER').'</th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JT_HEADING_PUBLISHED').'</th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JTSETTINGS_HEADING_SAVE').'</th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JT_HEADING_ACCESS').'</th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JTSETTINGS_HEADING_ACCESS_LIVPERSON').'</th>';
        $html[] = '      <th class="nowrap hidden-phone">'.Text::_('JTSETTINGS_HEADING_ALT_LIVPERSON').'</th>';
        $html[] = '    </tr>';
        $html[] = '  </thead>';
        $html[] = '  <tbody>';
        $html[] = '  </tbody>';

        foreach ($this->items as $i => $row) {
            //$published 	= HTMLHelper::_('grid.published', $row, $i );
            $options = [
                       'task_prefix' => 'settings.',
                       'disabled' => false,
                       'id' => 'state-' . $row->id,
                        ];
            $published = (new PublishedButton())->render((int) $row->published, $i, $options);

            $gedcomLabel  = '<input type="hidden" id="jtid'.$row->id.'" name="jtid['.$row->id.']" value="'.$row->id.'" />';
            $gedcomLabel .= '<input type="hidden" id="code'.$row->id.'" name="code['.$row->id.']" value="'.Text::_($row->code).'" />';
            $gedcomLabel .= Text::_($row->code);
            $access		 = '<select id="access'.$row->id.'" name="access'.$row->id.'" class="inputbox" onchange="javascript:changeAccessLevel(\'cb'.$i.'\')">';
            $access		.= HTMLHelper::_('select.options', HtmlHelper::_('access.assetgroups'), 'value', 'text', $row->access);
            $access		.= '</select>';
            $accessLiving = '<select id="accessLiving'.$row->id.'" name="accessLiving'.$row->id.'" class="inputbox" onchange="javascript:changeAccessLevel(\'cb'.$i.'\')">';
            $accessLiving .= '<option  value="">'.Text::_('JTSETTINGS_LISTVALUE_NOBODY').'</option>';
            $accessLiving .= HTMLHelper::_('select.options', HtmlHelper::_('access.assetgroups'), 'value', 'text', $row->accessLiving);
            $accessLiving .= '</select>';
            $altLiving  = '<select id="altLiving'.$row->id.'" name="altLiving'.$row->id.'" class="inputbox" onchange="javascript:changeAccessLevel(\'cb'.$i.'\')">';
            $altLiving .= '<option  value="">'.Text::_('JTSETTINGS_LISTVALUE_NOBODY').'</option>';
            $altLiving .= HTMLHelper::_('select.options', HtmlHelper::_('access.assetgroups'), 'value', 'text', $row->altLiving);
            $altLiving .= '</select>';
            $html[] = '<tr class="row'.($i % 2).'">';
            $html[] = '  <td class="nowrap center hidden-phone">'.$this->pagination->getRowOffset($i).'</td>';
            $html[] = '  <td class="center hidden-phone">'.HTMLHelper::_('grid.id', $i, $row->id).'</td>';
            $html[] = '  <td class="hidden-phone" >';
            $html[] = HTMLHelper::_('image', 'admin/icon-16-notice-note.png', null, 'title="'.$this->showExplanation($row, $i).'"', true);
            $html[] = '  </td>';
            $html[] = '  <td class="nowrap hidden-phone">'.$gedcomLabel.'</td>';
            $html[] = '  <td class="order nowrap center hidden-phone">';
            if ($canDo->get('core.edit')) {
                $html[] = '<span class="sortable-handler hasTooltip" >';
                $html[] = '<i class="icon-menu"></i>';
                $html[] = '</span>';
                $html[] = '<input type="text" name="order[]" size="5" value="'.$row->ordering.'" class="width-20 text-area-order " />';
            } else {
                $html[] = '<span class="sortable-handler inactive" >';
                $html[] = '<i class="icon-menu"></i>';
                $html[] = '</span>';
            }
            $html[] = '  </td>';
            $html[] = '  <td class="center hidden-phone">'.$published.'</td>';
            $html[] = '  <td class="center hidden-phone active">';
            $html[] = '    <a href="javascript:jtsaveaccess(\'cb'.$i.'\')" title="'.Text::_('JTSETTINGS_TOOLTIP_SAVEACCESS').'">';
            $html[] = '    <span style="padding-left: 16px; background: url(/media/com_joaktree/images/filesave.png) no-repeat;"></span>';
            $html[] = '    </a>';
            $html[] = '  </td>';
            $html[] = '  <td class="hidden-phone">'.$access.'</td>';
            $html[] = '  <td class="hidden-phone">'.$accessLiving.'</td>';
            $html[] = '  <td class="hidden-phone">'.$altLiving.'</td>';
            $html[] = '</tr>';
        }
        $html[] = '</table>';
        $html[] = '<input type="hidden" name="option" value="com_joaktree" />';
        $html[] = '<input type="hidden" name="task" value="" />';
        $html[] = '<input type="hidden" name="controller" value="settings" />';
        $html[] = '<input type="hidden" name="boxchecked" value="0" />';
        $html[] = '<input type="hidden" name="layout" value="'.$this->layout.'" />';
        $html[] = HTMLHelper::_('form.token');
        $html[] = '</div>';
        $html[] = '</form>';
        return implode("\n", $html);
    }

    private function showExplanation($row, $i)
    {
        $html = '';
        $color 		= 'blue';
        $value 			= strtoupper(Text::_($row->code));
        $txtPerson		= Text::_('JTSETTINGS_EXPTEXT_PERSON').'&nbsp;';
        $personNotLiv	= $txtPerson.Text::_('JTSETTINGS_EXPTEXT_NOT_LIVPERSON').':&nbsp;';
        $personLiving	= $txtPerson.Text::_('JTSETTINGS_EXPTEXT_LIVPERSON').':&nbsp;';
        if (!$row->published) {
            // nothing is published
            $html .= $value.'&nbsp;'.Text::_('JTSETTINGS_EXPTEXT_FULLYHIDDEN');
        } else {
            // something is published
            $html .= $personNotLiv.$value.'&nbsp;';
            $html .= Text::_('JTSETTINGS_EXPTEXT_ACCESSLEVELS').'&nbsp;';
            $html .= $row->access_level .'.';
            if ((($row->accessLiving != null) and ($row->accessLiving != 0))
               or (($row->altLiving    != null) and ($row->altLiving    != 0))
            ) {
                if (($row->accessLiving != null) and ($row->accessLiving != 0)) {
                    $html .= $personLiving.$value.'&nbsp;';
                    $html .= Text::_('JTSETTINGS_EXPTEXT_ACCESSLEVELS').'&nbsp;';
                    $html .= $row->access_level_living;
                }
                if (($row->altLiving != null)
                   and ($row->altLiving != 0)
                   and ($row->altLiving != $row->accessLiving)
                ) {
                    $html .= $personLiving;
                    $html .= Text::_('JTSETTINGS_EXPTEXT_ALTTEXT').'&nbsp;';
                    $html .= $row->access_level_alttext;
                    if (($row->accessLiving != null) and ($row->accessLiving != 0)) {
                        $html .= Text::_('JTSETTINGS_EXPTEXT_ALTTEXT2').'&nbsp;';
                        $html .= $row->access_level_living;
                    }
                }
                $html .= '.';
            } else {
                $html .= $personLiving.$value.'&nbsp;';
                $html .= Text::_('JTSETTINGS_EXPTEXT_FULLYHIDDEN');
            }
        }
        return $html;
    }
}
