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
use Joomla\CMS\Router\Route;
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

$html = '';

// start with the name of the person
$html .= '<div class="jt-h1" style="float: left; width: 85%;">';
$html .= !empty($this->person->prefix) ? $this->person->prefix.'&nbsp;' : '';
$html .= $this->person->firstNamePatronym.'&nbsp;'.$this->person->familyName;
$html .= !empty($this->person->suffix) ? '&nbsp;'.$this->person->suffix : '';
$html .= '</div>';

// Button for editing (only active with AJAX)
if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
	if (is_object($this->canDo)) {
			$html .= '<div class="jt-edit-2" style="text-align: right;">';
			$displayNot = FormHelper::checkDisplay('person', $this->person->living, 'NOTE');
			$displayRef = FormHelper::checkDisplay('person', $this->person->living, 'SOUR');
			
	   		if  ($this->canDo->get('core.edit')) {
				$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'names\');" >';
				$html .= Text::_('JT_EDITNAMES');
				$html .= '</a>&nbsp;|<br />';
				
				if ($displayNot) {
		   			$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'notes\');" >';
					$html .= Text::_('JT_EDITNOTES');
					$html .= '</a>&nbsp;|<br />';
				}
				if ($displayRef) {
					$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'references\');" >';
					$html .= Text::_('JT_EDITREFS');
					$html .= '</a>&nbsp;|<br />';
				}
			} else {
				$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
				$html .= Text::_('JT_EDITNAMES').'</span>&nbsp;|<br />';
				if ($displayNot) {
					$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
					$html .= Text::_('JT_EDITNOTES').'</span>&nbsp;|<br />';
				}
				if ($displayRef) {
					$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
					$html .= Text::_('JT_EDITREFS').'</span>&nbsp;|<br />';
				}
			}

			if ($this->canDo->get('core.edit.state')) {
	   			$html .= '<a href="#" onclick="jtsubmitbutton(\'edit\', \'state\');" >';
				$html .= Text::_('JT_EDITSTATE');
				$html .= '</a>&nbsp;|<br />';
	   		} else {
				$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
	   			$html .= Text::_('JT_EDITSTATE').'</span>&nbsp;|<br />';
	   		}
	
	   		if ($this->canDo->get('core.delete')) {
	   			$html .= '<a href="#" onclick="jtsubmitbutton(\'delete\');" >';
				$html .= Text::_('JT_DELETE');
				$html .= '</a>&nbsp;|';
	   		} else {
				$html .= '<span class="jt-edit-nolink" title="'.Text::_('JT_NOPERMISSION_DESC').'" >';
	   			$html .= Text::_('JT_DELETE').'</span>&nbsp;|';
	   		}
	
	   		$html .= '</div>';
	   }	
}


// Buttons for showing ancestors / descendants (no ajax)
if (($this->lists['technology'] == 'b') or ($this->lists['technology'] == 'j')) {
	$html .= '<div style="float: right; padding-top: 20px; width: 9%;">';
	
	if ( ($this->person->indHasParent == true) && ($this->lists['showAncestors'] == 1) ) {
			$link =  Route::_('index.php?option=com_joaktree'
					.'&view=ancestors'
					.'&personId='.$this->person->app_id.'!'.$this->person->id
					.'&treeId='.$this->person->tree_id
					);
	
			//$html .= '<div>';
			$html .= '<a href="'.$link.'#S'.$this->person->id.'" id="jt1descid" class="jt-button-closed jt-buttonlabel" ';
			$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_ANCESTORS').'" >';
			$html .= Text::_('JT_ANCESTOR_BUTTON') . '</a>&nbsp;';
			//$html .= '</div>';
			
	}
	
	if ( ($this->person->indHasChild == true) && ($this->lists['showDescendants'] == 1) ) {
			$link =  Route::_('index.php?option=com_joaktree'
					.'&view=descendants'
					.'&personId='.$this->person->app_id.'!'.$this->person->id
					.'&treeId='.$this->person->tree_id
					);
		
			//$html .= '<div>';
			$html .= '<a href="'.$link.'#S'.$this->person->id.'" id="jt1descid" class="jt-button-closed jt-buttonlabel" ';
			$html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DESCENDANTS').'" >';
			$html .= Text::_('JT_DESCENDANT_BUTTON') . '</a>';
			//$html .= '</div>';
			
	}
	
	$html .= '</div>';
}
//$html .= '<div class="jt-clearfix">&nbsp;</div>';

$names = $this->person->getPersonNames(); 
$i=0;

foreach ($names as $name) {
	if (  ($name->value   != null) 
	   or ($name->indNote     == true)
	   or ($name->indCitation == true)
	   ) {
		$html .= '<div class="jt-clearfix">';
		// show label
		$html .= '<span class="jt-low-row jt-label">'.Text::_($name->code).': </span>';
		$html .= '<span class="jt-low-row jt-iconlabel">'; 
		
		// show icon for note(s)
		if ($name->indNote == true) {
			$njtid1 = 'jt1notnam'.$i.$this->person->id;
			$njtid2 = 'jt2notnam'.$i.$this->person->id;
			if ($this->lists['technology'] != 'b') {
				$html .= '<a href="#" id="'.$njtid1.'" class="jt-notes-icon"';
				
				if (($this->lists['technology'] == 'j') or ($name->indAltNote == true)) {
					$html .= 'onMouseOver="ShowPopup(\''.$njtid1.'\', \''.$njtid2.'\', 0, 0);return false;"';
				} else {
					$link =  Route::_('index.php?format=raw&option=com_joaktree'
						.'&view=joaktree&layout=_detailnotes'
						.'&tmpl=component&type=person&subtype=name&orderNumber='.$name->orderNumber
						.'&personId='.$this->person->app_id.'!'.$this->person->id
						.'&treeId='.$this->person->tree_id
						);
					$html .= 'onMouseOver="ShowAjaxPopup(\''.$njtid1.'\', \''.$njtid2.'\', \''.$link.'\');return false;"';
				}
				
				$html .= 'onMouseOut="HidePopup(\''.$njtid2.'\');return false;">';
				$html .= '&nbsp;</a>';
			}
		} else {
			$html .= '<span class="jt-empty-icon">&nbsp;</span>';
		}
		
		// show icon for source(s)
		if ($name->indCitation == true) {
			$sjtid1 = 'jt1srcnam'.$i.$this->person->id;
			$sjtid2 = 'jt2srcnam'.$i.$this->person->id;
			if ($this->lists['technology'] != 'b') {
				$html .= '<a href="#" id="'.$sjtid1.'" class="jt-sources-icon"';
				if (($this->lists['technology'] == 'j') or ($name->indAltSource == true)) {
					$html .= 'onMouseOver="ShowPopup(\''.$sjtid1.'\', \''.$sjtid2.'\', 0, 0);return false;"';
				} else {
					$link =  Route::_('index.php?format=raw&option=com_joaktree'
						.'&view=joaktree&layout=_detailsources'
						.'&tmpl=component&type=person&subtype=name&orderNumber='.$name->orderNumber
						.'&personId='.$this->person->app_id.'!'.$this->person->id
						.'&treeId='.$this->person->tree_id
						);
					$html .= 'onMouseOver="ShowAjaxPopup(\''.$sjtid1.'\', \''.$sjtid2.'\', \''.$link.'\');return false;"';
				}
				
				$html .= 'onMouseOut="HidePopup(\''.$sjtid2.'\');return false;">';
				$html .= '&nbsp;</a>';
			}
		} else {
			$html .= '<span class="jt-empty-icon">&nbsp;</span>';
		}
		$html .= '</span>'; // end of jt-iconlabel
		
		// show actual value
		$html .= '<span class="jt-low-row jt-valuelabel">';
		$html .= $name->value;
		$html .= '</span>';
		
		$html .= '</div>'; // end of line
	}
		
	// show note and source divs
	if ($name->indNote == true) {
		if ($this->lists['technology'] != 'b') {
			if (($this->lists['technology'] == 'j') or ($name->indAltNote == true)) {
				$html .= '<div id="'.$njtid2.'" class="jt-hide" style="position: absolute; z-index: 50;">';
			} else {
				$html .= '<div id="'.$njtid2.'" class="jt-ajax" style="position: absolute; z-index: 50;">';
			} 
			
			if ($name->indAltNote == true) {
				$html .= '<div class="jt-note">'.Text::_('JT_ALTERNATIVE').'</div>';
			} else if (($this->lists['technology'] != 'b') and($this->lists['technology'] != 'j')) {
				$html .= '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_NOTES').'</div>';
			} else {
				// prepare for template
				$notes[ 'type' ]     	= 'person';
				$notes[ 'subtype' ]  	= 'name';
				$notes[ 'orderNumber' ]	= $name->orderNumber;
				$notes[ 'relation_id' ]	= null;
				//$this->assignRef( 'notes',	$notes);
				$this->notes = $notes;
			
				// show template
				$layout = $this->setLayout('');
				$html .= $this->loadTemplate('detailnotes');
				$this->setLayout($layout);
			}
			$html .= '</div>';
		}
	}
	
	if ($name->indCitation == true) {
		if ($this->lists['technology'] != 'b') {
			if (($this->lists['technology'] == 'j') or ($name->indAltSource == true)) {
				$html .= '<div id="'.$sjtid2.'" class="jt-hide" style="position: absolute; z-index: 50;">';
			} else {
				$html .= '<div id="'.$sjtid2.'" class="jt-ajax" style="position: absolute; z-index: 50;">';
			} 
			
			if ($name->indAltSource == true) {
				$html .= '<div class="jt-source">'.Text::_('JT_ALTERNATIVE').'</div>';
			} else if (($this->lists['technology'] != 'b') and($this->lists['technology'] != 'j')) {
				$html .= '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_SOURCES').'</div>';
			} else {
				// prepare for template
				$sources[ 'type' ]     		= 'person';
				$sources[ 'subtype' ]  		= 'name';
				$sources[ 'orderNumber' ]	= $name->orderNumber;
				$sources[ 'relation_id' ]	= null;
				//$this->assignRef( 'sources',	$sources);
				$this->sources = $sources;
	
				// show template
				$layout = $this->setLayout('');
				$html .= $this->loadTemplate('detailsources');
				$this->setLayout($layout);
			}
			$html .= '</div>';
		}
	}
	
	$i++;
}

echo $html;
?>



