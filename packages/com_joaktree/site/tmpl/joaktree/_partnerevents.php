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
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

$html = '';

if (!isset($this->partner)) {
    return;
}
$events = $this->person->getPartnerEvents($this->partner->id, $this->partner->living);

if (count($events) > 0) {
    $html .= '<div class="jt-clearfix jt-person-drilldown1-info">';
}
$i = 0;

foreach ($events as $event) {
    $html .= '<div class="jt-clearfix">';
    if (($event->eventDate != null) or ($event->location != null) or ($event->value != null)) {
        $html .= '<span class="jt-high-row jt-label">' . Text::_($event->code);
        if ($event->type) {
            $tmpType = str_replace(' ', ' ', $event->type);
            $html .= ' - ' . Text::_($tmpType);
        }
        $html .= ': </span>';

        $html .= '<span class="jt-high-row jt-iconlabel">';

        if ($event->indNote == true) {
            $njtid1 = 'jt1notpev'.$i.$this->person->id.$this->partner->id;
            $njtid2 = 'jt2notpev'.$i.$this->person->id.$this->partner->id;
            if ($this->lists['technology'] != 'b') {

                $html .= '<a href="#" id="'.$njtid1.'" class="jt-notes-icon"';

                if (($this->lists['technology'] == 'j') or ($event->indAltNote == true)) {
                    $html .= 'onMouseOver="ShowPopup(\''.$njtid1.'\', \''.$njtid2.'\', 0, 0);return false;"';
                } else {
                    $link =  Route::_(
                        'index.php?format=raw&option=com_joaktree'
                        .'&view=joaktree&layout=_detailnotes'
                        .'&tmpl=component&type=relation&subtype=revent&orderNumber='.$event->orderNumber
                        .'&relationId='.$this->partner->id
                        .'&personId='.$this->person->app_id.'!'.$this->person->id
                        .'&treeId='.$this->person->tree_id
                    );
                    $html .= 'onMouseOver="ShowAjaxPopup(\''.$njtid1.'\', \''.$njtid2.'\', \''.$link.'\');return false;"';
                }

                $html .= 'onClick="HidePopup(\''.$njtid2.'\');return false;">';
                $html .= '&nbsp;</a>';
            }
        } else {
            $html .=  '<span class="jt-empty-icon">&nbsp;</span>';
        }

        if ($event->indCitation == true) {
            $sjtid1 = 'jt1srcpev'.$i.$this->person->id.$this->partner->id;
            $sjtid2 = 'jt2srcpev'.$i.$this->person->id.$this->partner->id;
            if ($this->lists['technology'] != 'b') {
                $html .=  '<a href="#" id="'.$sjtid1.'" class="jt-sources-icon"';

                if (($this->lists['technology'] == 'j') or ($event->indAltSource == true)) {
                    $html .= 'onMouseOver="ShowPopup(\''.$sjtid1.'\', \''.$sjtid2.'\', 0, 0);return false;"';
                } else {
                    $link =  Route::_(
                        'index.php?format=raw&option=com_joaktree'
                        .'&view=joaktree&layout=_detailsources'
                        .'&tmpl=component&type=relation&subtype=revent&orderNumber='.$event->orderNumber
                        .'&relationId='.$this->partner->id
                        .'&personId='.$this->person->app_id.'!'.$this->person->id
                        .'&treeId='.$this->person->tree_id
                    );
                    $html .= 'onMouseOver="ShowAjaxPopup(\''.$sjtid1.'\', \''.$sjtid2.'\', \''.$link.'\');return false;"';
                }
                $html .= 'onClick="HidePopup(\''.$sjtid2.'\');return false;">';
                $html .= '&nbsp;</a>';
            }
        } else {
            $html .= '<span class="jt-empty-icon">&nbsp;</span>';
        }

        $html .= '</span>'; // end of jt-iconlabel

        // show actual value
        $html .= '<span class="jt-high-row jt-valuelabel">';

        if ($event->value != null) {
            $html .= $event->value.'&nbsp;';
        }

        if (($event->value != null) and (($event->eventDate != null) or ($event->location != null))) {
            $html .= '(&nbsp;';
        }

        $html .= JoaktreeHelper::displayDate($event->eventDate).'&nbsp;';

        if ($event->location != null) {
            $html .= Text::_('JT_IN') . '&nbsp;' . $event->location . '&nbsp;';
        }

        if (($event->value != null) and (($event->eventDate != null) or ($event->location != null))) {
            $html .= ')&nbsp;';
        }

        $html .= '</span>'; // end of jt-valuelabel
    }
    $html .= '</div>';

    if ($event->indNote == true) {
        $njtid1 = 'jt1notpev'.$i.$this->person->id.$this->partner->id;
        $njtid2 = 'jt2notpev'.$i.$this->person->id.$this->partner->id;
        if ($this->lists['technology'] != 'b') {
            if (($this->lists['technology'] == 'j') or ($event->indAltNote == true)) {
                $html .= '<div id="'.$njtid2.'" class="jt-hide jt-popup" style="position: absolute; z-index: 50;">';
            } else {
                $html .= '<div id="'.$njtid2.'" class="jt-ajax jt-popup" style="position: absolute; z-index: 50;">';
            }

            if ($event->indAltNote == true) {
                $html .= '<div class="jt-source">'.Text::_('JT_ALTERNATIVE').'</div>';
            } elseif (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
                $html .=  '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_NOTES').'</div>';
            } else {
                // prepare for template
                $notes[ 'type' ]     	= 'relation';
                $notes[ 'subtype' ]  	= 'revent';
                $notes[ 'orderNumber' ]	= $event->orderNumber;
                $notes[ 'relation_id' ]	= $this->partner->id;
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

    if ($event->indCitation == true) {
        $sjtid1 = 'jt1srcpev'.$i.$this->person->id.$this->partner->id;
        $sjtid2 = 'jt2srcpev'.$i.$this->person->id.$this->partner->id;
        if ($this->lists['technology'] != 'b') {
            if (($this->lists['technology'] == 'j') or ($event->indAltSource == true)) {
                $html .= '<div id="'.$sjtid2.'" class="jt-hide jt-popup" style="position: absolute; z-index: 50;">';
            } else {
                $html .= '<div id="'.$sjtid2.'" class="jt-ajax jt-popup" style="position: absolute; z-index: 50;">';
            }

            if ($event->indAltSource == true) {
                $html .= '<div class="jt-source">'.Text::_('JT_ALTERNATIVE').'</div>';
            } elseif (($this->lists['technology'] != 'b') and ($this->lists['technology'] != 'j')) {
                $html .= '<div class="jt-ajax-loader">'.Text::_('JT_LOADING_SOURCES').'</div>';
            } else {
                // prepare for template
                $sources[ 'type' ]     		= 'relation';
                $sources[ 'subtype' ]  		= 'revent';
                $sources[ 'orderNumber' ]	= $event->orderNumber;
                $sources[ 'relation_id' ]	= $this->partner->id;
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

if (count($events) > 0) {
    $html .= '</div>';
}

echo $html;
?>

