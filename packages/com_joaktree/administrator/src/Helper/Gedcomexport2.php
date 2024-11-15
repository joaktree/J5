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
namespace Joaktree\Component\Joaktree\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\Database\DatabaseInterface;

class Gedcomexport2
{
    protected $procObject;
    protected $objectLines;
    protected $objectType;

    public function __construct($procObject)
    {
        $this->procObject = $procObject;
    }

    private function checkFamily_id()
    {

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' COUNT( jrn.app_id ) ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id = '.(int) $this->procObject->id.' ');
        $query->where(
            ' (  jrn.family_id IS NULL '
                     . ' OR jrn.family_id = '.$db->quote('').' '
                        . ' ) '
        );

        $db->setQuery($query);
        $count = $db->loadResult();
        return ((int) $count > 0) ? true : false;
    }

    private function fixFamily_id()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        // first update all empty family_id's for partner relationships
        $attribs = array();
        $attribs[] = 'person_id_1';
        $attribs[] = 'person_id_2';
        $key = $query->concatenate($attribs, '+');
        unset($attribs);

        $query->update(' #__joaktree_relations ');
        $query->set(' family_id = '.$key.' ');
        $query->where(' app_id = '.(int) $this->procObject->id.' ');
        $query->where(' type = '.$db->quote('partner').' ');
        $query->where(
            ' (  family_id IS NULL '
                     . ' OR family_id = '.$db->quote('').' '
                        . ' ) '
        );

        $db->setQuery($query);
        //$db->query();
        $db->execute();
        $db->transactionCommit();

        // second update all children - for first parent
        $query->clear();
        $query->update(
            ' #__joaktree_relations child '
                      . ' INNER JOIN #__joaktree_relations parents '
                      . ' ON (   parents.app_id      = child.app_id '
                      . '    AND parents.type        = '.$db->quote('partner').' '
                      . '    AND parents.person_id_1 = child.person_id_2 '
                      . '    ) '
                      . ' INNER JOIN #__joaktree_relations p2 '
                      . ' ON (   p2.app_id      = child.app_id '
                      . '    AND p2.person_id_1 = child.person_id_1 '
                      . '    AND p2.type        IN ( '.$db->quote('father').', '.$db->quote('mother').') '
                      . '    AND p2.person_id_2 = parents.person_id_2 '
                      . '    ) '
        );
        $query->set(' child.family_id = parents.family_id ');
        $query->where(' child.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' child.type IN ( '.$db->quote('father').', '.$db->quote('mother').') ');

        $db->setQuery($query);
        //$db->query();
        $db->execute();
        $db->transactionCommit();

        // third update all children - for second parent
        $query->clear();
        $query->update(
            ' #__joaktree_relations child '
                      . ' INNER JOIN #__joaktree_relations parents '
                      . ' ON (   parents.app_id      = child.app_id '
                      . '    AND parents.type        = '.$db->quote('partner').' '
                      . '    AND parents.person_id_2 = child.person_id_2 '
                      . '    ) '
                      . ' INNER JOIN #__joaktree_relations p2 '
                      . ' ON (   p2.app_id      = child.app_id '
                      . '    AND p2.person_id_1 = child.person_id_1 '
                      . '    AND p2.type        IN ( '.$db->quote('father').', '.$db->quote('mother').') '
                      . '    AND p2.person_id_2 = parents.person_id_1 '
                      . '    ) '
        );
        $query->set(' child.family_id = parents.family_id ');
        $query->where(' child.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' child.type IN ( '.$db->quote('father').', '.$db->quote('mother').') ');

        $db->setQuery($query);
        //$db->query();
        $db->execute();
        $db->transactionCommit();

        // fourth update all children in a single parent family
        $attribs = array();
        $attribs[] = 'person_id_2';
        $attribs[] = $db->quote('+');
        $key = $query->concatenate($attribs);
        unset($attribs);

        $query->clear();
        $query->update(' #__joaktree_relations ');
        $query->set(' family_id = '.$key.' ');
        $query->where(' app_id = '.(int) $this->procObject->id.' ');
        $query->where(' type IN ( '.$db->quote('father').', '.$db->quote('mother').') ');
        $query->where(
            ' (  family_id IS NULL '
                     . ' OR family_id = '.$db->quote('').' '
                        . ' ) '
        );

        $db->setQuery($query);
        //$db->query();
        $db->execute();
        $db->transactionCommit();


        return 'Fixed family_ids ';
    }

    private function getPersons($offset = 0, $limit = 0)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        static $concatTxt;

        //prepare concatenated query
        if (empty($concatTxt)) {
            $attribs = array();
            $attribs[] = 'jpn.namePreposition';
            $attribs[] = 'jpn.familyName';
            $concatTxt = $query->concatenate($attribs, ' ');
        }

        $query->select(' jpn.id ');
        $query->select(' jpn.firstName ');
        $query->select(' jpn.patronym ');
        $query->select(' '.$concatTxt.' AS familyName ');
        $query->select(' jpn.prefix ');
        $query->select(' jpn.suffix ');
        $query->select(' jpn.sex ');
        $query->select(' jpn.indHasParent ');
        $query->select(' jpn.indHasPartner ');
        $query->select(' jpn.indHasChild ');
        $query->select(' jpn.indNote ');
        $query->select(' jpn.indCitation ');
        $query->select(' jpn.indIsWitness ');
        $query->from(' #__joaktree_persons  jpn ');
        $query->where(' jpn.app_id = '.(int) $this->procObject->id.' ');
        $query->order(' CONVERT( SUBSTRING(jpn.id FROM 2), SIGNED) ');

        $db->setQuery($query, $offset, $limit);
        $persons = $db->loadAssocList();

        return $persons;
    }

    private function getFamilies($offset = 0, $limit = 0)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' DISTINCT jrn.family_id ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id = '.(int) $this->procObject->id.' ');

        $query->order(' CONVERT( SUBSTRING(jrn.family_id FROM 2), SIGNED) ');

        $db->setQuery($query, $offset, $limit);
        $families = $db->loadAssocList();

        return $families;
    }

    private function getNotes($offset = 0, $limit = 0)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jne.* ');
        $query->from(' #__joaktree_notes  jne ');
        $query->where(' jne.app_id = '.(int) $this->procObject->id.' ');
        $query->order(' CONVERT( SUBSTRING(jne.id FROM 2), SIGNED) ');

        $db->setQuery($query, $offset, $limit);
        $notes = $db->loadAssocList();

        return $notes;
    }

    private function getSources($offset = 0, $limit = 0)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jse.* ');
        $query->from(' #__joaktree_sources  jse ');
        $query->where(' jse.app_id = '.(int) $this->procObject->id.' ');
        $query->order(' CONVERT( SUBSTRING(jse.id FROM 2), SIGNED) ');

        $db->setQuery($query, $offset, $limit);
        $sources = $db->loadAssocList();

        return $sources;
    }

    private function getRepos($offset = 0, $limit = 0)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jry.* ');
        $query->from(' #__joaktree_repositories  jry ');
        $query->where(' jry.app_id = '.(int) $this->procObject->id.' ');
        $query->order(' CONVERT( SUBSTRING(jry.id FROM 2), SIGNED) ');

        $db->setQuery($query, $offset, $limit);
        $repos = $db->loadAssocList();

        return $repos;
    }

    /* =========================================== */

    private function getNames(&$handle, $level, $personId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jne.* ');
        $query->from(' #__joaktree_person_names  jne ');
        $query->where(' jne.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jne.person_id = '.$db->quote($personId).' ');
        $query->order(' jne.orderNumber ');

        $db->setQuery($query);
        $names = $db->loadAssocList();

        if (is_array($names)) {
            foreach ($names as $name) {
                switch ($name['code']) {
                    case "ADPN": $name['code'] = '_ADPN';
                        break;
                    case "AKA":  $name['code'] = '_AKAN';
                        break;
                    case "BIRN": $name['code'] = '_BIRN';
                        break;
                    case "CENN": $name['code'] = '_CENN';
                        break;
                    case "CURN": $name['code'] = '_CURN';
                        break;
                    case "FRKA": $name['code'] = '_FRKA';
                        break;
                    case "HEBN": $name['code'] = '_HEBN';
                        break;
                    case "INDG": $name['code'] = '_INDG';
                        break;
                    case "MARN": $name['code'] = '_MARN';
                        break;
                    case "OTHN": $name['code'] = '_OTHN';
                        break;
                    case "RELN": $name['code'] = '_RELN';
                        break;
                    default:	 // do nothing
                        break;
                }

                fwrite($handle, $this->jt($level." ".$name['code']." ".$name['value']."\r\n"));
                if (!empty($name['eventDate'])) {
                    fwrite($handle, $this->jt(($level + 1)." DATE ".$name['eventDate']."\r\n"));
                }

                if ($name['indCitation']) {
                    $this->getCites($handle, ($level + 1), $personId, 'EMPTY', $name['orderNumber'], $type = 'personName');
                }

                if ($name['indNote']) {
                    $this->getPersonNotes($handle, ($level + 1), $personId, $name['orderNumber'], $type = 'name');
                }
            }
        }
        unset($names);

        return true;
    }

    private function getPersonEvents(&$handle, $level, $personId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jpe.* ');
        $query->from(' #__joaktree_person_events  jpe ');
        $query->where(' jpe.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jpe.person_id = '.$db->quote($personId).' ');
        $query->order(' jpe.orderNumber ');

        $db->setQuery($query);

        $events = $db->loadAssocList();

        if (is_array($events)) {
            foreach ($events as $event) {
                switch ($event['code']) {
                    case "BRTM": $event['code'] = '_BRTM';
                        break;
                    case "YART": $event['code'] = '_YART';
                        break;
                    default:	 // do nothing
                        break;
                }

                if (!empty($event['value'])) {
                    fwrite($handle, $this->jt($level." ".$event['code']." ".$event['value']."\r\n"));
                } else {
                    fwrite($handle, $this->jt($level." ".$event['code']."\r\n"));
                }
                if (!empty($event['type'])) {
                    fwrite($handle, $this->jt(($level + 1)." TYPE ".$event['type']."\r\n"));
                }
                if (!empty($event['eventDate'])) {
                    fwrite($handle, $this->jt(($level + 1)." DATE ".$event['eventDate']."\r\n"));
                }
                if (!empty($event['location'])) {
                    fwrite($handle, $this->jt(($level + 1)." PLAC ".$event['location']."\r\n"));
                }

                if ($event['indCitation']) {
                    $this->getCites($handle, ($level + 1), $personId, 'EMPTY', $event['orderNumber'], $type = 'personEvent');
                }

                if ($event['indNote']) {
                    $this->getPersonNotes($handle, ($level + 1), $personId, $event['orderNumber'], $type = 'event');
                }
            }
        }
        unset($events);

        return true;
    }

    private function getFamilyPartners($familyId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jrn.* ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id    = '.(int) $this->procObject->id.' ');
        $query->where(' jrn.family_id = '.$db->quote($familyId).' ');
        $query->where(' jrn.type      = '.$db->quote('partner').' ');

        $query->select(' jpn1.sex AS sex1 ');
        $query->leftJoin(' #__joaktree_persons  jpn1 '
                         .' ON (jpn1.app_id = jrn.app_id AND jpn1.id = jrn.person_id_1) ');

        $query->select(' jpn2.sex AS sex2');
        $query->leftJoin(' #__joaktree_persons  jpn2 '
                         .' ON (jpn2.app_id = jrn.app_id AND jpn2.id = jrn.person_id_2) ');

        $query->order(' jrn.orderNumber_2 ');

        $db->setQuery($query);
        $partners = $db->loadAssocList();

        if (count($partners) == 0) {
            return false;
        }

        return $partners;
    }

    private function getSingleParent($familyId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' DISTINCT jrn.person_id_2 AS person_id ');
        $query->select(' jrn.type ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id    = '.(int) $this->procObject->id.' ');
        $query->where(' jrn.family_id = '.$db->quote($familyId).' ');
        $query->where(' jrn.type      IN ('.$db->quote('father').', '.$db->quote('mother').') ');

        $db->setQuery($query);
        $parents = $db->loadAssocList();

        if (count($parents) == 0) {
            return false;
        }

        return $parents;
    }

    private function getFamilyChildren(&$handle, $level, $familyId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' DISTINCT jrn.person_id_1 ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id    = '.(int) $this->procObject->id.' ');
        $query->where(' jrn.family_id = '.$db->quote($familyId).' ');
        $query->where(' jrn.type      IN ('.$db->quote('father').', '.$db->quote('mother').') ');
        $query->order(' jrn.orderNumber_2 ');

        $db->setQuery($query);
        $children = $db->loadAssocList();

        if (is_array($children)) {
            foreach ($children as $child) {
                fwrite($handle, $this->jt($level." CHIL @".$child['person_id_1']."@\r\n"));
            }
        }
        unset($children);

        return true;
    }

    private function getRelationEvents(&$handle, $level, $personId_1, $personId_2)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jre.* ');
        $query->from(' #__joaktree_relation_events  jre ');
        $query->where(' jre.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jre.person_id_1 = '.$db->quote($personId_1).' ');
        $query->where(' jre.person_id_2 = '.$db->quote($personId_2).' ');
        $query->order(' jre.orderNumber ');

        $db->setQuery($query);

        $events = $db->loadAssocList();

        if (is_array($events)) {
            foreach ($events as $event) {
                if (!empty($event['value'])) {
                    fwrite($handle, $this->jt($level." ".$event['code']." ".$event['value']."\r\n"));
                } else {
                    fwrite($handle, $this->jt($level." ".$event['code']."\r\n"));
                }
                if (!empty($event['type'])) {
                    fwrite($handle, $this->jt(($level + 1)." TYPE ".$event['type']."\r\n"));
                }
                if (!empty($event['eventDate'])) {
                    fwrite($handle, $this->jt(($level + 1)." DATE ".$event['eventDate']."\r\n"));
                }
                if (!empty($event['location'])) {
                    fwrite($handle, $this->jt(($level + 1)." PLAC ".$event['location']."\r\n"));
                }

                if ($event['indNote']) {
                    $this->getRelationNotes($handle, ($level + 1), $personId_1, $personId_2, $event['orderNumber']);
                }

                if ($event['indCitation']) {
                    $this->getCites($handle, ($level + 1), $personId_1, $personId_2, $event['orderNumber'], $type = 'relationEvent');
                }
            }
        }
        unset($events);

        return true;
    }

    private function getDocuments(&$handle, $level, $personId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jdt.* ');
        $query->from(' #__joaktree_documents  jdt ');
        $query->innerJoin(
            ' #__joaktree_person_documents  jpd '
                         .' ON ( jpd.document_id = jdt.id AND jpd.app_id = jdt.app_id) '
        );
        $query->where(' jpd.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jpd.person_id = '.$db->quote($personId).' ');

        $db->setQuery($query);
        $docs = $db->loadAssocList();

        if (is_array($docs)) {
            foreach ($docs as $doc) {
                fwrite($handle, $this->jt($level." OBJE \r\n"));
                if (!empty($doc['fileformat'])) {
                    fwrite($handle, $this->jt(($level + 1)." FORM ".$doc['fileformat']."\r\n"));
                }
                if (!empty($doc['file'])) {
                    fwrite($handle, $this->jt(($level + 1)." FILE ".$doc['file']."\r\n"));
                }
                if (!empty($doc['title'])) {
                    fwrite($handle, $this->jt(($level + 1)." TITL ".$doc['title']."\r\n"));
                }
                if ($doc['note_id']) {
                    fwrite($handle, $this->jt(($level + 1)." NOTE @".$doc['note_id']."@\r\n"));
                }
                if ($doc['note']) {
                    $this->exportLongText($handle, 'NOTE', ($level + 1), $doc['note']);
                }

                if ($doc['indCitation']) {
                    $this->getCites($handle, ($level + 1), $personId, 'EMPTY', null, $type = 'personDocument');
                }
            }
        }
        unset($docs);

        return true;
    }

    private function getPersonlog(&$handle, $level, $personId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' UPPER(DATE_FORMAT(MAX(jlg.changeDateTime), '.$db->quote('%e %b %Y').')) ');
        $query->from(' #__joaktree_logs  jlg ');
        $query->where(' jlg.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jlg.object_id = '.$db->quote($personId).' ');
        $query->where(' jlg.object    = '.$db->quote('person').' ');
        $query->where(' jlg.logevent != '.$db->quote('JT_L_PRSN').' ');

        $db->setQuery($query);
        $log = $db->loadResult();

        if (!empty($log)) {
            fwrite($handle, $this->jt($level." CHAN\r\n"));
            fwrite($handle, $this->jt(($level + 1)." DATE ".$log."\r\n"));
        }
        unset($log);
    }

    private function getPersonNotes(&$handle, $level, $personId, $orderNumber = null, $type = null)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jpe.* ');
        $query->from(' #__joaktree_person_notes  jpe ');
        $query->where(' jpe.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jpe.person_id = '.$db->quote($personId).' ');

        if (!empty($type)) {
            if ($type == 'name') {
                $query->where(' jpe.nameOrderNumber = '.$orderNumber.' ');
                $query->where(' jpe.eventOrderNumber IS NULL ');
            }

            if ($type == 'event') {
                $query->where(' jpe.nameOrderNumber IS NULL ');
                $query->where(' jpe.eventOrderNumber = '.$orderNumber.' ');
            }
        } else {
            $query->where(' jpe.nameOrderNumber IS NULL ');
            $query->where(' jpe.eventOrderNumber IS NULL ');
        }

        $query->order(' jpe.orderNumber ');

        $db->setQuery($query);
        $notes = $db->loadAssocList();

        if (is_array($notes)) {
            foreach ($notes as $note) {
                if ($note['note_id']) {
                    fwrite($handle, $this->jt($level." NOTE @".$note['note_id']."@\r\n"));
                } else {
                    $this->exportLongText($handle, 'NOTE', $level, $note['value']);
                }

                if ($note['indCitation']) {
                    $this->getCites($handle, ($level + 1), $personId, 'EMPTY', $note['orderNumber'], 'personNote');
                }
            }
        }
        unset($notes);

        return true;
    }

    private function getRelationNotes(&$handle, $level, $personId_1, $personId_2, $orderNumber)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jre.* ');
        $query->from(' #__joaktree_relation_notes  jre ');
        $query->where(' jre.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jre.person_id_1 = '.$db->quote($personId_1).' ');
        $query->where(' jre.person_id_2 = '.$db->quote($personId_2).' ');
        $query->where(' jre.eventOrderNumber = '.$orderNumber.' ');

        $query->order(' jre.orderNumber ');

        $db->setQuery($query);
        $notes = $db->loadAssocList();

        if (is_array($notes)) {
            foreach ($notes as $note) {
                if ($note['note_id']) {
                    fwrite($handle, $this->jt($level." NOTE @".$note['note_id']."@\r\n"));
                } else {
                    $this->exportLongText($handle, 'NOTE', $level, $note['value']);
                }
            }
        }
        unset($notes);

        return true;
    }

    private function getCites(&$handle, $level, $personId_1, $personId_2, $orderNumber, $type)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(' jcn.* ');
        $query->from(' #__joaktree_citations  jcn ');
        $query->where(' jcn.objectType = '.$db->quote($type).' ');
        if ($orderNumber) {
            $query->where(' jcn.objectOrderNumber = '.$orderNumber.' ');
        }
        $query->where(' jcn.app_id = '.(int) $this->procObject->id.' ');
        $query->where(' jcn.person_id_1 = '.$db->quote($personId_1).' ');
        $query->where(' jcn.person_id_2 = '.$db->quote($personId_2).' ');
        $query->order(' jcn.orderNumber ');

        $db->setQuery($query);
        $cites = $db->loadAssocList();

        if (is_array($cites)) {
            foreach ($cites as $cite) {
                fwrite($handle, $this->jt($level." SOUR @".$cite['source_id']."@\r\n"));
                if (!empty($cite['page'])) {
                    $this->exportLongText($handle, 'PAGE', ($level + 1), $cite['page']);
                }
                if (!empty($cite['quotation'])) {
                    fwrite($handle, $this->jt(($level + 1)." DATA\r\n"));
                    $this->exportLongText($handle, 'TEXT', ($level + 2), $cite['quotation']);
                }
                if (!empty($cite['dataQuality'])) {
                    fwrite($handle, $this->jt(($level + 1)." QUAY ".$cite['dataQuality']."\r\n"));
                }
                if (!empty($cite['note'])) {
                    $this->exportLongText($handle, 'NOTE', ($level + 1), $cite['note']);
                }
            }
        }
        unset($cites);

        return true;
    }

    private function getPersonFamilies(&$handle, $level, $personId, $type = 'adult')
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if ($type == 'child') {
            $query = $db->getQuery(true);

            $query->select(' DISTINCT jrn.family_id ');
            $query->select(' jrn.subtype ');
            $query->from(' #__joaktree_relations  jrn ');
            $query->where(' jrn.app_id = '.(int) $this->procObject->id.' ');
            $query->where(' jrn.person_id_1 = '.$db->quote($personId).' ');
            $query->where(' jrn.type IN ('.$db->quote('father').', '.$db->quote('mother').') ');
            $query->order(' jrn.orderNumber_1 ');
        } else {
            // adult
            $query = 'SELECT DISTINCT iv_jrn.family_id '
                    .'FROM '
                    .'( SELECT  jrn.family_id '
                    .'  ,       jrn.orderNumber_1 AS orderNumber '
                    .'  FROM    #__joaktree_relations  jrn '
                    .'  WHERE   jrn.app_id      = '.(int) $this->procObject->id.' '
                    .'  AND     jrn.person_id_1 = '.$db->quote($personId).' '
                    .'  AND     jrn.type        = '.$db->quote('partner').' '
                    .'  UNION   '
                    .'  SELECT  jrn.family_id '
                    .'  ,       jrn.orderNumber_2 AS orderNumber '
                    .'  FROM    #__joaktree_relations  jrn '
                    .'  WHERE   jrn.app_id      = '.(int) $this->procObject->id.' '
                    .'  AND     jrn.person_id_2 = '.$db->quote($personId).' '
                    .'  AND     jrn.type        = '.$db->quote('partner').' '
                    .'  UNION   '
                    .'  SELECT  jrn.family_id '
                    .'  ,       jrn.orderNumber_2 AS orderNumber '
                    .'  FROM    #__joaktree_relations  jrn '
                    .'  WHERE   jrn.app_id      = '.(int) $this->procObject->id.' '
                    .'  AND     jrn.person_id_2 = '.$db->quote($personId).' '
                    .'  AND     jrn.type        IN ('.$db->quote('father').', '.$db->quote('mother').') '
                    .') iv_jrn '
                    .'ORDER BY  iv_jrn.orderNumber ';
        }

        $db->setQuery($query);
        $fams = $db->loadAssocList();

        if (is_array($fams)) {
            foreach ($fams as $fam) {
                if ($type == 'child') {
                    fwrite($handle, $this->jt($level." FAMC @".$fam['family_id']."@\r\n"));

                    if ((!empty($fam['subtype']))
                        && ($fam['subtype'] != 'natural')
                        && ($fam['subtype'] != 'spouse')
                        && ($fam['subtype'] != 'partner')
                    ) {
                        fwrite($handle, $this->jt(($level + 1)." PEDI ".$fam['subtype']."\r\n"));
                    }
                } else {
                    fwrite($handle, $this->jt($level." FAMS @".$fam['family_id']."@\r\n"));
                }
            }
        }
        unset($fams);

        return true;
    }

    private function exportLongText(&$handle, $tag, $level, $textString)
    {
        $texts = explode('&#10;&#13;', $textString);

        $firstline = true;

        foreach ($texts as $text) {
            if ($firstline) {
                $line = $this->jt($level." ".$tag." ".$text);
                $lineLength = strlen($line);
                if ($lineLength < 255) {
                    fwrite($handle, $line."\r\n");
                } else {
                    fwrite($handle, substr($line, 0, 254)."\r\n");

                    while ($lineLength > 254) {
                        $line = ($level + 1)." CONC ".substr($line, 254);
                        $lineLength = strlen($line);
                        fwrite($handle, substr($line, 0, 254)."\r\n");
                    }
                }
                $firstline = false;
            } else {
                $line = $this->jt(($level + 1)." CONT ".$text);
                $lineLength = strlen($line);
                if ($lineLength < 255) {
                    fwrite($handle, $line."\r\n");
                } else {
                    fwrite($handle, substr($line, 0, 254)."\r\n");

                    while ($lineLength > 254) {
                        $line = ($level + 1)." CONC ".substr($line, 254);
                        $lineLength = strlen($line);
                        fwrite($handle, substr($line, 0, 254)."\r\n");
                    }
                }
            }
        }
    }

    private function jt($line)
    {
        // & (= ampersand)
        $line = str_replace("&#38;", "&", $line);
        // < (= less than sign)
        $line = str_replace("&#60;", "<", $line);
        // > (= greater than sign)
        $line = str_replace("&#62;", ">", $line);

        //return utf8_decode($line);
        return $line;
    }

    /*
    ** Main function to import the gedcom file.
    */
    public function process()
    {
        // initialize parameters and paths / filename
        $params				= JoaktreeHelper::getJTParams($this->procObject->id);
        $path  				= JPATH_ROOT.'/'.$params->get('gedcomfile_path');
        $filename			= $path.'/export_' .$params->get('gedcomfile_name');
        $patronymSetting	= (int) $params->get('patronym');
        $patronymString		= $params->get('patronymSeparation', '-');
        $procStepSize		= (int) $params->get('procStepSize', 50);
        $ret				= true;

        // check if family_id are filled correctly
        if (($this->procObject->status == 'new') && ($this->checkFamily_id())) {
            $this->procObject->msg .= '<br />'.$this->fixFamily_id();
            return $this->procObject;
        }

        // check if gedcom file exists, when status is new
        if (@is_file($filename) && ($this->procObject->status == 'new')) {
            //unlink($filename);
            $this->procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_GEDCOM', $filename);
            $this->procObject->status = 'end';
            return $this->procObject;
        }

        // initialize array
        $this->objectLines  = array();

        // initialize counters
        $teller0 = 0; // counter for gedcom objects
        $tellert = 0; // counter for total number of lines in file

        $this->objectType = 'START';

        // open file
        $handle = @fopen($filename, "a");

        // Loop through the array.
        if ($handle) {

            if ($this->procObject->status == 'new') {
                $date = Factory::getDate();
                fwrite($handle, $this->jt("0 HEAD\r\n"));
                fwrite($handle, $this->jt("1 SOUR JOAKTREE\r\n"));
                fwrite($handle, $this->jt("2 VERS ".JoaktreeHelper::getJoaktreeVersion()."\r\n"));
                fwrite($handle, $this->jt("1 DEST JOAKTREE\r\n"));
                fwrite($handle, $this->jt("1 DATE ".$date->format('d M Y')."\r\n"));
                fwrite($handle, $this->jt("1 CHAR UTF-8\r\n"));
                fwrite($handle, $this->jt("1 FILE ".$filename."\r\n"));
                fwrite($handle, $this->jt("1 GEDC\r\n"));
                fwrite($handle, $this->jt("2 VERS 5.5\r\n"));
                fwrite($handle, $this->jt("2 FORM LINEAGE-LINKED\r\n"));

                $this->procObject->status = 'person';
            }

            if ($this->procObject->status == 'person') {
                $offset = $this->procObject->persons;
                $persons = $this->getPersons($offset, $procStepSize);

                if (count($persons) == 0) {
                    $this->procObject->status = 'family';
                    return $this->procObject;
                }

                foreach ($persons as $person) {
                    $this->procObject->persons++;
                    fwrite($handle, $this->jt("0 @".$person['id']."@ INDI\r\n"));

                    // names - main name + subnames

                    if (($patronymSetting == 9) && (!empty($person['patronym']))) {
                        // add patronym to name field using patronymString-separation
                        $firstName = $person['firstName'].' '.$patronymString.$person['patronym'].$patronymString;
                    } else {
                        $firstName = $person['firstName'];
                    }

                    fwrite($handle, $this->jt("1 NAME ".$firstName." /".$person['familyName']."/\r\n"));
                    $this->getNames($handle, 2, $person['id']);
                    if ($patronymSetting == 2) {
                        // add patronym to _PATR field
                        fwrite($handle, $this->jt("2 _PATR ".$person['patronym']."\r\n"));
                    }
                    if (!empty($person['prefix'])) {
                        fwrite($handle, $this->jt("2 NPFX ".$person['prefix']."\r\n"));
                    }
                    if (!empty($person['suffix'])) {
                        fwrite($handle, $this->jt("2 NSFX ".$person['suffix']."\r\n"));
                    }

                    // sex
                    fwrite($handle, $this->jt("1 SEX ".$person['sex']."\r\n"));

                    // last change
                    $this->getPersonLog($handle, 1, $person['id']);

                    // person events
                    $this->getPersonEvents($handle, 1, $person['id']);

                    // person notes
                    if ($person['indNote']) {
                        $this->getPersonNotes($handle, 1, $person['id']);
                    }

                    // person documents
                    $this->getDocuments($handle, 1, $person['id']);

                    // person citations
                    if ($person['indCitation']) {
                        $this->getCites($handle, 1, $person['id'], 'EMPTY', 0, $type = 'person');
                    }

                    // person as adult (partner, parent) in family
                    if (($person['indHasPartner']) || ($person['indHasChild'])) {
                        $this->getPersonFamilies($handle, 1, $person['id'], $type = 'adult');
                    }

                    // person as child in family
                    if ($person['indHasParent']) {
                        $this->getPersonFamilies($handle, 1, $person['id'], $type = 'child');
                    }

                } // end loop through persons
                // done with persons
                return $this->procObject;
            }

            if ($this->procObject->status == 'family') {
                $offset = $this->procObject->families;
                $families = $this->getFamilies($offset, $procStepSize);

                if (count($families) == 0) {
                    $this->procObject->status = 'note';
                    return $this->procObject;
                }

                foreach ($families as $family) {
                    $this->procObject->families++;
                    fwrite($handle, $this->jt("0 @".$family['family_id']."@ FAM\r\n"));

                    // partners
                    $partnerSets = $this->getFamilyPartners($family['family_id']);
                    if (is_array($partnerSets)) {
                        foreach ($partnerSets as $partnerSet) {
                            if (($partnerSet['sex1'] == 'M') && ($partnerSet['sex2'] == 'F')) {
                                fwrite($handle, $this->jt("1 HUSB @".$partnerSet['person_id_1']."@\r\n"));
                                fwrite($handle, $this->jt("1 WIFE @".$partnerSet['person_id_2']."@\r\n"));
                            } elseif (($partnerSet['sex1'] == 'F') && ($partnerSet['sex2'] == 'M')) {
                                fwrite($handle, $this->jt("1 HUSB @".$partnerSet['person_id_2']."@\r\n"));
                                fwrite($handle, $this->jt("1 WIFE @".$partnerSet['person_id_1']."@\r\n"));
                            } else {
                                $tag1 = ($partnerSet['sex1'] == 'F') ? 'WIFE' : 'HUSB';
                                $tag2 = ($partnerSet['sex2'] == 'F') ? 'WIFE' : 'HUSB';
                                if ($partnerSet['person_id_1'] < $partnerSet['person_id_2']) {
                                    fwrite($handle, $this->jt("1 ".$tag1." @".$partnerSet['person_id_1']."@\r\n"));
                                    fwrite($handle, $this->jt("1 ".$tag2." @".$partnerSet['person_id_2']."@\r\n"));
                                } else {
                                    fwrite($handle, $this->jt("1 ".$tag2." @".$partnerSet['person_id_2']."@\r\n"));
                                    fwrite($handle, $this->jt("1 ".$tag1." @".$partnerSet['person_id_1']."@\r\n"));
                                }
                            }
                        }
                    } else {
                        // single parent family
                        $singleParents = $this->getSingleParent($family['family_id']);
                        if (is_array($singleParents)) {
                            foreach ($singleParents as $singleParent) {
                                $tag = ($singleParent['type'] == 'mother') ? 'WIFE' : 'HUSB';
                                fwrite($handle, $this->jt("1 ".$tag." @".$singleParent['person_id']."@\r\n"));
                            }
                        }
                    }

                    // children
                    $this->getFamilyChildren($handle, 1, $family['family_id']);

                    // person events
                    if (is_array($partnerSets)) {
                        foreach ($partnerSets as $partnerSet) {
                            $this->getRelationEvents($handle, 1, $partnerSet['person_id_1'], $partnerSet['person_id_2']);
                        }
                    }
                }
                // done with families
                return $this->procObject;
            }

            if ($this->procObject->status == 'note') {
                $offset = $this->procObject->notes;
                $notes = $this->getNotes($offset, $procStepSize);

                if (count($notes) == 0) {
                    $this->procObject->status = 'source';
                    return $this->procObject;
                }

                foreach ($notes as $note) {
                    $this->procObject->notes++;
                    fwrite($handle, $this->jt("0 @".$note['id']."@ NOTE\r\n"));

                    if (!empty($note['value'])) {
                        $this->exportLongText($handle, 'TEXT', 1, $note['value']);
                    }
                }

                // done with notes
                return $this->procObject;
            }

            if ($this->procObject->status == 'source') {
                $offset = $this->procObject->sources;
                $sources = $this->getSources($offset, $procStepSize);

                if (count($sources) == 0) {
                    $this->procObject->status = 'repo';
                    return $this->procObject;
                }

                foreach ($sources as $source) {
                    $this->procObject->sources++;
                    fwrite($handle, $this->jt("0 @".$source['id']."@ SOUR\r\n"));

                    if (!empty($source['author'])) {
                        $this->exportLongText($handle, 'AUTH', 1, $source['author']);
                    }
                    if (!empty($source['title'])) {
                        $this->exportLongText($handle, 'TITL', 1, $source['title']);
                    }
                    if (!empty($source['publication'])) {
                        $this->exportLongText($handle, 'PUBL', 1, $source['publication']);
                    }
                    if (!empty($source['information'])) {
                        $this->exportLongText($handle, 'TEXT', 1, $source['information']);
                    }
                    if (!empty($source['repo_id'])) {
                        fwrite($handle, $this->jt("1 REPO @".$source['repo_id']."@\r\n"));
                    }
                }

                // done with sources
                return $this->procObject;
            }

            if ($this->procObject->status == 'repo') {
                $offset = $this->procObject->repos;
                $repos = $this->getRepos($offset, $procStepSize);

                if (count($repos) == 0) {
                    $this->procObject->status = 'close';
                    return $this->procObject;
                }

                foreach ($repos as $repo) {
                    $this->procObject->repos++;
                    fwrite($handle, $this->jt("0 @".$repo['id']."@ REPO\r\n"));

                    if (!empty($repo['name'])) {
                        fwrite($handle, $this->jt("1 NAME ".$repo['name']."\r\n"));
                    }
                    if (!empty($repo['website'])) {
                        fwrite($handle, $this->jt("1 WWW ".$repo['website']."\r\n"));
                    }
                }

                // done with repos
                return $this->procObject;
            }

            if ($this->procObject->status == 'close') {
                fwrite($handle, $this->jt("0 TRLR\r\n"));
                $this->procObject->status = 'stop';
                $this->procObject->msg = Text::sprintf('JTPROCESS_END_MSG', $this->procObject->id);
                // $this->procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_GEDCOM', $filename);
                //$this->procObject->status = 'end';
            }
        }

        if ($handle) {
            fclose($handle);
        }

        return $this->procObject;
    }
}
