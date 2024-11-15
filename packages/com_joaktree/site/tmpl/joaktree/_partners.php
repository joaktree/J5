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
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

$html = '';
$partner = array();
$linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$this->lists['technology'].'';
$linkBaseRaw = 'index.php?format=raw&tmpl=component&option=com_joaktree&view=joaktree&tech='.$this->lists['technology'].'';
$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';
$partners = $this->person->getPartners('full');
// Button for editing (only active with AJAX)
if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
	if (is_object($this->canDo)) {
   		$html .= '<div class="jt-clearfix"></div>';
   		$html .= '<div class="jt-edit-2" style="text-align: right;">';
		if ($this->canDo->get('core.create')) {
			$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'newpartner\');" >';
			$html .= Text::_('JT_ADDPARTNER');
			$html .= '</a>';
   		} else {
			$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
   			$html .= Text::_('JT_ADDPARTNER');
   			$html .= '</span>';
   		}
   		$html .= '&nbsp;|';
   		if (count($partners) > 0) {
   			if ($this->canDo->get('core.edit')) {
				$html .= '&nbsp;<a href="#" onclick="jtsubmitbutton(\'edit\', \'partners\');" >';
				$html .= Text::_('JT_EDITPARTNERS');
				$html .= '</a>';
   			} else {
				$html .= '&nbsp;<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
	   			$html .= Text::_('JT_EDITPARTNERS');
   				$html .= '</span>';
	   		$html .= '&nbsp;|';
   			}
   		}
   		$html .= '</div>';
   }	
}
if (count( $partners ) > 0) {
	if (count( $partners ) == 1) {
		switch ( $partners[0]->sex ) {
			case "M":	$label = ($partners[0]->relationtype == 'partner') 
									? Text::_('JT_PARTNER') : Text::_('JT_HUSBAND');
					BREAK;
			case "F": 	$label = ($partners[0]->relationtype == 'partner') 
									? Text::_('JT_PARTNER') : Text::_('JT_WIFE');
					BREAK;
			default: 	$label = Text::_('JT_PARTNER');
					BREAK;
		}
	} else {
		switch ( $partners[0]->sex ) {
			case "M":	$label = ($partners[0]->relationtype == 'partner') 
									? Text::_('JT_PARTNERS') : Text::_('JT_HUSBANDS');
					BREAK;
			case "F": 	$label = ($partners[0]->relationtype == 'partner') 
									? Text::_('JT_PARTNERS') : Text::_('JT_WIFES');
					BREAK;
			default: 	$label = Text::_('JT_PARTNERS');
					BREAK;
		}
	}
	$html .= '<div class="jt-clearfix">';
	$html .= '<span class="jt-left-col-label jt-h3">' . $label . '</span>';
	if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
		$html .= '<span class="jt-detail-col-label jt-h3">&nbsp;</span>';
	}
	$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_BORN') . '</span>';
	$html .= '<span class="jt-right-col-label jt-h3">' . Text::_('JT_DIED') . '</span>';
	$html .= '</div>';
	foreach ($partners as $partner) {
		// Button for editing (only active with AJAX)
		if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
			if (is_object($this->canDo)) {
			   	$indliving = (($this->person->living) && ($partner->living)) ? true : false;
			   	$display = FormHelper::checkDisplay('relation', $indliving);
			   	If ($display) {
					$html .= '<div class="jt-clearfix"></div>';
				   	$html .= '<div class="jt-edit-2" style="text-align: right;">';
					if ($this->canDo->get('core.edit'))  {
						$html .= '<a href="#" ';
						$html .= '   title="'.Text::sprintf('JT_EDITPARTNEREVENTS_DESC', $partner->firstName).'"';
						$html .= '   onclick="jtsetrelation(\''.$partner->id.'\'); jtsubmitbutton(\'edit\', \'partnerevents\');" >';
						$html .= Text::_('JT_EDITPARTNEREVENTS');
						$html .= '</a>';
					} else {
						$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
			   			$html .= Text::_('JT_EDITPARTNEREVENTS');
		   				$html .= '</span>';
					}
		   			$html .= '&nbsp;|';
					$html .= '</div>';
			   	}
			}	
		}		
		$divid 	= $this->person->id.$partner->id;
		$link  = Route::_( $linkBase.'&Itemid='.$partner->menuItemId.'&treeId='.$partner->tree_id.'&personId='.$partner->app_id.'!'.$partner->id);
		$html .= '<div class="jt-clearfix">';
		// name of person
		$html .= '<span class="jt-table-row jt-left-col-label">';
		if ($partner->indHasPage) { 
			$html .= '<a href="' . $link . '" '.$robot.' >';
		}
		$html .= $partner->firstNamePatronym . " " . $partner->familyName;
		if ($partner->indHasPage) { 
			$html .= '</a>';
		}
		$html .= '</span>';
		// links to details
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<span class="jt-detail-col-label">';
			$link =  Route::_($linkBaseRaw.'&layout=_personevents'
				.'&Itemid='.$partner->menuItemId.'&treeId='.$partner->tree_id.'&personId='.$partner->app_id.'!'.$partner->id);
			$html .= '<a href="#" id="bev'.$divid.'"class="jt-button-closed jt-buttonlabel" ';
			$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DETAILS').'" ';
			$html .= 'onclick="drilldownAjaxDetail(\'bev'.$divid.'\', \'event'.$divid.'\', \''.$link.'\');return false;">';
			$html .= Text::_('JT_DETAILS') . '</a>&nbsp;';
			$html .= '</span>';
		}
		// basic information
		$html .= '<span class="jt-right-col-label">' . $partner->birthDate . '&nbsp;</span>';
		$html .= '<span class="jt-right-col-label">' . $partner->deathDate . '&nbsp;</span>';
		$html .= '</div>';
		// show relation events
		$partner2[ 'id' ]     	= $partner->id;
		$partner2[ 'living' ]	= $partner->living;
		//$this->assignRef( 'partner',	$partner2);
		$this->partner = $partner;
		// show template
		$layout = $this->setLayout('');
		$html .= self::loadTemplate('partnerevents');
		$this->setLayout($layout);
		// block with details is shown below the person
		if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
			$html .= '<div id="event'.$divid.'" class="jt-clearfix jt-person-drilldown1-info jt-ajax">';
			$html .= '<div class="jt-high-row jt-ajax-loader">&nbsp;</div></div>';
		}
	}
}
echo $html;
?>



