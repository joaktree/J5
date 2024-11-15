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
$width = 100;
$lines = $this->person->getSources($this->sources[ 'subtype' ], $this->sources[ 'orderNumber' ], $this->sources[ 'relation_id' ]);


$html .= '<div class="jt-source">';
$html .= '<div class="jt-h3">';
$html .= '<span style="float: left;">'.Text::_('JT_SOURCES').'</span>';
$html .= '</div>';
$html .= '<div class="jt-clearfix"></div>';


// loop through citations
$i = 0;
$n = count($lines);

foreach ($lines as $line) {
	if ( ($line->quotation) or ($line->note) ) {
		$pstyle = '<p class="jt-nobottom-row">';
	} else {
		$pstyle = '<p>';
	}
	
	$html .= $pstyle;
	// if more than 1 source, a number is shown
	if ($n > 1) {
		$i++;
		$html .= $i.'.&nbsp;';
	}
		
	// set up empty line
	$htmlline = '';
	
	if ($line->title) {
		$htmlline .= '<span class="jt-source-title">' . $line->title . '</span>';
	}
	
	if ($line->publication) {
		if ($htmlline != '') {
			$htmlline .= ', ';
		}		
		$htmlline .= $line->publication;
	}
	
	if ($line->author) {
		if ($htmlline != '') {
			$htmlline .= '&nbsp;';
		}
		$htmlline .= '(' . $line->author . ')';
	}
	
	if ($line->page) {
		if ($htmlline != '') {
			$htmlline .= ', ';
		}
		$htmlline .= $line->page;
	}
	
	// if line is not empty, produce html
	if ($htmlline != '') {
		$html .=  wordwrap($htmlline, $width, '<br />');
	} 
	
	if ($line->information) {
		$html .=  wordwrap($line->information, $width, '<br />');
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
		$html .= '<p class="jt-nomargin-row">&nbsp;&nbsp;&nbsp;<span class="jt-label">'; 
		$html .= Text::_('JT_QUOTE') . ': </span>' . wordwrap($line->quotation, $width, '<br />');
		$html .= '</p>';
	}

	if ($line->note) {
		$html .= '<p class="jt-nomargin-row">&nbsp;&nbsp;&nbsp;<span class="jt-label">';
		$html .= Text::_('JT_NOTE') . ': </span>' . wordwrap($line->note, $width, '<br />') ;
		$html .= '</p>';
	}

} // end of loop

$html .= '</div>';
echo $html;
?>


