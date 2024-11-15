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

$lines = $this->person->getNotes($this->notes[ 'subtype' ], $this->notes[ 'orderNumber' ], $this->notes[ 'relation_id' ]);

$html .= '<div class="jt-source">';
$html .= '<div class="jt-h3">';
$html .= '<span style="float: left;">'.Text::_('JT_NOTES').'</span>';
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
	
	$text = str_replace('&#10;&#13;', '<br />', $line->text);
	$text = wordwrap($text, $width, '<br />');
	$html .= $text;			
} // end of loop

$html .= '</div>';
echo $html;
?>


