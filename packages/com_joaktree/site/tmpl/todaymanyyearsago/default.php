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

// no direct access
defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;

$html = '';
$html .= (!empty($this->title)) ? '<h3>'.$this->title.'</h3>' : '';

if(count($this->jtlist) > 0) { 
	
	for ($i=0;$i<count($this->jtlist);$i++) {
		if ($this->sorting == 1) {
			// sorting by day
			$prevDay = ($i == 0) ? '-1' : $this->jtlist[$i-1]->eventday;
			
			if ($this->jtlist[$i]->eventday != $prevDay) {
				$html .= '<h4>';
				$html .= $this->jtlist[$i]->eventday;
				$html .= '</h4>';
			}
			
			
		} else {
			// sorting by year
			$prevYear = ($i == 0) ? '-1' : $this->jtlist[$i-1]->eventyear;
			
			if ($this->jtlist[$i]->eventyear != $prevYear) {
				$html .= '<h4>';
				$html .= Text::sprintf('JTMOD_TMYA_HEADING_YEARSAGO', $this->jtlist[$i]->yearsago);
				$html .= '</h4>';
			}
		}
	
		$html .= '<p>';
		$html .= '<a href="'.$this->jtlist[$i]->route.'" '.$this->jtlist[$i]->robot.'>';
		$html .= $this->jtlist[$i]->name;
		$html .= '</a>';
		$html .= '<br />';
		$html .= $this->jtlist[$i]->code.':&nbsp;'.$this->jtlist[$i]->eventdate;
		$html .= '</p>';				
		
	}
} else {
	$html .= '<div class="jt-high-row">'.Text::_('JTMOD_TMYA_HEADING_NOEVENTS').'</div>';
}

echo $html;
?>

