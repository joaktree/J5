<?php 
/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;		//replace JRoute

$html = '';
$linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$this->lists['technology'].'';
$linkBaseRaw = 'index.php?format=raw&tmpl=component&option=com_joaktree&view=joaktree&tech='.$this->lists['technology'].'';
$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';

$children	= $this->person->getChildren('full');
$partners	= $this->person->getPartners('full');

// Button for editing (only active with AJAX)
if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
	if (is_object($this->canDo)) {
	   	$html .= '<div class="jt-clearfix"></div>';
	   	$html .= '<div class="jt-edit-2" style="text-align: right;">';
   		if ($this->canDo->get('core.create')) {
			$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'newchild\');" >';
			$html .= Text::_('JT_ADDCHILD');
			$html .= '</a>';
   		} else {
   			$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
   			$html .= Text::_('JT_ADDCHILD');
   			$html .= '</span>';
   		}
   		
   		$html .= '&nbsp;|';
   		
   		if (count( $children ) > 0) {
   			if ($this->canDo->get('core.edit')) {
				$html .= '&nbsp;<a href="#" onclick="jtsubmitbutton(\'edit\', \'children\');" >';
				$html .= Text::_('JT_EDITCHILDREN');
				$html .= '</a>';
	   		} else {
				$html .= '&nbsp;<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
	   			$html .= Text::_('JT_EDITCHILDREN');
   				$html .= '</span>';
	   		}
	   		$html .= '&nbsp;|';
   		}
   		$html .= '</div>';
   }	
}

// when there are no children , skip this routine
if (count($children) > 0 ) {
	// check for children with only one parent
	$counter_1 = 0;
	foreach ($children as $child) {
		if ( $child->secondParent_id == null ) {
			$counter_1++;
		}
	}
	
	if ($counter_1 > 0) {
		// children with only one parent
		$html .= '<div class="jt-clearfix">';
		$html .= '<span class="jt-left-col-label jt-h3">' . Text::_('JT_CHILDREN') . '</span>';
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<span class="jt-right-col-label jt-h3">&nbsp;</span>';
		}
		$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_BORN') . '</span>';
		$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_DIED') . '</span>';
		$html .= '</div>';
		
		// loop through the children and filter on the one parent children
		foreach ($children as $child) {
			if ( $child->secondParent_id == null ) {
				$divid = $this->person->id.$child->id;
				$link  = Route::_( $linkBase.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
				$html .= '<div class="jt-clearfix">';
				
				// name of person
				$html .= '<span class="jt-table-row jt-left-col-label">';
				if ($child->indHasPage) { 
					$html .= '<a href="' . $link . '" '.$robot.' >'; 
				}
				$html .= $child->firstNamePatronym . " " . $child->familyName;
				if ($child->indHasPage) { 
					$html .= '</a>'; 
				}
				if ($child->relationtype) {
					// show type of child
					$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($child->relationtype));
				}
				$html .= '</span>';
				
				// links to children-of-child and details
				if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
					$html .= '<span class="jt-detail-col-label">';
					
					$link =  Route::_($linkBaseRaw.'&layout=_personevents'
						.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
					$html .= '<a href="#" id="bev'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
					$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DETAILS').'" ';
					$html .= 'onclick="drilldownAjaxDetail(\'bev'.$divid.'\', \'event'.$divid.'\', \''.$link.'\');return false;">';
					$html .= Text::_('JT_DETAILS') . '</a>&nbsp;';
					
					if ($child->indHasChild == true) {
						$link =  Route::_($linkBaseRaw.'&layout=_grandchildren'
							.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
						$html .= '<a href="#" id="but'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
						$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_CHILDREN').'" ';
						$html .= 'onclick="drilldownAjaxDetail(\'but'.$divid.'\', \'child'.$divid.'\', \''.$link.'\');return false;">'.Text::_('JT_CHILDREN_BUTTON').'</a>';
					} else {
						$html .=  '<span class="jt-empty-icon">&nbsp;</span>';
					}
					
					$html .= '</span>';
				}
				
				// basic information
				$html .= '<span class="jt-right-col-label">' . $child->birthDate . '&nbsp;</span>';
				$html .= '<span class="jt-right-col-label">' . $child->deathDate . '&nbsp;</span>';
				
				$html .= '</div>';
				
				// Block with details and children-of-child is shown below 
				if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
					$html .= '<div id="event'.$divid.'" class="jt-clearfix jt-person-info jt-person-drilldown1-info jt-ajax">';
					$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
					
					if ($child->indHasChild == true) {
						$html .= '<div id="child'.$divid.'" class="jt-clearfix jt-person-drilldown1-info jt-ajax">';
						$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
					}
				}
			}
		}
	}
	
	// check for situation of only one spouse, and no childresn out of wedlock
	if ( ( $counter_1 == 0 ) and ( count($partners) == 1 ) ) {
		// display children from one partner
		$html .= '<div class="jt-clearfix">';
		$html .= '<span class="jt-left-col-label jt-h3">' . Text::_('JT_CHILDREN') . '</span>';
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<span class="jt-right-col-label jt-h3">&nbsp;</span>';
		}
		$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_BORN') . '</span>';
		$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_DIED') . '</span>';
		$html .= '</div>';
		
		// loop through the children without filtering
		foreach ($children as $child) {
			$divid = $this->person->id.$child->id;
			$link  = Route::_( $linkBase.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
			$html .= '<div class="jt-clearfix">';
			
			// name of person
			$html .= '<span class="jt-table-row jt-left-col-label">';
			if ($child->indHasPage) { 
				$html .= '<a href="' . $link . '" '.$robot.' >'; 
			}
			$html .= $child->firstNamePatronym . " " . $child->familyName;
			if ($child->indHasPage) { 
				$html .= '</a>'; 
			}
			if ($child->relationtype) {
				// show type of child
				$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($child->relationtype));
			}
			$html .= '</span>';
			
			// links to children-of-child and details
			if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
				$html .= '<span class="jt-detail-col-label">';
				
				$link =  Route::_($linkBaseRaw.'&layout=_personevents'
					.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
				$html .= '<a href="#" id="bev'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
				$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DETAILS').'" ';
				$html .= 'onclick="drilldownAjaxDetail(\'bev'.$divid.'\', \'event'.$divid.'\', \''.$link.'\');return false;">';
				$html .= Text::_('JT_DETAILS') . '</a>&nbsp;';
				
				if ($child->indHasChild == true) {
					$link =  Route::_($linkBaseRaw.'&layout=_grandchildren'
						.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
					$html .= '<a href="#" id="but'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
					$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_CHILDREN').'" ';
					$html .= 'onclick="drilldownAjaxDetail(\'but'.$divid.'\', \'child'.$divid.'\', \''.$link.'\');return false;">'.Text::_('JT_CHILDREN_BUTTON').'</a>';
				} else {
					$html .=  '<span class="jt-empty-icon">&nbsp;</span>';
				}
				
				$html .= '</span>';
			}
			
			// basic information
			$html .= '<span class="jt-right-col-label">' . $child->birthDate . '&nbsp;</span>';
			$html .= '<span class="jt-right-col-label">' . $child->deathDate . '&nbsp;</span>';
			
			$html .= '</div>';
			
			// Block with details and children-of-child is shown below 
			if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
				$html .= '<div id="event'.$divid.'" class="jt-clearfix jt-person-info jt-person-drilldown1-info jt-ajax">';
				$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
				
				if ($child->indHasChild == true) {
					$html .= '<div id="child'.$divid.'" class="jt-clearfix jt-person-drilldown1-info jt-ajax">';
					$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
				}
			}
		}
	} else {
		// loop through the partners of person
		foreach ($partners as $partner) {
			// count children for this partner
			$counter = 0;
			foreach ($children as $child) {
				if ( $child->secondParent_id == $partner->id ) {
					$counter++;
				}
			}
			
			if ($counter > 0) {
				// children with this partner
				// remove first brackets
				$partnerName = explode('(', $partner->firstName);
				
				$html .= '<div class="jt-clearfix">';
				$html .= '<span class="jt-left-col-label jt-h3">' . Text::_('JT_CHILDREN_WITH') . ' ' . $partnerName[0] . '</span>';
				if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
					$html .= '<span class="jt-right-col-label jt-h3">&nbsp;</span>';
				}
				$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_BORN') . '</span>';
				$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_DIED') . '</span>';
				$html .= '</div>';
				
				// loop through the children and filter on the correct parent 
				foreach ($children as $child) {
					if ( $child->secondParent_id == $partner->id ) {
						$divid = $this->person->id.$child->id;
						$link  = Route::_( $linkBase.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
						$html .= '<div class="jt-clearfix">';
						
						// name of person
						$html .= '<span class="jt-table-row jt-left-col-label">';
						if ($child->indHasPage) { 
							$html .= '<a href="' . $link . '" '.$robot.' >'; 
						}
						$html .= $child->firstNamePatronym . " " . $child->familyName;
						if ($child->indHasPage) { 
							$html .= '</a>'; 
						}
						if ($child->relationtype) {
							// show type of child
							$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($child->relationtype));
						}
						$html .= '</span>';
						
						// links to children-of-child and details
						if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
							$html .= '<span class="jt-detail-col-label">';
							
							$link =  Route::_($linkBaseRaw.'&layout=_personevents'
								.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
							$html .= '<a href="#" id="bev'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
							$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DETAILS').'" ';
							$html .= 'onclick="drilldownAjaxDetail(\'bev'.$divid.'\', \'event'.$divid.'\', \''.$link.'\');return false;">';
							$html .= Text::_('JT_DETAILS') . '</a>&nbsp;';
							
							if ($child->indHasChild == true) {
								$link =  Route::_($linkBaseRaw.'&layout=_grandchildren'
									.'&Itemid='.$child->menuItemId.'&treeId='.$child->tree_id.'&personId='.$child->app_id.'!'.$child->id);
								$html .= '<a href="#" id="but'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
								$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_CHILDREN').'" ';
								$html .= 'onclick="drilldownAjaxDetail(\'but'.$divid.'\', \'child'.$divid.'\', \''.$link.'\');return false;">'.Text::_('JT_CHILDREN_BUTTON').'</a>';
							} else {
								$html .=  '<span class="jt-empty-icon">&nbsp;</span>';
							}
							
							$html .= '</span>';
						}
						
						// basic information
						$html .= '<span class="jt-right-col-label">' . $child->birthDate . '&nbsp;</span>';
						$html .= '<span class="jt-right-col-label">' . $child->deathDate . '&nbsp;</span>';
						
						$html .= '</div>';
						
						// Block with details and children-of-child is shown below 
						if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
							$html .= '<div id="event'.$divid.'" class="jt-clearfix jt-person-info jt-person-drilldown1-info jt-ajax">';
							$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
							
							if ($child->indHasChild == true) {
								$html .= '<div id="child'.$divid.'" class="jt-clearfix jt-person-drilldown1-info jt-ajax">';
								$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
							}
						}
					}
				}
			}
			
		}
	}
}

echo $html;
?>

