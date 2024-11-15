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

$fathers = $this->person->getFathers('full'); 
$mothers = $this->person->getMothers('full'); 

if ( (count( $fathers ) +  count( $mothers )) > 0) {
	$html .= '<div class="jt-clearfix">';
	$html .= '<span class="jt-h3">' . Text::_('JT_GRANDPARENTS') . ' - ' . Text::_('JT_PARENTS_OF') . ' ' . $this->person->firstName . '</span>';
	$html .= '</div>';
	
	foreach ($fathers as $father) {
		$link  = Route::_( $linkBase.'&Itemid='.$father->menuItemId.'&treeId='.$father->tree_id.'&personId='.$father->app_id.'!'.$father->id);
		$html .= '<div class="jt-clearfix">';
		
		$html .= '<span class="jt-table-row jt-left-col-label">';
		if ($father->indHasPage) { 
			$html .= '<a href="' . $link . '" '.$robot.' >';
		}
		$html .= $father->firstNamePatronym . " " . $father->familyName;
		if ($father->indHasPage) { 
			$html .= '</a>';
		}
		$html .= '</span>';
		
		$html .= '<span class="jt-detail-col-label">&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $father->birthDate . '&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $father->deathDate . '&nbsp;</span>';
		
		$html .= '</div>';
	} 

	foreach ($mothers as $mother) {
		$link  = Route::_( $linkBase.'&Itemid='.$mother->menuItemId.'&treeId='.$mother->tree_id.'&personId='.$mother->app_id.'!'.$mother->id);
		$html .= '<div class="jt-clearfix">';
		
		$html .= '<span class="jt-table-row jt-left-col-label">';
		if ($mother->indHasPage) { 
			$html .= '<a href="' . $link . '" '.$robot.' >';
		}
		$html .= $mother->firstNamePatronym . " " . $mother->familyName;
		if ($mother->indHasPage) { 
			$html .= '</a>';
		}
		$html .= '</span>';
		
		$html .= '<span class="jt-detail-col-label">&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $mother->birthDate . '&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $mother->deathDate . '&nbsp;</span>';
		$html .= '</div>';
	} 
} else {
	$html .= '<div class="jt-clearfix">';
	$html .= '<span class="jt-table-row">' . Text::_('JT_NODATA').'</span>';
	$html .= '</div>';
}

echo $html;
?>

