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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$html = '';
$linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$this->lists['technology'].'';
$linkBaseRaw = 'index.php?format=raw&tmpl=component&option=com_joaktree&view=joaktree&tech='.$this->lists['technology'].'';
$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';

$fathers = $this->person->getFathers('full'); 
$mothers = $this->person->getMothers('full'); 

// Button for editing (only active with AJAX)
if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
	if (is_object($this->canDo)) {
		$html .= '<div class="jt-clearfix"></div>';
		$html .= '<div class="jt-edit-2" style="text-align: right;">';
		if ($this->canDo->get('core.create')) {
			$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'newparent\');" >';
			$html .= Text::_('JT_ADDPARENT');
			$html .= '</a>';
   		} else {
			$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
   			$html .= Text::_('JT_ADDPARENT');
   			$html .= '</span>';
   		}
   		
   		$html .= '&nbsp;|';
   		 
   		if ((count($fathers) +  count($mothers)) > 0) {
   			if ($this->canDo->get('core.edit')) {
				$html .= '&nbsp;<a href="#" onclick="jtsubmitbutton(\'edit\', \'parents\');" >';
				$html .= Text::_('JT_EDITPARENTS');
				$html .= '</a>';
	   		} else {
				$html .= '&nbsp;<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
	   			$html .= Text::_('JT_EDITPARENTS');
   				$html .= '</span>';
	   		}
	   		$html .= '&nbsp;|';
   		}
		$html .= '</div>';
   }	
}

if ( (count( $fathers ) +  count( $mothers )) > 0) {
	$html .= '<div class="jt-clearfix">';
	$html .= '<span class="jt-left-col-label jt-h3">' . Text::_('JT_PARENTS') . '</span>';
	if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
		$html .= '<span class="jt-detail-col-label jt-h3">&nbsp;</span>';
	}
	$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_BORN') . '</span>';
	$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_DIED') . '</span>';
	$html .= '</div>';
	
	foreach ($fathers as $father) {
		$divid = $this->person->id.$father->id;
		$link  = Route::_( $linkBase.'&Itemid='.$father->menuItemId.'&treeId='.$father->tree_id.'&personId='.$father->app_id.'!'.$father->id);
		
		// Block with parents-of-father is shown above the father
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			if ($father->indHasParent == true) {
				$html .= '<div id="parent'.$divid.'" class="jt-clearfix jt-person-drilldown2-info jt-ajax">';
				$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
			}
		}
				
		$html .= '<div class="jt-clearfix">';
		
		// name of person
		$html .= '<span class="jt-table-row jt-left-col-label">';
		if ($father->indHasPage) { 
			$html .= '<a href="' . $link . '" '.$robot.' >';
		}
		$html .= $father->firstNamePatronym . " " . $father->familyName;
		if ($father->indHasPage) { 
			$html .= '</a>';
		}
		if ($father->relationtype) {
			// show type of father
			$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($father->relationtype));
		}
		$html .= '</span>';
		
		// links to parents-of-father and details
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<span class="jt-detail-col-label">';
			
			$link =  Route::_($linkBaseRaw.'&layout=_personevents'
				.'&Itemid='.$father->menuItemId.'&treeId='.$father->tree_id.'&personId='.$father->app_id.'!'.$father->id);
			$html .= '<a href="#" id="bev'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
			$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DETAILS').'" ';
			$html .= 'onclick="drilldownAjaxDetail(\'bev'.$divid.'\', \'event'.$divid.'\', \''.$link.'\');return false;">';
			$html .= Text::_('JT_DETAILS') . '</a>&nbsp;';
			
			if ($father->indHasParent == true) {
				$link =  Route::_($linkBaseRaw.'&layout=_grandparents'
					.'&Itemid='.$father->menuItemId.'&treeId='.$father->tree_id.'&personId='.$father->app_id.'!'.$father->id);
				$html .= '<a href="#" id="but'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
				$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_PARENTS').'"';
				$html .= 'onclick="drilldownAjaxParent(\'but'.$divid.'\', \'parent'.$divid.'\', \''.$link.'\');return false;">'.Text::_('JT_PARENTS_BUTTON').'</a>';
			} else {
				$html .=  '<span class="jt-empty-icon">&nbsp;</span>';
			}
			
			$html .= '</span>';
		}
		
		// basic information
		$html .= '<span class="jt-right-col-label">' . $father->birthDate . '&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $father->deathDate . '&nbsp;</span>';
		
		$html .= '</div>';
		
		// block with details is shown below the person
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<div id="event'.$divid.'" class="jt-clearfix jt-person-drilldown1-info jt-ajax">';
			$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
		}
	} 

	foreach ($mothers as $mother) {
		$divid = $this->person->id.$mother->id;
		$link  = Route::_( $linkBase.'&Itemid='.$mother->menuItemId.'&treeId='.$mother->tree_id.'&personId='.$mother->app_id.'!'.$mother->id);
		
		// Block with parents-of-mother is shown above the mother
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			if ($mother->indHasParent == true) {
				$html .= '<div id="parent'.$divid.'" class="jt-clearfix jt-person-drilldown2-info jt-ajax">';
				$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
			}
		}

		$html .= '<div class="jt-clearfix">';
		
		// name of person
		$html .= '<span class="jt-table-row jt-left-col-label">';
		if ($mother->indHasPage) { 
			$html .= '<a href="' . $link . '" '.$robot.' >';
		}
		$html .= $mother->firstNamePatronym . " " . $mother->familyName;
		if ($mother->indHasPage) { 
			$html .= '</a>';
		}
		if ($mother->relationtype) {
			// show type of mother
			$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($mother->relationtype));
		}
		$html .= '</span>';
		
		// links to parents-of-mother and details
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<span class="jt-detail-col-label">';
			
			$link =  Route::_($linkBaseRaw.'&layout=_personevents'
				.'&Itemid='.$mother->menuItemId.'&treeId='.$mother->tree_id.'&personId='.$mother->app_id.'!'.$mother->id);
			$html .= '<a href="#" id="bev'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
			$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DETAILS').'" ';
			$html .= 'onclick="drilldownAjaxDetail(\'bev'.$divid.'\', \'event'.$divid.'\', \''.$link.'\');return false;">';
			$html .= Text::_('JT_DETAILS') . '</a>&nbsp;';
			
			if ($mother->indHasParent == true) {
				$link =  Route::_($linkBaseRaw.'&layout=_grandparents'
					.'&Itemid='.$mother->menuItemId.'&treeId='.$mother->tree_id.'&personId='.$mother->app_id.'!'.$mother->id);
				$html .= '<a href="#" id="but'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
				$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_PARENTS').'"';
				$html .= 'onclick="drilldownAjaxParent(\'but'.$divid.'\', \'parent'.$divid.'\', \''.$link.'\');return false;">'.Text::_('JT_PARENTS_BUTTON').'</a>';
			} else {
				$html .=  '<span class="jt-empty-icon">&nbsp;</span>';
			}
			
			$html .= '</span>';
		}
		
		// basic information
		$html .= '<span class="jt-right-col-label">' . $mother->birthDate . '&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $mother->deathDate . '&nbsp;</span>';
		
		$html .= '</div>';
		
		// block with details is shown below the person
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<div id="event'.$divid.'" class="jt-clearfix jt-person-drilldown1-info jt-ajax">';
			$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
		}
	} 
}

echo $html;
?>

