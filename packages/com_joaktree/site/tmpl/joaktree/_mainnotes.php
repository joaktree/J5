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

$html = '';
if (isset($this->notes[ 'relation_id' ])) {
	$lines = $this->person->getNotes($this->notes[ 'subtype' ], $this->notes[ 'orderNumber' ], $this->notes[ 'relation_id' ]);
} else {
	$lines = $this->person->getNotes($this->notes[ 'subtype' ], $this->notes[ 'orderNumber' ], null);
}


$html .= '<div class="jt-note">';
$html .= '<div class="jt-h3">';
$html .= '<span style="float: left;">'.Text::_('JT_NOTES').' '.Text::_('JT_FOR').' '.$this->person->firstName.' '.$this->person->familyName.'</span>';

if ($this->lists['technology'] != 'b') {
	// box can be closed
	$html .= '<a href="#" style="float: right;" title="'.Text::_('JT_CLOSE').'" ';
	$html .= 'onclick="toggleNotesSources(0, \'jt1notesid\', \'jt2notesid\', \'jt1sourcesid\', \'jt2sourcesid\');return false;">';
	$html .= '<strong>x</strong>';
	$html .= '</a>';
} 
$html .= '</div>';
$html .= '<div class="jt-clearfix"></div>';


// loop through notes
$i=0;
$n=count( $lines );

foreach ($lines as $line) {
	// if more than 1 note, a number is shown
	if ($n > 1) {
		$i++;
		$html .= $i.'.&nbsp;';
	}
	
	$rows = (int) floor(strlen($line->text)/80);
	$html .= '<textarea rows="'.$rows.'" cols="98">' .$line->text . '</textarea>';			
} // end of loop

$html .= '</div>';

echo $html;

?>


