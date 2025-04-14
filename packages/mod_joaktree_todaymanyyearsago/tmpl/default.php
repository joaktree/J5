<?php
/**
 * Joomla! module Joaktree Today Many Years Ago
 *
 * @author	Niels van Dantzig
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Joomla! 5.x conversion by Conseilgouz
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joaktree\Component\Joaktree\Site\Model\TodaymanyyearsagoModel;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

// include stylesheets and javascript
$model = new TodaymanyyearsagoModel();
$theme = $model->getThemeName();

// scripts
$base	= 'media/com_joaktree/';
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
$wa->registerAndUseStyle('jtthemecss',JoaktreeHelper::joaktreecss($theme));

$wa->registerAndUseScript('toggle', $base.'js/toggle.js');
$wa->registerAndUseScript('jtajax', $base.'js/jtajax.js');

// Include language file from Joaktree component;
$jtlang = Factory::getApplication()->getLanguage();
$jtlang->load('com_joaktree');
$jtlang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);


$day    	= $model->getDay();
$days   	= $model->getDays();
$month  	= $model->getMonth();
$months 	= $model->getMonths();
$jtlist 	= $model->getList($module->id);
$title  	= $model->getTitle();
$sorting 	= $model->getSorting();
$buttonText	= $model->getButtonText();

$html = '';

$showdivid = 'jtmodmanyyearsago'.$params->get('moduleclass_sfx');
$html .= '<div id="'.$showdivid .'" >';
$html .= (!empty($title)) ? '<h3>'.$title.'</h3>' : '';

if (count($jtlist) > 0) {
    for ($i = 0;$i < count($jtlist);$i++) {
        if ($sorting == 1) {
            // sorting by day
            $prevDay = ($i == 0) ? '-1' : $jtlist[$i - 1]->eventday;

            if ($jtlist[$i]->eventday != $prevDay) {
                $html .= '<h4>';
                $html .= $jtlist[$i]->eventday;
                $html .= '</h4>';
            }


        } else {
            // sorting by year
            $prevYear = ($i == 0) ? '-1' : $jtlist[$i - 1]->eventyear;

            if ($jtlist[$i]->eventyear != $prevYear) {
                $html .= '<h4>';
                $html .= Text::sprintf('JTMOD_TMYA_HEADING_YEARSAGO', $jtlist[$i]->yearsago);
                $html .= '</h4>';
            }
        }

        $html .= '<p>';
        $html .= '<a href="'.$jtlist[$i]->route.'" '.$jtlist[$i]->robot.'>';
        $html .= $jtlist[$i]->name;
        $html .= '</a>';
        $html .= '<br />';
        $html .= $jtlist[$i]->code.':&nbsp;'.$jtlist[$i]->eventdate;
        $html .= '</p>';
    }

} else {
    $html .= '<div class="jt-high-row">'.Text::_('JTMOD_TMYA_HEADING_NOEVENTS').'</div>';
}

// end of <div id="$showdivid">
$html .= '</div>';

// user interaction
if ($params->get('freeChoice')) {
    $pickdateid = 'txtjttmya'.$params->get('moduleclass_sfx');
    $html .= '<div style="float: right;">';

    $html .= '<a href="#" title="'.Text::_('JTMOD_TMYA_DESC_PICKDATE').'"';
    $html .= 'onclick="jttmya_toggle(\''.$pickdateid.'\');return false;">'.Text::_('JTMOD_TMYA_BUTTON_PICKDATE').'</a>';
    $html .= '</div>';

    // hidden area
    $html .= '<div id="'.$pickdateid.'" class="jt-hide">';

    // line with select boxes for days and months
    $class = 'class="inputbox"';
    $html .= '<div class="jt-high-row" style="float: right">';

    if (($params->get('periodType') != 2) && ($params->get('periodType') != 3)) {
        // days - not shown when parameter setting is on months
        $html .= HTMLHelper::_('select.genericlist', $days, 'days', $class, 'value', 'text', $day, 'day');
        $html .= '&nbsp;&nbsp;';
    } else {
        $html .= '<input id="day" type="hidden" value="0">';
    }
    // months
    $html .= HTMLHelper::_('select.genericlist', $months, 'months', $class, 'value', 'text', $month, 'month');
    $html .= '</div>';

    // empty line
    $html .= '<div class="jt-high-row">&nbsp;</div>';
    $html .= '<div class="jt-clearfix"></div>';

    // line with execution button
    $link =  'index.php?format=raw&option=com_joaktree&view=todaymanyyearsago'
            .'&tmpl=component&module='.$module->id;

    $html .= '<div class="jt-high-row" style="float: right">';
    $html .= '<a href="#" class="jt-button-closed jt-buttonlabel" ';
    $html .= 'title="'.Text::_('JTMOD_TMYA_DESC_GO').'"';
    $html .= 'onclick="getManyYearsAgo(\'day\', \'month\', \''.$showdivid.'\', \''.$link.'\');return false;">'.Text::_('JTMOD_TMYA_BUTTON_GO').'</a>';
    $html .= '</div>';

    // empty line
    $html .= '<div class="jt-high-row">&nbsp;</div>';
    $html .= '<div class="jt-clearfix"></div>';

    // button returning to today
    $html .= '<div class="jt-high-row" style="float: right">';
    $html .= '<a href="#" class="jt-button-closed jt-buttonlabel" ';
    $html .= 'title="'.$buttonText.'"';
    $html .= 'onclick="getManyYearsAgo(0, 0, \''.$showdivid.'\', \''.$link.'\');return false;">'.$buttonText.'</a>';
    $html .= '</div>';

    $html .= '</div>';
    // end of hidden area

    $html .= '<div class="jt-clearfix"></div>';
}


echo $html;
?>

