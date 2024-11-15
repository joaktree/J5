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

$html = '';

if ($this->person->indNote == true) {
	if ($this->lists['technology'] == 'b') {
		$html .= '<div id="jt2notesid" class="jt-clearfix">';
	} else if ($this->lists['technology'] == 'j') {
		$html .= '<div id="jt2notesid" class="jt-hide">';
	} else {
		// AJAX: notes moved to tabpage
		// $html .= '<div id="jt2notesid" class="jt-ajax">';
		$html .= '<div>';
	} 
	
	if ($this->person->indAltNote == true) {
		$html .= '<div class="jt-note">'.Text::_('JT_ALTERNATIVE').'</div>';
	} else if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
		// AJAX: notes moved to tabpage
		// $html .= '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_NOTES').'</div>';
		$html .= '';
	} else {
		// prepare for template
		$notes[ 'type' ]     	= 'person';
		$notes[ 'subtype' ]  	= 'person';
		$notes[ 'orderNumber' ]	= null;
		//$this->assignRef( 'notes',	$notes);
		$this->notes = $notes;
	
		// show template
		$layout = $this->setLayout('');
		$html .= $this->loadTemplate('mainnotes');
		$this->setLayout($layout);
	}
	$html .= '</div>';
}

if ($this->person->indCitation == true) {
	if ($this->lists['technology'] == 'b') {
		$html .= '<div id="jt2sourcesid" class="jt-clearfix">';
	} else if ($this->lists['technology'] == 'j') {
		$html .= '<div id="jt2sourcesid" class="jt-hide">';
	} else {
		$html .= '<div id="jt2sourcesid" class="jt-ajax">';
	} 
	
	if ($this->person->indAltSource == true) {
		$html .= '<div class="jt-source">'.Text::_('JT_ALTERNATIVE').'</div>';
	} else if (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
		$html .= '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_SOURCES').'</div>';
	} else {
		// prepare for template
		$sources[ 'type' ]     	= 'person';
		$sources[ 'subtype' ]  	= 'personAll';
		$sources[ 'orderNumber' ]	= null;
		//$this->assignRef( 'sources',	$sources);
		$this->sources = $sources;
	
		// show template
		$layout = $this->setLayout('');
		$html .= $this->loadTemplate('mainsources');
		$this->setLayout($layout);
	}
	$html .= '</div>';
}

echo $html
?>
