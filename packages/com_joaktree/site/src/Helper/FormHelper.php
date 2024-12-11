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

namespace Joaktree\Component\Joaktree\Site\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

class FormHelper extends \StdClass
{
    public static function getNameEventRow($indPHP, $type, &$form, $item, $object, $appId = null, $relationId = null)
    {
        $html = array();
        $rowMainId      = ($indPHP) ? 'rN_'.$object->orderNumber : 'rN_\'+orderNumber+\'';
        $rowMainRefId	= ($indPHP) ? $rowMainId.'_ref' : 'rN_\'+orderNumber+\'_ref';
        $rowMainNotId	= ($indPHP) ? $rowMainId.'_not' : 'rN_\'+orderNumber+\'_not';
        $rowMainEDId	= ($indPHP) ? $object->orderNumber : '\'+orderNumber+\'';
        $tabRefId       = ($indPHP) ? 'tbref_'.$object->orderNumber : 'tbref_\'+orderNumber+\'';
        $tabNotId  		= ($indPHP) ? 'tbnot_'.$object->orderNumber : 'tbnot_\'+orderNumber+\'';
        $rowclass		= 'jt-table-entry4';

        switch ($type) {
            case "personName": 		$formRecord = 'person.names';
                break;
            case "personEvent":
            case "relationEvent":
            default:				$formRecord = 'person.events';
                break;
        }

        //<!-- Row for one existing additional name / person event / relation event -->
        $html[] = '<tr id="'.$rowMainId.'" class="'.$rowclass.'" >';
        $html[] = $form->getInput('orderNumber', $formRecord, (($indPHP) ? $object->orderNumber : '\'+orderNumber+\''));
        $html[] = $form->getInput('status', $formRecord, $rowMainId.'!'.(($indPHP) ? 'loaded' : 'new'));

        if ($type == 'personName') {
            $html[] = '<td>'.$form->getInput('code', $formRecord, (($indPHP) ? $object->code : null)).'</td>';
            $html[] = '<td>'.$form->getInput('value', $formRecord, (
                ($indPHP)
                                                                    ? htmlspecialchars_decode($object->value, ENT_QUOTES)
                                                                    : null
            )).'</td>';
        } else {
            $html[] = '<td>';
            if ($type == 'relationEvent') {
                $html[] = $form->getLabel('relcode', $formRecord);
                $html[] = $form->getInput('relcode', $formRecord, (($indPHP) ? $object->code : null));
            } else {
                $html[] = $form->getLabel('code', $formRecord);
                $html[] = $form->getInput('code', $formRecord, (($indPHP) ? $object->code : null));
            }
            $html[] = $form->getLabel('type', $formRecord);
            $html[] = $form->getInput('type', $formRecord, (
                ($indPHP && isset($object->type))
                                                            ? htmlspecialchars_decode($object->type, ENT_QUOTES)
                                                            : null
            ));

            $htmlEventdate = self::getEventDateHTML((
                ($indPHP && isset($object->eventDate))
                                                    ? htmlspecialchars_decode($object->eventDate, ENT_QUOTES)
                                                    : null
            ), $rowMainEDId, $formRecord, $form);
            if (is_array($htmlEventdate)) {
                $html = array_merge($html, $htmlEventdate);
            }

            $html[] = $form->getLabel('location', $formRecord);
            $html[] = $form->getInput('location', $formRecord, (
                ($indPHP && isset($object->location))
                                                                ? htmlspecialchars_decode($object->location, ENT_QUOTES)
                                                                : null
            ));
            $html[] = $form->getLabel('value', $formRecord);
            $html[] = $form->getInput('value', $formRecord, (
                ($indPHP && isset($object->value))
                                                            ? htmlspecialchars_decode($object->value, ENT_QUOTES)
                                                            : null
            ));
            $html[] = '</td>';
        }

        $html[] = '<td>';
        $html[] = '   <span class="jt-edit">';
        $html[] = '      <a	href="#" ';
        if ($indPHP) {
            $html[] = '		onclick="return jtrefnot(\''.$rowMainNotId.'\');"';
        } else {
            $html[] = '		onclick="return jtrefnot(\\\''.$rowMainNotId.'\\\');"';
        }
        $html[] = '         title="'.Text::_('JT_NOTES').'" >';
        $html[] =           Text::_('JT_NOTES');
        $html[] = '      </a>';
        $html[] = '   </span>';
        $html[] = '   &nbsp;|&nbsp;';
        $html[] = '   <span class="jt-edit">';
        $html[] = '      <a	href="#" ';
        if ($indPHP) {
            $html[] = '		onclick="return jtrefnot(\''.$rowMainRefId.'\');"';
        } else {
            $html[] = '		onclick="return jtrefnot(\\\''.$rowMainRefId.'\\\');"';
        }
        $html[] = '         title="'.Text::_('JT_REFERENCES').'" >';
        $html[] =           Text::_('JT_REFERENCES');
        $html[] = '      </a>';
        $html[] = '   </span>';
        $html[] = '   &nbsp;|&nbsp;';
        $html[] = '   <span class="jt-edit">';
        $html[] = '      <a	href="#"';
        if ($indPHP) {
            $html[] = '      	onclick="remove_row(\''.$rowMainId.'\'); return false;"';
        } else {
            $html[] = '      	onclick="remove_row(\\\''.$rowMainId.'\\\'); return false;"';
        }
        $html[] = '      	title="'.Text::_('JT_DELETE_DESC').'"';
        $html[] = '      >';
        $html[] = 	     	Text::_('JT_DELETE');
        $html[] = '      </a>';
        $html[] = '   </span>';
        $html[] = '</td>';
        $html[] = '</tr><!-- end row -->';
        //<!-- End: Row for one existing additional name / person event / relation event -->

        //<!-- Row with references for one additional name / person event / relation event -->
        $html_ref = self::getReferenceBlock(
            $indPHP,
            $rowMainRefId,
            $tabRefId,
            $rowMainId,
            $appId,
            $type,
            (($indPHP) ? $object->orderNumber : '\'+orderNumber+\''),
            $form,
            $item,
            $relationId
        );
        $html = array_merge($html, $html_ref);
        //<!-- End: Row with references for one additional name / person event / relation event -->

        //<!-- Row with notes for one additional name / person event / relation event -->
        $html_ref = self::getNoteBlock(
            $indPHP,
            $rowMainNotId,
            $tabNotId,
            $rowMainId,
            $appId,
            $type,
            (($indPHP) ? $object->orderNumber : '\'+orderNumber+\''),
            $form,
            $item,
            $relationId
        );
        $html = array_merge($html, $html_ref);
        //<!-- End: Row with notes for one additional name / person event / relation event -->

        return implode("\n", $html);
    }


    public static function getRelationRow($object, $type, &$form)
    {
        $html = array();
        $rowMainId      = 're_'.$object->orderNumber;
        $rowclass		= 'jt-table-entry4';

        $html[] = '<tr id="'.$rowMainId.'" class="'.$rowclass.'" >';
        $html[] = $form->getInput('id', 'person.relations', $object->id);
        $html[] = $form->getInput('status', 'person.relations', $rowMainId.'!'.'loaded');
        $html[] = $form->getInput('familyid', 'person.relations', $object->family_id);

        if ($type == 'children') {
            $html[] = $form->getInput('parentid', 'person.relations', $object->secondParent_id);
        }

        if ($type == 'parents') {
            $html[] = '<td>'.(($object->sex == 'M') ? Text::_('JT_FATHER') : (($object->sex == 'F') ? Text::_('JT_MOTHER') : null)).'</td>';
        } elseif ($type == 'partners') {
            $html[] = '<td>'.(($object->sex == 'M') ? Text::_('JT_HUSBAND') : (($object->sex == 'F') ? Text::_('JT_WIFE') : Text::_('JT_PARTNER'))).'</td>';
        }
        $html[] = '<td>'.$object->fullName.'</td>';
        $html[] = '<td>'.$object->birthDate.'</td>';
        $html[] = '<td>'.$object->deathDate.'</td>';
        if (($type == 'children') || ($type == 'parents')) {
            $html[] = '<td>'.$form->getInput('relationtype', 'person.relations', $object->relationtype).'</td>';
        }
        if ($type == 'partners') {
            $html[] = '<td>'.$form->getInput('partnertype', 'person.relations', $object->relationtype).'</td>';
        }
        $html[] = '<td>';
        $html[] = '   <span class="jt-edit">';
        $html[] = '      <a	href="#"';
        $html[] = '      	onclick="move_row(\''.$rowMainId.'\', \'up\'); return false;"';
        $html[] = '      	title="'.Text::_('JT_UP_DESC').'"';
        $html[] = '      >';
        $html[] = 	     	Text::_('JT_UP');
        $html[] = '      </a>';
        $html[] = '      &nbsp;|&nbsp;';
        $html[] = '      <a	href="#"';
        $html[] = '      	onclick="move_row(\''.$rowMainId.'\', \'down\'); return false;"';
        $html[] = '      	title="'.Text::_('JT_DOWN_DESC').'"';
        $html[] = '      >';
        $html[] = 	     	Text::_('JT_DOWN');
        $html[] = '      </a>';
        $html[] = '      &nbsp;|&nbsp;';
        $html[] = '      <a	href="#"';
        $html[] = '      	onclick="remove_row(\''.$rowMainId.'\'); return false;"';
        $html[] = '      	title="'.Text::_('JT_DELETE_DESC').'"';
        $html[] = '      >';
        $html[] = 	     	Text::_('JT_DELETE');
        $html[] = '      </a>';
        $html[] = '   </span>';
        $html[] = '</td>';
        $html[] = '</tr><!-- end row -->';

        return implode("\n", $html);
    }

    public static function getPictureRow(&$form, $picture, $docsFromGedcom)
    {
        $html = array();
        $rowMainId      = 'rPic_'.$picture->orderNumber;
        $rowclass		= 'jt-table-entry4';

        $html[] = '<tr id="'.$rowMainId.'" class="'.$rowclass.'" >';
        $html[] = $form->getInput('id', 'person.media', $picture->id);
        $html[] = $form->getInput('status', 'person.media', $rowMainId.'!loaded');

        // retrieve size of picture
        if (@is_file($picture->file)) {
            $imagedata   = GetImageSize($picture->file);
            $imageWidth  = $imagedata[0];
            $imageHeight = $imagedata[1];
            $maxpixels	 = ($imageWidth > $imageHeight) ? $imageWidth : $imageHeight;
            $factor		 = ($maxpixels > 300) ? 300 / $maxpixels : 1;
            $showWidth 	 = (int) ($imageWidth * $factor);  //(100/$imageWidth) * $imageWidth * $factor;
            $showHeigth  = (int) ($imageHeight * $factor); //(100/$imageWidth) * $imageHeight * $factor;
            $html[] = '<td><img src="'.$picture->file.'" height="'.$showHeigth.'" width="'. $showWidth.'" /></td>';
        } else {
            $html[] = '<td>'.$picture->file.'</td>';
        }
        $html[] = '<td>'.$picture->title.'</td>';

        // show the file + path
        $html[] = '<td>'.wordwrap($picture->file, 30, "<br />\n", true).'</td>';

        // Actions
        if ($docsFromGedcom) {
            $html[] = '<td>';
            $html[] = '   <span class="jt-edit">';
            $html[] = '      <a	href="#"';
            $html[] = '      	onclick="document.getElementById(\'picture\').value=\''
                                .base64_encode(json_encode($picture))
                                .'\';jtsubmitbutton(\'edit\');"';
            $html[] = '      	title="'.Text::_('JT_EDIT_DESC').'"';
            $html[] = '      >';
            $html[] = 	     	Text::_('JT_EDIT');
            $html[] = '      </a>';
            $html[] = '   </span>&nbsp;|&nbsp;';
            $html[] = '   <span class="jt-edit">';
            $html[] = '      <a	href="#"';
            $html[] = '      	onclick="remove_row(\''.$rowMainId.'\'); return false;"';
            $html[] = '      	title="'.Text::_('JT_DELETE_DESC').'"';
            $html[] = '      >';
            $html[] = 	     	Text::_('JT_DELETE');
            $html[] = '      </a>';
            $html[] = '   </span>';
            $html[] = '</td>';
        }

        $html[] = '</tr><!-- end row -->';

        return implode("\n", $html);
    }

    private static function getReferenceBlock(
        $indPHP,
        $rowMainRefId,
        $tabRefId,
        $rowId,
        $appId,
        $type,
        $orderNumber,
        &$form,
        &$item,
        $relationId
    ) {
        $html = array();

        $html[] = '<tr id="'.$rowMainRefId.'" class="jt-edit-2 jt-table-entry5" >';
        $html[] = '<td colspan="3">';
        $html[] = '<a href="#" onclick="return jtrefnot();" class="jt-btn-close"></a>';
        //        <!-- Additional name-references -->
        $html[] = '<table style="margin: 0;">';
        //			  <!-- header for additional name-references -->
        $html[] = '   <thead>';
        $html[] = '   <tr>';
        $html[] = '      <th class="jt-content-th">'.Text::_('JT_REFERENCES').'</th>';
        $html[] = '      <th class="jt-content-th">'.Text::_('JT_ACTIONS').'</th>';
        $html[] = '   </tr>';
        $html[] = '   </thead>';
        //			  <!-- header for references -->
        //			  <!-- table body for references -->
        $html[] = '   <tbody id="'.$tabRefId.'">';
        //				 <!-- Add row for new reference -->
        $html[] = '      <tr>';
        $html[] = '      <td style="padding: 2px 5px;">&nbsp;</td>';
        $html[] = '      <td style="padding: 2px 5px;">';
        $html[] = '         <span class="jt-edit">';
        $html[] = '            <a href="#" ';
        if ($indPHP) {
            $html[] = '        onclick="inject_refrow(\''.$tabRefId
                                    .'\', \''.$rowId
                                    .'\', \''.$appId
                                    .'\', \''.$type
                                    .'\', \''.$orderNumber
                                    .'\'); return false;"';
        } else {
            $html[] = '        onclick="inject_refrow(\\\''.$tabRefId
                                    .'\\\', \\\''.$rowId
                                    .'\\\', \\\''.$appId
                                    .'\\\', \\\''.$type
                                    .'\\\', \\\'\'+orderNumber+\''
                                    .'\\\'); return false;"';
        }
        $html[] = '               title="'.Text::_('JTADD_DESC').'" >';
        $html[] =                 Text::_('JTADD');
        $html[] = '            </a>';
        $html[] = '         </span>';
        $html[] = '      </td>';
        $html[] = '      </tr>';
        //				 <!-- End: Add row for new reference -->
        if ($indPHP) {
            //		     <!-- List of existing references -->
            switch ($type) {
                case "personName":		$sourceType = 'name';
                    break;
                case "personNote":		$sourceType = 'note';
                    break;
                case "personEvent":		$sourceType = 'pevent';
                    break;
                case "relationEvent":	$sourceType = 'revent';
                    break;
                default:		  		$sourceType = 'pevent';
                    break;
            }
            $refs = $item->getSources($sourceType, $orderNumber, $relationId);
            if (count($refs)) {
                foreach ($refs as $ref) {
                    $rowRefId	= $rowId.'_r_'.$ref->orderNumber;
                    $html[] = '         <tr id="'.$rowRefId.'" >';
                    $html[] = 				self::getReferenceRow($indPHP, $form, $ref, $rowRefId);
                    $html[] = '         </tr>';
                }
            }
            //			<!-- End: List of existing references -->
        }
        $html[] = '   </tbody>';
        //			  <!-- End: table body for references -->
        $html[] = '</table>';
        //		  <!-- End: References -->
        $html[] = '</td>';
        $html[] = '</tr><!-- end row -->';

        return $html;
    }


    public static function getReferenceRow($indPHP, &$form, $ref, $rowRefId = null, $appId = null)
    {
        $html = array();

        // setup counter
        if ($indPHP) {
            $form->setValue('counter', null, $ref->orderNumber);
            $html[] = $form->getInput('counter', null, $ref->orderNumber);

        }

        $html[] = $form->getInput('objectType', 'person.references', (($indPHP) ? $ref->objectType : '\'+obj_type+\''));
        $html[] = $form->getInput('objectOrderNumber', 'person.references', (($indPHP) ? $ref->objectOrderNumber : '\'+obj_number+\''));
        $html[] = $form->getInput('orderNumber', 'person.references', (($indPHP) ? $ref->orderNumber : '\'+orderNumber+\''));
        $html[] = $form->getInput('status', 'person.references', (($indPHP) ? $rowRefId : '\'+rowref+\'').'!'.(($indPHP) ? 'loaded' : 'new'));
        $html[] = '<td>';
        $html[] = '   <ul class="joaktreeformlist">';
        $html[] = '      <li>';
        $html[] =           $form->getLabel('app_source_id', 'person.references');
        $html[] = 			$form->getInput('app_source_id', 'person.references', (($indPHP) ? $ref->app_id.'!'.$ref->source_id : $appId));
        $html[] = '      </li>';
        $html[] = '      <li>';
        $html[] =           $form->getLabel('page', 'person.references');
        $html[] = 			$form->getInput('page', 'person.references', (
            ($indPHP && isset($ref->page))
                                                                            ? htmlspecialchars_decode($ref->page, ENT_QUOTES)
                                                                            : null
        ));
        $html[] = '      </li>';
        $html[] = '      <li>';
        $html[] =           $form->getLabel('quotation', 'person.references');
        $html[] = 			$form->getInput('quotation', 'person.references', (
            ($indPHP && isset($ref->quotation))
                                                                                ? htmlspecialchars_decode($ref->quotation, ENT_QUOTES)
                                                                                : null
        ));
        $html[] = '      </li>';
        $html[] = '      <li>';
        $html[] =           $form->getLabel('note', 'person.references');
        $html[] = 			$form->getInput('note', 'person.references', (
            ($indPHP && isset($ref->note))
                                                                            ? htmlspecialchars_decode($ref->note, ENT_QUOTES)
                                                                            : null
        ));
        $html[] = '      </li>';
        $html[] = '      <li>';
        $html[] =           $form->getLabel('dataQuality', 'person.references');
        $html[] = 			$form->getInput('dataQuality', 'person.references', (($indPHP) ? $ref->dataQuality : null));
        $html[] = '      </li>';
        $html[] = '   </ul>';
        $html[] = '</td>';
        $html[] = '<td>';
        $html[] = '   <span class="jt-edit">';
        $html[] = '      <a	href="#"';
        $html[] = '      	onclick="remove_row(\''.(($indPHP) ? $rowRefId : '\\\'+rownot+\'\\').'\'); return false;"';
        $html[] = '      	title="'.Text::_('JT_DELETE_DESC').'"';
        $html[] = '      >';
        $html[] = 	     	Text::_('JT_DELETE');
        $html[] = '      </a>';
        $html[] = '   </span>';
        $html[] = '</td>';

        return implode("\n", $html);
    }

    private static function getNoteBlock(
        $indPHP,
        $rowMainNotId,
        $tabNotId,
        $rowId,
        $appId,
        $type,
        $orderNumber,
        &$form,
        &$item,
        $relationId
    ) {
        $html = array();

        $html[] = '<tr id="'.$rowMainNotId.'" class="jt-edit-2 jt-table-entry5" >';
        $html[] = '<td colspan="3">';
        $html[] = '<a href="#" onclick="return jtrefnot();" class="jt-btn-close"></a>';
        //		  <!-- notes -->
        $html[] = '<table style="margin: 0;">';
        //		      <!-- header for notes -->
        $html[] = '   <thead>';
        $html[] = '   <tr>';
        $html[] = '      <th class="jt-content-th">'.Text::_('JT_NOTES').'</th>';
        $html[] = '      <th class="jt-content-th">'.Text::_('JT_ACTIONS').'</th>';
        $html[] = '   </tr>';
        $html[] = '   </thead>';
        //			  <!-- header for notes -->
        //			  <!-- table body for notes -->
        $html[] = '   <tbody id="'.$tabNotId.'">';
        //				 <!-- Add row for new note -->
        $html[] = '      <tr>';
        $html[] = '      <td style="padding: 2px 5px;">&nbsp;</td>';
        $html[] = '      <td style="padding: 2px 5px;">';
        $html[] = '         <span class="jt-edit">';
        $html[] = '            <a href="#"';
        if ($indPHP) {
            $html[] = '        onclick="inject_notrow(\''.$tabNotId
                                .'\', \''.$rowId
                                .'\', \''.$appId
                                .'\', \'personName'
                                .'\', \''.$orderNumber
                                .'\'); return false;"';
        } else {
            $html[] = '        onclick="inject_notrow(\\\''.$tabNotId
                                    .'\\\', \\\''.$rowId
                                    .'\\\', \\\''.$appId
                                    .'\\\', \\\'personName'
                                    .'\\\', \\\'\'+orderNumber+\''
                                    .'\\\'); return false;"';
        }
        $html[] = '               title="'.Text::_('JTADD_DESC').'" >';
        $html[] =                 Text::_('JTADD');
        $html[] = '            </a>';
        $html[] = '         </span>';
        $html[] = '      </td>';
        $html[] = '      </tr>';
        //				 <!-- End: Add row for new note -->
        if ($indPHP) {
            //		     <!-- List of existing notes -->
            switch ($type) {
                case "personName":		$sourceType = 'name';
                    break;
                case "personEvent":		$sourceType = 'pevent';
                    break;
                case "relationEvent":	$sourceType = 'revent';
                    break;
                default:		  		$sourceType = 'pevent';
                    break;
            }

            $nots = $item->getNotes($sourceType, $orderNumber, $relationId);
            if (count($nots)) {
                foreach ($nots as $not) {
                    $rowNotId	= $rowId.'_n_'.$not->orderNumber;
                    $html[] 	= self::getNoteRow($indPHP, false, $form, $not, $rowNotId, null, $item);
                }
            }
            //			<!-- End: List of existing notes -->
        }
        $html[] = '   </tbody>';
        //			  <!-- End: table body for notes -->
        $html[] = '</table>';
        //		  <!-- End: Notes -->
        $html[] = '</td>';
        $html[] = '</tr><!-- end row -->';

        return $html;
    }

    public static function getNoteRow($indPHP, $indRef, &$form, $not, $rowNotId = null, $appId = null, $item = null)
    {
        static $_eol;

        if (!isset($_eol)) {
            $document	= Factory::getApplication()->getDocument();
            $_eol 		= $document->_getLineEnd();
        }

        $html = array();
        $rowNoteRefId	= ($indPHP) ? $rowNotId.'_ref' : 'rN_\'+orderNumber+\'_ref';

        if ($indPHP) {
            $html[] = '<tr id="'.$rowNotId.'" class="jt-table-entry4">';
            $noteText = htmlspecialchars_decode(str_replace("&#10;&#13;", $_eol, $not->text), ENT_QUOTES);
        } else {
            $html[] = '<tr id="\'+rownot+\'" class="jt-table-entry3">';
            $noteText = null;
        }

        $html[] = $form->getInput('note_id', 'person.notes', (($indPHP) ? $not->note_id : null));
        $html[] = $form->getInput('objectOrderNumber', 'person.notes', (($indPHP) ? $not->objectOrderNumber : '\'+obj_number+\''));
        $html[] = $form->getInput('orderNumber', 'person.notes', (($indPHP) ? $not->orderNumber : '\'+orderNumber+\''));
        $html[] = $form->getInput('status', 'person.notes', (($indPHP) ? $rowNotId : '\'+rownot+\'').'!'.(($indPHP) ? 'loaded' : 'new'));
        $html[] = '<td>';
        $html[] = '   <ul class="joaktreeformlist">';
        $html[] = '      <li>';
        $html[] = 			$form->getInput('text', 'person.notes', $noteText);
        $html[] = '      </li>';
        $html[] = '   </ul>';
        $html[] = '</td>';
        $html[] = '<td>';

        if ($indRef) {
            $html[] = '   <span class="jt-edit">';
            $html[] = '      <a	href="#" ';

            if ($indPHP) {
                $html[] = '		onclick="return jtrefnot(\''.$rowNoteRefId.'\');"';
            } else {
                $html[] = '		onclick="return jtrefnot(\\\''.$rowNoteRefId.'\\\');"';
            }

            $html[] = '         title="'.Text::_('JT_REFERENCES').'" >';
            $html[] =           Text::_('JT_REFERENCES');
            $html[] = '      </a>';
            $html[] = '   </span>';
            $html[] = '   &nbsp;|&nbsp;';
        }

        $html[] = '   <span class="jt-edit">';
        $html[] = '      <a	href="#"';
        $html[] = '      	onclick="remove_row(\''.(($indPHP) ? $rowNotId : '\\\'+rownot+\'\\').'\'); return false;"';
        $html[] = '      	title="'.Text::_('JT_DELETE_DESC').'"';
        $html[] = '      >';
        $html[] = 	     	Text::_('JT_DELETE');
        $html[] = '      </a>';
        $html[] = '   </span>';
        $html[] = '</td>';
        $html[] = '</tr><!-- end row -->';

        if ($indRef) {
            $tabRefId       = ($indPHP) ? 'tbref_'.$not->orderNumber : 'tbref_\'+orderNumber+\'';

            //<!-- Row with references for one additional name -->
            $html_ref = self::getReferenceBlock(
                $indPHP,
                $rowNoteRefId,
                $tabRefId,
                $rowNotId,
                $appId,
                'personNote',
                (($indPHP) ? $not->orderNumber : '\'+orderNumber+\''),
                $form,
                $item,
                null
            );
            $html = array_merge($html, $html_ref);
            //<!-- End: Row with references for one additional name -->
        }

        return implode("\n", $html);
    }

    public static function getNameEventRowScript($type, &$form, $appId)
    {
        $tmp   = self::getNameEventRow(false, $type, $form, null, null, $appId, null);
        $rows = explode("\n", $tmp);

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtfnameeventrow.js');
        } else {
            $wa->registerAndUseScript('jtfnameeventrow', $base.'js/jtfnameeventrow.js');
        }
        Factory::getApplication()->getDocument()->addScriptOptions(
            'jtfnameeventrow',
            array(  'rows' => $rows)
        );
        return "";

    }

    public static function getReferenceRowScript(&$form, $appId)
    {
        $base	= 'media/com_joaktree/';
        $rows   = self::getReferenceRow(false, $form, null, null, $appId);
        $rows = explode("\n", $rows);

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtfrefrow.js');
        } else {
            $wa->registerAndUseScript('jtfrefrow', $base.'js/jtfrefrow.js');
        }
        Factory::getApplication()->getDocument()->addScriptOptions(
            'jtfrefrow',
            array(  'rows' => $rows,'sef' => Factory::getApplication()->get('sef') )
        );
        return "";
    }

    public static function getNoteRowScript($indRef, &$form, $appId)
    {
        $base	= 'media/com_joaktree/';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtfnoterow.js');
        } else {
            $wa->registerAndUseScript('jtfnoterow', $base.'js/jtfnoterow.js');
        }
        $tmp   = self::getNoteRow(false, $indRef, $form, null, null, $appId, null);
        $rows = explode("\n", $tmp);

        Factory::getApplication()->getDocument()->addScriptOptions(
            'jtfnoterow',
            array(  'rows' => $rows)
        );
        return "";

    }

    public static function getGeneralRowScript()
    {
        $base	= 'media/com_joaktree/';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtfgrefrow.js');
        } else {
            $wa->registerAndUseScript('jtformgenperson', $base.'js/jtfgrefrow.js');
        }
        Factory::getApplication()->getDocument()->addScriptOptions(
            'jtfgrefrow',
            array(  'LOWERROW_MESSAGE' => Text::_('JT_LOWERROW_MESSAGE'),
            'JT_UPPERROW_MESSAGE' => Text::_('JT_UPPERROW_MESSAGE') )
        );
        return "";
    }

    public static function getSubmitScript($personName)
    {
        $base	= 'media/com_joaktree/';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtfsubmit.js');
        } else {
            $wa->registerAndUseScript('jtfsubmit', $base.'js/jtfsubmit.js');
        }
        Factory::getApplication()->getDocument()->addScriptOptions(
            'jtfsubmit',
            array(  'message' => Text::sprintf('JT_CONFIRMDELETE_PERSON', $personName) )
        );
        return "";
    }

    public static function getButtons(
        $counter,
        $but = array( 'save' => true, 'cancel' => true,
                                                  'check' => false, 'done' => false,
                                                  'add' => false),
        $indParent1 = false
    ) {
        $html = array();
        if ($counter == 1) {
            $html[] = '<div class="jt-buttonbar" style="margin-left: 10px;">';
        } else {
            $html[] = '<div class="jt-buttonbar" style="margin-left: 10px; margin-top: 10px;">';
        }

        if (($but['save']) && (!$but['check'])) {
            $html[] = '	<a 	href="#" ';
            $html[] = '		id="save'.$counter.'"';
            $html[] = '		class="jt-button-closed jt-buttonlabel"';
            $html[] = '		title="'.Text::_('JSAVE').'" ';
            $html[] = '		onclick="jtsubmitbutton(\'save\');"';
            $html[] = '	>';
            $html[] =       Text::_('JSAVE');
            $html[] = '	</a>';
            $html[] = '&nbsp;';
        }

        if (($but['save']) && ($but['check'])) {
            $link  = 'index.php?option=com_joaktree'
                     .'&amp;view=list'
                     .'&amp;layout=check'
                     .'&amp;tmpl=component'
                     .'&amp;treeId='.JoaktreeHelper::getTreeId()
                     .'&amp;action='.(($indParent1) ? 'saveparent1' : 'save');

            HTMLHelper::_('bootstrap.modal', '.modal');
            $html[] = '<a class="jt-button-closed jt-buttonlabel" data-bs-toggle="modal" data-bs-target="#save'.$counter.'">'.Text::_('JSAVE').'</a>';
            $html[] = ' <div class="modal fade modal-xl"  id="save'.$counter.'" tabindex="-1" aria-labelledby="newstatus" aria-hidden="true">
            <div class="modal-dialog h-75">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body h-100">
                        <iframe id="iframeModalWindowsave'.$counter.'" height="100%" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
            </div>';
        }

        if ($but['done']) {
            $html[] = '	<a 	href="#" ';
            $html[] = '		id="done'.$counter.'"';
            $html[] = '		class="jt-button-closed jt-buttonlabel" ';
            $html[] = '		title="'.Text::_('JT_DONE').'" ';
            $html[] = '		onclick="jtsubmitbutton(\'cancel\');"';
            $html[] = '	>';
            $html[] =       Text::_('JT_DONE');
            $html[] = '	</a>';
            $html[] = '	&nbsp;';
        }

        if ($but['add']) {
            $html[] = '	<a 	href="#" ';
            $html[] = '		id="add'.$counter.'"';
            $html[] = '		class="jt-button-closed jt-buttonlabel" ';
            $html[] = '		title="'.Text::_('JTADD_DESC').'" ';
            $html[] = '		onclick="document.getElementById(\'mediaForm\').object.value=\'media\'; jtsubmitbutton(\'edit\');"';
            $html[] = '	>';
            $html[] =       Text::_('JTADD');
            $html[] = '	</a>';
            $html[] = '	&nbsp;';
        }

        if ($but['cancel']) {
            $html[] = '	<a 	href="#" ';
            $html[] = '		id="cancel'.$counter.'"';
            $html[] = '		class="jt-button-closed jt-buttonlabel" ';
            $html[] = '		title="'.Text::_('JCANCEL').'" ';
            $html[] = '		onclick="jtsubmitbutton(\'cancel\');"';
            $html[] = '	>';
            $html[] =       Text::_('JCANCEL');
            $html[] = '	</a>';
            $html[] = '	&nbsp;';
        }

        if (($but['check']) && ($counter == 1)) {
            $params	= JoaktreeHelper::getJTParams(true);
            $patronym = $params->get('patronym', 0);

            $link1  = 'index.php?option=com_joaktree'
                     .'&amp;view=list'
                     .'&amp;layout=check'
                     .'&amp;tmpl=component'
                     .'&amp;treeId='.JoaktreeHelper::getTreeId()
                     .'&amp;action=select';

            $link2  = 'index.php?option=com_joaktree'
                     .'&amp;view=list'
                     .'&amp;layout=check'
                     .'&amp;tmpl=component'
                     .'&amp;treeId='.JoaktreeHelper::getTreeId()
                     .'&amp;action='.(($indParent1) ? 'saveparent1' : 'save');

            $base	= 'media/com_joaktree/';
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
                Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtfperson.js');
            } else {
                $wa->registerAndUseScript('jtfperson', $base.'js/jtfperson.js');
            }
            Factory::getApplication()->getDocument()->addScriptOptions(
                'jtfperson',
                array(  'link1' => Route::_($link1),'link2' => Route::_($link2),
                        'patronym' => (int)$patronym,'sef' => Factory::getApplication()->get('sef'),
                        'urlclose' => Route::_('index.php?option=com_joaktree&amp;view=close'))
            );

            // Load the modal behavior script.
            HTMLHelper::_('bootstrap.modal', '.modal');
            $html[] = '<span id="cp_label" class="jt-edit-2">';
            $html[] = '<a class="jt-button-closed jt-buttonlabel" data-bs-toggle="modal" data-bs-target="#check'.$counter.'">'.Text::_('JT_CHECK').'</a>';
            $html[] = ' <div class="modal fade modal-xl"  id="check'.$counter.'" tabindex="-1" aria-labelledby="newstatus" aria-hidden="true">
            <div class="modal-dialog h-75">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body h-100">
                        <iframe id="iframeModalWindowcheck'.$counter.'" height="100%" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
            </div>
            ';
            $html[] = '</span>';
            $html[] = '<input type="hidden" id="newstatus" value="unchecked" />';
        }
        $html[] = '</div>';
        $html[] = '<div class="clearfix"></div>';

        return implode("\n", $html);
    }

    private static function getEventDateHTML($eventDateString, $counter, $formRecord, &$form)
    {
        // initialize
        $html = array();
        $ed = array();
        $ed['type'] 		= 'simple';
        $ed['fullString'] 	= $eventDateString;
        $ed['Label1']		= null;
        $ed['Label2']		= null;
        $ed['M1']			= null;
        $ed['D1']			= null;
        $ed['Y1']			= null;
        $ed['M2']			= null;
        $ed['D2']			= null;
        $ed['Y2']			= null;

        // evaluate string
        $elements = [];
        if ($eventDateString) {
            $elements = explode(' ', $eventDateString);
        }
        foreach ($elements as $element) {
            switch ($element) {
                case "ABT":	// continute
                case "ABOUT":	$ed['Label1'] = 'ABT';
                    $ed['type']   = 'extended';
                    break;
                case "BEF": 	// continue
                case "BEFORE": 	$ed['Label1'] = 'BEF';
                    $ed['type']   = 'extended';
                    break;
                case "AFT":	// continute
                case "AFTER":	$ed['Label1'] = 'AFT';
                    $ed['type']   = 'extended';
                    break;
                case "BET":	// continute
                case "BETWEEN":	$ed['Label1'] = 'BET';
                    $ed['type']   = 'extended';
                    break;
                case "FROM":	$ed['Label1'] = 'FROM';
                    $ed['type']   = 'extended';
                    break;
                case "AND":	$ed['Label2'] = 'AND';
                    $ed['type']   = 'extended';
                    break;
                case "TO":	$ed['Label2'] = 'TO';
                    $ed['type']   = 'extended';
                    break;
                case "JAN":	// continue
                case "FEB":	// continue
                case "MAR":	// continue
                case "APR":	// continue
                case "MAY":	// continue
                case "JUN":	// continue
                case "JUL":	// continue
                case "AUG":	// continue
                case "SEP":	// continue
                case "OCT":	// continue
                case "NOV":	// continue
                case "DEC":	if (!isset($ed['M1'])) {
                    $ed['M1'] = $element;
                } else {
                    $ed['M2'] = $element;
                    $ed['type']   = 'extended';
                }
                    break;
                case "":	// empty -> just continue with the next element
                    break;
                default:   // check whether this is a day or a year
                    $tmp = (int) $element;
                    if (($tmp >= 0)
                       and ($tmp <= 31)
                       and ($element == (string) $tmp)
                    ) {
                        // This is a day
                        if (!isset($ed['D1'])) {
                            $ed['D1'] = $element;
                        } else {
                            $ed['D2']	= $element;
                            $ed['type']	= 'extended';
                        }
                    } elseif (($tmp > 900)
                              and ($tmp < 10000)
                              and ($element == (string) $tmp)
                    ) {
                        // This is a year
                        if (!isset($ed['Y1'])) {
                            $ed['Y1'] = $element;
                        } else {
                            $ed['Y2']	= $element;
                            $ed['type']	= 'extended';
                        }
                    } else {
                        $ed['type']	= 'description';
                    }

                    break;
            }

        }

        // setup classes
        switch ($ed['type']) {
            case "simple"		:	$classSimpleExtended	= 'jt-show';
                $classExtended			= 'jt-hide';
                $classDescription		= 'jt-hide';
                break;
            case "extended"		:	$classSimpleExtended	= 'jt-show';
                $classExtended			= 'jt-show';
                $classDescription		= 'jt-hide';
                break;
            case "description"	:	// continue
            default:				$classSimpleExtended	= 'jt-hide';
                $classExtended			= 'jt-hide';
                $classDescription		= 'jt-show';
                break;
        }

        // start with the fields -> all situations
        $html[] = $form->getLabel('eventDateType', $formRecord);
        $html[] = $form->getInput('eventDateType', $formRecord, $ed['type'].'!'.$counter);
        $html[] = '<label for="jform_person_events_ed" id="jform_person_events_ed-lbl">&nbsp;</label>';

        // First line
        // Extended only
        $html[] = '<span id="ed_l1_'.$counter.'" class="'.$classExtended.'">';
        $html[] = $form->getInput('eventDateLabel1', $formRecord, $ed['Label1']);
        $html[] = '</span>';

        // Simple + extended
        $html[] = '<span id="ed_d1_'.$counter.'" class="'.$classSimpleExtended.'">';
        $html[] = $form->getInput('eventDateDay1', $formRecord, $ed['D1']);
        $html[] = $form->getInput('eventDateMonth1', $formRecord, $ed['M1']);
        $html[] = $form->getInput('eventDateYear1', $formRecord, $ed['Y1']);
        $html[] = '</span>';

        // Description only
        $html[] = '<span id="ed_desc_'.$counter.'" class="'.$classDescription.'">';
        $html[] = $form->getInput('eventDate', $formRecord, $ed['fullString']);
        $html[] = '</span>';

        // Second line
        // Extended only
        $html[] = '<span id="ed_d2_'.$counter.'" class="'.$classExtended.'">';
        $html[] = $form->getLabel('eventDateLabel2', $formRecord);
        $html[] = $form->getInput('eventDateLabel2', $formRecord, $ed['Label2']);
        $html[] = $form->getInput('eventDateDay2', $formRecord, $ed['D2']);
        $html[] = $form->getInput('eventDateMonth2', $formRecord, $ed['M2']);
        $html[] = $form->getInput('eventDateYear2', $formRecord, $ed['Y2']);
        $html[] = '</span>';

        return $html;
    }

    public static function checkDisplay($gedcomtype = 'person', $indLiving = null, $code = null)
    {
        // Get the database object.
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getquery(true);
        $levels  = JoaktreeHelper::getUserAccessLevels();
        $indLiving = (empty($indLiving)) ? false : $indLiving;

        $query->select(' count(code) ');
        $query->from(' #__joaktree_display_settings ');
        $query->where(' level = :level');
        $query->where(' published = true ');

        if (!empty($code)) {
            $query->where(' code = :code ');
        } else {
            $query->where(' code NOT IN ('
                                .$db->quote('NAME').', '
                                .$db->quote('NOTE').', '
                                .$db->quote('ENOT').', '
                                .$db->quote('SOUR').', '
                                .$db->quote('ESOU')
                                .') ');
        }

        if ($indLiving == false) {
            $query->where(' access IN '.$levels.' ');
        } else {
            $query->where(' accessLiving IN '.$levels.' ');
        }
        $query->bind(':level',$db->quote($gedcomtype),\Joomla\Database\ParameterType::STRING);
        $query->bind(':code',$db->quote($code),\Joomla\Database\ParameterType::STRING);
        // Set the query and get the result list.
        $db->setquery($query);
        $result = $db->loadResult();
        $count = (int) $result;

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}
