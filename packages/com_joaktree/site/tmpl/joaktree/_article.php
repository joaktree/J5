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

$html = '';

$article = $this->person->getArticle($this->notes[ 'orderNumber' ], $this->notes[ 'app_id' ], $this->notes[ 'person_id' ], $this->notes[ 'type' ]);

if ($this->notes[ 'type' ] == 'note') {
	$article->text = str_replace("&#10;&#13;", "<br />", $article->text);
}

$html .= '<div class="jt-h2 contentheading">'.$article->title.'</div>';
if (!empty($article->modified)) {
	$html .= '<div class="modifydate">'.$article->modified.'</div>';
}
$html .= '<div class="article-content">'.$article->text.'</div>';	

echo $html;

?>


