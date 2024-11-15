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

// wat is relation_id
if (($this->person->indNote == true) and ($this->person->indAltNote == false)) {
	$lines = $this->person->getNotes('person', null, null);
}
$items = $this->person->getArticleList();

// dummy arrays
if (!isset($lines) || !is_array($lines)) {
	$lines = array();
}
if (!is_array($items)) {
	$items = array();
}

if (count($items) > 0) {
	$article = $this->person->getArticle($items[0]->id, $this->person->app_id, $this->person->id, 'article');
}

$html .= '<div class="jt-clearfix"></div>';

if ((count($lines) + count($items)) == 1) {
	if (count($items) == 1) {
		// there exists 1 article and no notes
		$html .= '<div class="jt-h2 contentheading">'.$article->title.'</div>';
		$html .= '<div class="article-content">'.$article->text.'</div>';	
	}
	
	
	if (count($lines) == 1) {
		// there exists 1 note and no articles
		// check for sources
		$sources = $this->person->getSources('note', $lines[0]->orderNumber, null);
		$html1  = '';
		if (count($sources) > 0) {
			// there are sources
			// show button
			$buttonId = 'jtinf3sid';
			$divId    = 'jtinf4sid';
			$link =  Route::_('index.php?format=raw&option=com_joaktree'
					.'&view=joaktree&layout=_detailsources'
					.'&tmpl=component&type=person&subtype=note&orderNumber='.$lines[0]->orderNumber
					.'&personId='.$this->person->app_id.'!'.$this->person->id
					.'&treeId='.$this->person->tree_id
					);
					
			
			$html1 .= '<a href="#" id="'.$buttonId.'" class="jt-sources-icon" ';
			$html1 .= 'onMouseOver="ShowAjaxPopup(\''.$buttonId.'\', \''.$divId.'\', \''.$link.'\');return false;" ';
			$html1 .= 'onMouseOut="HidePopup(\''.$divId.'\');return false;">';							
			$html1 .= '&nbsp;</a>';
		}
		
		
		$lines[0]->text  = str_replace("&#10;&#13;", "<br />", $lines[0]->text);
		$lines[0]->title = str_replace("&#10;&#13;", "<br />", $lines[0]->title);
		$html .= '<h2 class="contentheading">'.$lines[0]->title.$html1.'</h2>';
		$html .= '<div class="article-content">'.$lines[0]->text.'</div>';	
		
		if (count($sources) > 0) {			
			// show text
			if ($this->person->indAltSource == true) {
				$html .= '<div id="'.$divId.'" class="jt-hide" style="position: absolute; z-index: 50;">';
				$html .= '<div class="jt-source">'.Text::_('JT_ALTERNATIVE').'</div>';
				$html .= '</div>';
			} else
				$html .= '<div id="'.$divId.'" class="jt-ajax" style="position: absolute; z-index: 50;">';
				$html .= '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_SOURCES').'</div>';
				$html .= '</div>';
			} 
		} 
	
} else if ((count($lines) + count($items)) > 1) {
	$html .= '<div style="float: left; width: 20%;">';
	$html .= '<table>';
	$html .= '<tr><th class="jt-content-th">'.Text::_('JT_NUM').'</th><th class="jt-content-th">'.Text::_('JT_ARTICLES').'</th></tr>';
	
	$k = 2;
	$counter = 0;
	$busysignal = Text::_('JT_LOADING').'&nbsp;'.Text::_('JT_ARTICLE');
	
	foreach ($items as $item) {
		$counter++;
		$link = Route::_('index.php?format=raw&option=com_joaktree'
				.'&view=joaktree&layout=_article'
				.'&tmpl=component&orderNumber='.$item->id.'&type=article'
				.'&personId='.$this->person->app_id.'!'.$this->person->id
				.'&treeId='.$this->person->tree_id
				);
		$rowclass = 'jt-table-entry' . $k;
		$html .= '<tr class="'.$rowclass.'"><td>'.$counter.'</td>';
		$html .= '<td><a href="#" onclick="retrieveAjaxArticle(\''.$link.'\', \'art1id\', \''.$busysignal.'\'); return false;" >'.$item->title.'</a></td></tr>';
		$k = 3 - $k;
	}
	
	foreach ($lines as $line) {
		$counter++;
		$link = Route::_('index.php?format=raw&option=com_joaktree'
				.'&view=joaktree&layout=_article'
				.'&tmpl=component&orderNumber='.$line->orderNumber.'&type=note'
				.'&personId='.$this->person->app_id.'!'.$this->person->id
				.'&treeId='.$this->person->tree_id
				);
		$rowclass = 'jt-table-entry' . $k;
		$html .= '<tr class="'.$rowclass.'"><td>'.$counter.'</td>';
		$html .= '<td><a href="#" onclick="retrieveAjaxArticle(\''.$link.'\', \'art1id\', \''.$busysignal.'\'); return false;" >';
		$html .= $line->title;
		$html .= '</a></td></tr>';
		$k = 3 - $k;
	}
	
	$html .= '</table>';
	$html .= '</div>';
	
	$html .= '<div style="float: right; width: 78%;">';
	$html .= '<div id="art1id">';
	
	if (count($items) > 0) {
		$html .= '<h2 class="contentheading">'.$article->title.'</h2>';
		$html .= '<div class="article-content">'.$article->text.'</div>';	
	} else if (count($lines) > 0) {
		$lines[0]->text  = str_replace("&#10;&#13;", "<br />", $lines[0]->text);
		$lines[0]->title = str_replace("&#10;&#13;", "<br />", $lines[0]->title);
		$html .= '<h2 class="contentheading">'.$lines[0]->title.'</h2>';
		$html .= '<div class="article-content">'.$lines[0]->text.'</div>';	
	}

	$html .= '</div>';
	$html .= '</div>';
	
	$html .= '<div class="jt-clearfix"></div>';
}


echo $html;

?>


