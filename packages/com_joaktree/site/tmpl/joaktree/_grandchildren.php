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
$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';

$children	= $this->person->getChildren('full');
$partners	= $this->person->getPartners('full');

// when there are no children , skip this routine
if (count($children) > 0 ) {
	$html .= '<div class="jt-clearfix">';
	$html .= '<span class="jt-h3">'.Text::_('JT_GRANDCHILDREN').' - '.Text::_('JT_CHILDREN_OF').' '.$this->person->firstName.'</span>';
	$html .= '</div>';
	
	// check for children with only one parent
	$counter_1 = 0;
	foreach ($children as $child) {
		if ( $child->secondParent_id == null ) {
			$counter_1++;
		}
	}
	
	if ($counter_1 > 0) {
		// children with only one parent
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
				$html .= '</span>';
				
				// basic information
				$html .= '<span class="jt-detail-col-label">&nbsp;</span>';
				$html .= '<span class="jt-right-col-label">' . $child->birthDate . '&nbsp;</span>';
				$html .= '<span class="jt-right-col-label">' . $child->deathDate . '&nbsp;</span>';
				
				$html .= '</div>';
			}
		}
	}

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
					$html .= '</span>';
					
					// basic information
					$html .= '<span class="jt-detail-col-label">&nbsp;</span>';
					$html .= '<span class="jt-right-col-label">' . $child->birthDate . '&nbsp;</span>';
					$html .= '<span class="jt-right-col-label">' . $child->deathDate . '&nbsp;</span>';
					
					$html .= '</div>';
				} // end of check on second parent
			} // end of loop through children
		} // end of children with specific partner
	} // end loop through partners

	
} else {
	$html .= '<div class="jt-clearfix">';
	$html .= '<span class="jt-table-row">' . Text::_('JT_NODATA').'</span>';
	$html .= '</div>';
}

echo $html;
?>

