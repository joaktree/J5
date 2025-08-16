<?php
/**
* CG Isotope Component  - Joomla 4.x/5.x Component
* Package			: CG ISotope
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*
*/
defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

header('Content-Type: text/html; charset=utf-8');

$uri = Uri::getInstance();
$app = Factory::getApplication();
$user = $app->getIdentity();
$com_id = $app->input->getInt('Itemid');

$comfield = ''.URI::base(true).'/media/com_joaktree/iso';
$displaysort = "true";
$displaysearch = "true";
$displayfamily = $this->displayfilterfamily;
$displayalpha = $this->displayalpha;
$familyfiltercount = $this->familyfiltercount;
$displayrange =  $this->displayrange;
$button_bootstrap = 'btn btn-sm ';
$btndate = "true";
$btnalpha = "true";
$btnfamily = "true";

$defaultdisplay = "";

$libreverse = Text::_('JT_ISO_LIBREVERSE');
$liball = Text::_('JT_ISO_LIBALL');
$libdate = Text::_('JT_ISO_LIBDATE');
$libfamily = Text::_('JT_ISO_FAMILY');
$libalpha = Text::_('JT_ISO_LIBALPHA');
$libsearch = Text::_('JT_ISO_LIBSEARCH');
$libsearchclear = Text::_('JT_ISO_SEARCHCLEAR');


//==============================LAYOUTS======================================//
$layouts = [];
$layouts_order = [];
// Default values
$width = 1;
$line = 1;
$pos = 0;
// if ($displaysort == "true") {
$values = new \stdClass();
$values->div = "sort";
$values->div_line = $line;
$values->div_pos = "1";
$pos = $values->div_pos;
$values->div_width = "3";
$values->div_align = "";
$width += 3;
$layouts['sort'] = $values;
//}
// if ($displaysearch == "true") {
$values = new \stdClass();
$values->div = "search";
$pos += 1;
$values->div_pos = $pos;
$values->div_line = $line;
$width += 4;
$values->div_width = "4";
$values->div_align = "";
$layouts['search'] = $values;
// }
// if ($displayalpha != "false") {
$values = new stdClass();
$values->div = "alpha";
$line += 1;
$values->div_pos = 1;
$values->div_width = "12";
$values->div_align = "text-center";
$values->div_line = $line;
$layouts['alpha'] = $values;
// }
if ($displayfamily != "hide") {
    $values = new \stdClass();
    $values->div = "family";
    $pos += 1;
    $values->div_line = $line;
    if ($displayfamily == 'list' || $displayfamily == "listmulti") { // sur 1ere ligne
        $values->div_line = 1;
        $values->div_pos = $pos;
        $width = 5;
        $values->div_width = "5";
        $values->div_align = "";
    } else { // buttons : on a new line
        $pos = 1;
        $line += 1;
        $width = 12;
        $values->div_line = $line;
        $values->div_width = "12";
        $values->div_pos = $pos;
        $values->div_align = "text-center";
    }
    $layouts['family'] = $values;
}
if ($displayrange == "true") {
    $values = new stdClass();
    $line += 1;
    $values->div = "range";
    $values->div_line = $line;
    $values->div_pos = "1";
    $values->div_width = "12";
    $values->div_align = "";
    $layouts['range'] = $values;
}

$values = new \stdClass();
$values->div = "iso";
$line += 1;
$values->div_line = $line;
$values->div_pos = "1";
$values->div_width = "12";
$values->div_align = "";
$layouts["iso"] = $values;

foreach ($layouts as $layout) {
    $layouts_order[$layout->div_line.$layout->div_pos] = $layout->div;
}
?>
<div id="isotope-loading-<?php echo $com_id;?>" class="article-loading"></div>
<div id="isotope-main-<?php echo $com_id;?>" data="<?php echo $com_id;?>" class="isotope-main container hidden">
<div class="isotope-div row" >
<?php
// =====================================sort buttons div =================================================//
$sort_buttons_div = "";
if ($displaysort != "hide") {
    $awidth = $layouts["sort"]->div_width;

    $sort_buttons_div = '<div class="isotope_button-group sort-btn-grp col-md-'.$awidth.' col-12 '.$layouts["sort"]->div_align.'" data="'.$com_id.'">';
    $checked = " is-checked ";

    if ($btndate != "false") {
        $sens = $this->iso_params->get('btndate', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "date_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' is-checked iso_btn_date" data-sv="date,title,family" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libdate.'</button>';
        $checked = "";
    }
    if ($btnfamily != "false") {
        $sens = $this->iso_params->get('btnfamily', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "family_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_btn_family" data-sv="family,title,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libfamily.'</button>';
        $checked = "";
    }
    if ($btnalpha != "false") {
        $sens = $this->iso_params->get('btnalpha', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "alpha_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_btn_alpha" data-sv="title,family,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libalpha.'</button>';
        $checked = "";
    }
    $sort_buttons_div .= "</div>";
}
// ============================search div ============================================//
$search_div = "";
if ($displaysearch == "true") {
    $awidth = $layouts["search"]->div_width;
    if (!property_exists($layouts["search"], 'offcanvas')) {
        $layouts["search"]->offcanvas = "false";
    }
    if ($layouts["search"]->offcanvas == "true") {
        $awidth = 12;
    }
    $search_div .= '<div class="iso_search col-md-'.$awidth.' col-12 '.$layouts["search"]->div_align.'" data="'.$com_id.'" >';
    $search_div .= '<input type="text" class="quicksearch " placeholder="'.$libsearch.'" style="width:80%;float:left" >';
    $search_div .= '<i class="ison-cancel-squared " title="'.$libsearchclear.'" style="width:20%;float:right" ></i>';
    $search_div .= '</div>';
}
//============================filter div===============================================//
$filter_family_div = "";
if (($displayfamily != "hide")) {
    // family sort
    asort($this->sortFilter, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // alphabatic order
    if (($displayfamily == "button")  || ($displayfamily == "multi")) {
        $awidth = $layouts["family"]->div_width;
        $filter_family_div .= '<div class="iso_btn-grp filter-btn-grp-family col-md-'.$awidth.' col-12 '.$layouts["family"]->div_align.'" data-fg="family" data="'.$com_id.'">';
        $checked = "";
        if ($this->default_family == "") {
            $checked = "is-checked";
        }
        $filter_family_div .= '<button class="'.$button_bootstrap.'  iso_btn_family_tout '.$checked.'" data-sv="*" >'.$liball.'</button>';
        foreach ($this->sortFilter as $key => $filter) {
            $aff = $this->families[$key];
            $aff_alias = $this->families_alias[$key];
            if (!is_null($aff)) {
                $checked = "";
                if ($this->default_family == $aff_alias) {
                    $checked = "is-checked";
                }
                $familycount = '';
                if ($familyfiltercount == 'true') {
                    $familycount = '<span class="family-count badge bg-info">'.$this->family_count[$aff_alias].'</span>';
                }
                $filter_family_div .= '<button class="'.$button_bootstrap.'  iso_btn_family_'.$aff_alias.' '.$checked.'" data-sv="'.$aff_alias.'" title="'.$this->families[$key].'">'.htmlentities($aff).$familycount.'</button>';
            }
        }
        $filter_family_div .= '</div>';
    } else {
        Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');
        $app->getDocument()->getWebAssetManager()
             ->useScript('webcomponent.field-fancy-select')
             ->usePreset('choicesjs');
        $attributes = array(
            'class="isotope_select"',
            ' data-fg="family"',
            ' id="isotope-select-family"'
        );
        $selectAttr = array();
        $multiple = "";
        if ($displayfamily == "listmulti") {
            $libmulti = Text::_('CG_ISO_LIBLISTMULTI');
            $multiple = "  place-placeholder='".$libmulti."'";
            $selectAttr = array(' multiple');
        }
        $awidth = $layouts["family"]->div_width;
        $filter_family_div .= '<div class="iso_btn-grp filter-btn-grp-family col-md-'.$awidth.' col-12 '.$layouts["family"]->div_align.'" data-fg="family" data="'.$com_id.'">';
        $name = 'isotope-select-family';
        $options = array();
        $options['']['items'][] = ModulesHelper::createOption('', $liball);
        foreach ($this->sortFilter as $key => $filter) {
            $aff = $this->families[$key];
            $aff_alias = $this->families_alias[$key];
            $familycount = '';
            if ($familyfiltercount == 'true') {
                $familycount = ' ('.$this->family_count[$aff_alias].') ';
            }
            if (!is_null($aff)) {
                $selected = "";
                if ($this->default_family == $aff_alias) {
                    $selected = "selected";
                }
                $options['']['items'][] = ModulesHelper::createOption($aff_alias, Text::_($aff).$familycount);
            }
        }
        $filter_family_div .= '<joomla-field-fancy-select '.implode(' ', $attributes).'>';
        $filter_family_div .= HTMLHelper::_('select.groupedlist', $options, $name, array('id'          => $name,'list.select' => null,'list.attr'   => implode(' ', $selectAttr)));

        $filter_family_div .= '</joomla-field-fancy-select>';
        $filter_family_div .= '</div>';
    }
}

//============================= isotope grid =============================================//
$width = $layouts["iso"]->div_width;
$isotope_grid_div = "";

$isotope_grid_div .= '<div class="isotope_grid col-md-'.$width.' col-12" style="padding:0" data="'.$com_id.'">'; // bootstrap : suppression du padding pour isotope
foreach ($this->personlist as $item) {

    $data_range = "";
    if (($displayrange == "true")) { // display birth or death date
        if ($item->birthDate) {
            $data_range = " data-range='".$item->birthDate."' ";
        } elseif ($item->deathDate) {
            $data_range = " data-range='".$item->deathDate."' ";
        }
    }

    $alias = ApplicationHelper::stringURLSafe((string) $item->familyName);

    $isotope_grid_div .=  '<div class="iso_itm " data-family="'.$alias.'" data-title="'.$item->firstName.'"';// data-id="'.$item->id.'"';
    $isotope_grid_div .=  ' data-date="'.$item->birthDate.'" data-alpha="'.strtoupper(substr($item->familyName, 0, 1)).'" '.$data_range.'>';

    $menus		= JoaktreeHelper::getMenus('joaktree');
    $treeId     = $this->lists['tree_id'];
    $link = Route::_(
        'index.php?option=com_joaktree&view=joaktree'
                 .'&tech='.$this->lists['technology']
                 .'&Itemid='.$menus[$treeId]
                 .'&treeId='.$treeId
                 .'&personId='.$item->app_id.'!'.$item->id
    );
    $pat = "";
    if ($this->lists['patronym'] != 0) {
        $sep = $this->iso_params->get('patronymSeparation');
        if ($item->patronym) {
            $pat = $sep.htmlentities($item->patronym).$sep.' ';
        }
    }

    $title = '<a href="'.$link.'" target="_blank">'.htmlentities($item->firstName).' '.$pat.htmlentities($item->familyName).'</a>';

    $perso = "";// $this->iso_params->get('perso');

    $arr_css = array("title" => $title);
    foreach ($arr_css as $key_c => $val_c) {
        $perso = str_replace($key_c, Text::_($val_c), $perso);
    }
    $perso = $title;
    if ($item->birthDate || $item->deathDate) {
        $perso .= '<br>';
        if ($item->birthDate) {
            $perso .= $item->birthDate; // '<br>'.Text::_('JT_ISO_BIRTH').' : '.$item->birthDate;
        } else {
            $perso .= '?';
        }
        if ($item->deathDate) {
            $perso .= '-'.$item->deathDate; // Text::_('JT_ISO_DEATH').' : '.$item->deathDate;
        } else {
            $perso .= '-?';
        }
    }
    $isotope_grid_div .= $perso;
    $isotope_grid_div .= '</div>';
}

// ============================range div ==============================================//
$isotope_range_div = "";
if ($displayrange == "true") {
    $awidth = $layouts["range"]->div_width;
    $isotope_range_div = '<div class="iso_range col-md-'.$awidth.' col-12 '.$layouts["range"]->div_align.'" data="'.$com_id.'">';
    $isotope_range_div .= '<div class="col-12"><label title="'.$this->rangedesc.'">'.$this->rangelabel.'</label></div><div class="col-12 col-md-12"><input type="text" id="rSlider" data="'.$com_id.'"/></div>';
    $isotope_range_div .= '</div>';
}
// ============================alpha div ==============================================//
$isotope_alpha_div = "";
if ($displayalpha != "false") {

    $this->alpha = [];
    foreach ($this->personlist as $key => $item) {
        $first = strtoupper(substr($item->familyName, 0, 1));
        if (!in_array($first, $this->alpha)) {
            $this->alpha[] = $first;
        }
    }

    $awidth = $layouts["alpha"]->div_width;
    $isotope_alpha_div = '<div class="iso_btn-grp filter-btn-grp-alpha iso_alpha col-md-'.$awidth.' col-12 '.$layouts["alpha"]->div_align.'" data-fg="alpha" data="'.$com_id.'">';
    $isotope_alpha_div .= JoaktreeHelper::create_alpha_buttons($this, $button_bootstrap);
    $isotope_alpha_div .= '</div>';
}
//=====================================layouts==============================================//
ksort($layouts_order, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // order
$val = 0;
$offcanvas = false;
$line = 0;
foreach ($layouts_order as $layout) {
    $key = (string)$layout;
    $obj = $layouts[$key];
    $val = $obj->div_width;
    $line = $obj->div_line;

    if (($val > 12) || (($obj->div_width == 12) && ($val > 12))) { // new line needed
        if (($obj->div == "iso") && ($obj->div_width == 12)) {
            echo "</div><div>";
        } else {
            echo "</div><div class='row'>";
        }
        $val = $obj->div_width;
        if ($line < $obj->div_line) { // requested new line
            $line = $obj->div_line;
        }
    }
    if ($obj->div == "search") {
        echo $search_div;
    }
    if ($obj->div == "sort") {
        echo $sort_buttons_div;
    }
    if ($obj->div == "family") {
        echo $filter_family_div;
    }
    if ($obj->div == "iso") {
        echo $isotope_grid_div;
    }
    if ($obj->div == "range") {
        echo $isotope_range_div;
    }
    if ($obj->div == "alpha") {
        echo $isotope_alpha_div;
    }
}

?>
</div>

</div>
<div class="jt-update">
	<?php echo $this->lists[ 'lastUpdate' ]; ?>
</div>
<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>
