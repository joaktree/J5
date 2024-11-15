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

if (isset($this->notes[ 'relation_id' ])) {
	$lines = $this->person->getSources($this->sources[ 'subtype' ], $this->sources[ 'orderNumber' ], $this->sources[ 'relation_id' ]);
} else {
	$lines = $this->person->getSources($this->sources[ 'subtype' ], $this->sources[ 'orderNumber' ], null);
}

$html = '';
$width = 120;

$html .= '<div class="jt-source">';
$html .= '<div class="jt-h3">';
$html .= '<span style="float: left;">'.Text::_('JT_SOURCES').' '.Text::_('JT_FOR').' '.$this->person->firstName.' '.$this->person->familyName.'</span>';

if ($this->lists['technology'] != 'b') {
	// box can be closed
	$html .= '<a href="#" style="float: right;" title="'.Text::_('JT_CLOSE').'" ';
	$html .= 'onclick="toggleNotesSources(0, \'jt1notesid\', \'jt2notesid\', \'jt1sourcesid\', \'jt2sourcesid\');return false;">';
	$html .= '<strong>x</strong>';
	$html .= '</a>';
}
$html .= '</div>';
$html .= '<div class="jt-clearfix"></div>';

// loop through citations
$i=0;
$n=count( $lines );

foreach ($lines as $line) {
	if ( ($line->quotation) or ($line->note) or ($line->information) ) {
		$pstyle = '<p class="jt-nobottom-row">';
	} else {
		$pstyle = '<p>';
	}
	$html .= $pstyle;
	
	$emptyline = true;
	
	// if more than 1 source, a number is shown
	if ($n > 1) {
		$i++;
		$html .= $i.'.&nbsp;';
	}
	
	if ($line->title) {
		$html .= '<span class="jt-source-title">' . $line->title . '</span>';
		$emptyline = false;
	}
	
	if ($line->publication) {
		if (!$emptyline) {
			$html .= ', ';
		}
		$html .=  $line->publication;
		$emptyline = false;
	}
	
	if ($line->author) {
		if (!$emptyline) {
			$html .= '&nbsp;';
		}
		$html .= '(' . $line->author . ')';
		$emptyline = false;
	}
	
	if ($line->page) {
		if (!$emptyline) {
			$html .= ', ';
		}
		$html .=  $line->page;
		$emptyline = false;
	}
	
	if ($line->information) {
		$line->information = str_replace('&#10;&#13;', '<br />', $line->information);
		$html .=  $line->information.'<br /><br />';
							
		$emptyline = false;
	}
	
	if ($line->repository) {
		$html .= '&nbsp;[';
		if ($line->website) {
			$html .= '<a href="' . $line->website . '" target="_repository">';
		}
		$html .= $line->repository;
		if ($line->website) {
			$html .= '</a>';
		}
		$html .= ']';
	}
	
	$html .= '</p>';
	
	if ($line->quotation) {
		$html .= '<p class="jt-nomargin-row">&nbsp;&nbsp;&nbsp;<span class="jt-label">' . Text::_('JT_QUOTE') . ': </span>' . $line->quotation . '</p>';
	}

	if ($line->note) {
		$html .= '<p class="jt-nomargin-row">&nbsp;&nbsp;&nbsp;<span class="jt-label">' . Text::_('JT_NOTE') . ': </span>' . $line->note . '</p>';
	}

} // end of loop
$html .= '</div>';

echo $html;
?>


