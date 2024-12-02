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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

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
if ($displaysort == "true") {
    $values = new \stdClass();
    $values->div = "sort";
    $values->div_line = $line;
    $values->div_pos = "1";
    $pos = $values->div_pos;
    $values->div_width = "5";
    $values->div_align = "";
    $values->offcanvas = "false";
    $width += 5;
    $layouts['sort'] = $values;
}
if ($displaysearch == "true") {
    $values = new \stdClass();
    $values->div = "search";
    $pos += 1;
    $values->div_pos = $pos;
    $values->div_line = $line;
    $width += 4;
    $values->div_width = "4";
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['search'] = $values;
}
if ($displayfamily != "false") {
    $values = new \stdClass();
    $values->div = "family";
    $pos += 1;
    if ($width + 6 > 12) {
        $pos = 1;
        $line += 1;
        $width = 0;
    }
    $width += 12;
    $values->div_line = $line;
    $values->div_pos = $pos;
    $values->div_width = "12";
    $values->div_align = "";
    $values->offcanvas = "false";
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
    $values->offcanvas = "false";
    $layouts['range'] = $values;
}
if ($displayalpha != "false") {
    $values = new stdClass();
    $line += 1;
    $values->div = "alpha";
    $values->div_line = $line;
    $values->div_pos = "1";
    $values->div_width = "12";
    $values->div_align = "";
    $values->offcanvas = "false";
    $layouts['alpha'] = $values;
}

$values = new \stdClass();
$values->div = "iso";
$line += 1;
$values->div_line = $line;
$values->div_pos = "1";
$values->div_width = "12";
$values->div_align = "";
$values->offcanvas = "false";
$layouts["iso"] = $values;

foreach ($layouts as $layout) {
    $layouts_order[$layout->div_line.$layout->div_pos] = $layout->div;
}
?>
<div id="isotope-main-<?php echo $com_id;?>" data="<?php echo $com_id;?>" class="isotope-main container">
<div class="isotope-div row" >
<?php
// =====================================sort buttons div =================================================//
$sort_buttons_div = "";
if ($displaysort != "hide") {
    $awidth = $layouts["sort"]->div_width;

    $sort_buttons_div = '<div class="isotope_button-group sort-by-button-group col-md-'.$awidth.' col-12 '.$layouts["sort"]->div_align.'" data="'.$com_id.'">';
    $checked = " is-checked ";

    if ($btndate != "false") {
        $sens = $this->iso_params->get('btndate', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "date_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' is-checked iso_button_date" data-sort-value="date,title,family" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libdate.'</button>';
        $checked = "";
    }
    if ($btnfamily != "false") {
        $sens = $this->iso_params->get('btnfamily', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "family_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_cat" data-sort-value="family,title,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libfamily.'</button>';
        $checked = "";
    }
    if ($btnalpha != "false") {
        $sens = $this->iso_params->get('btnalpha', 'true') == 'true' ? '+' : '-';
        $sens = $defaultdisplay == "alpha_desc" ? "-" : $sens;
        $sort_buttons_div .= '<button class="'.$button_bootstrap.$checked.' iso_button_alpha" data-sort-value="title,family,date" data-init="'.$sens.'" data-sens="'.$sens.'" title="'.$libreverse.'">'.$libalpha.'</button>';
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
if (($displayfamily != "false")) {
    // family sort
    asort($this->sortFilter, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL); // alphabatic order
    if (($displayfamily == "button")  || ($displayfamily == "multi")) {
        $awidth = $layouts["family"]->div_width;
        $filter_family_div .= '<div class="isotope_button-group filter-button-group-family col-md-'.$awidth.' col-12 '.$layouts["family"]->div_align.'" data-filter-group="family" data="'.$com_id.'">';
        $checked = "";
        if ($this->default_family == "") {
            $checked = "is-checked";
        }
        $filter_family_div .= '<button class="'.$button_bootstrap.'  iso_button_family_tout '.$checked.'" data-sort-value="*" />'.$liball.'</button>';
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
                $filter_family_div .= '<button class="'.$button_bootstrap.'  iso_button_family_'.$aff_alias.' '.$checked.'" data-sort-value="'.$aff_alias.'" title="'.$this->families[$key].'"/>'.Text::_($aff).$familycount.'</button>';
            }
        }
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

    $isotope_grid_div .=  '<div class="isotope_item iso_family_'.$alias.'" data-family="'.$alias.'" data-title="'.$item->familyName.'" data-id="'.$item->id.'"';
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
    
    
    $title = '<a href="'.$link.'" target="_blank">'.$item->firstName.' '.$item->familyName.'</a>';

    $perso = "";// $this->iso_params->get('perso');

    $arr_css = array("title" => $title);
    foreach ($arr_css as $key_c => $val_c) {
        $perso = str_replace($key_c, Text::_($val_c), $perso);
    }
    $perso = $title;
    if ($item->birthDate || $item->deathDate) {
        if ($item->birthDate) {
            $perso .= '<br>'.Text::_('JT_ISO_BIRTH').' : '.$item->birthDate;
        }
        if ($item->deathDate) {
            $perso .= '<br>'.Text::_('JT_ISO_DEATH').' : '.$item->deathDate;
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
    $isotope_alpha_div = '<div class="isotope_button-group filter-button-group-alpha iso_alpha col-md-'.$awidth.' col-12 '.$layouts["alpha"]->div_align.'" data-filter-group="alpha" data="'.$com_id.'">';
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
