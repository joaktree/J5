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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\Names;

class Gedcompersons2 extends \StdClass
{
    public function __construct($app_id)
    {
        $this->_db      = Factory::getContainer()->get(DatabaseInterface::class);
        $this->errors   = array();
        $this->application = Factory::getApplication();

        // parameters
        $params           		= JoaktreeHelper::getJTParams($app_id);
        $this->patronymSetting	= (int) $params->get('patronym');
        $this->patronymString	= $params->get('patronymSeparation');
        $this->familynameSetting = (int) $params->get('familyname');
        $tmpDoc					= (int) $params->get('indDocuments');
        if ($tmpDoc == 0) {
            $this->docsFromGedcom	= false;
        } else {
            $this->docsFromGedcom	= true;
        }

        // initialize counters + date
        $currentYear 			= (int)date('Y');
        //$currentYear 			= (int)strftime('%Y');
        $this->offsetYear  		= $currentYear - 100;

        // initialize tables
        $this->admin 			= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Adminpersons');
        //$this->documents		= Table::getInstance('DocumentsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\', array('dbo' => $this->_db));
        $this->documents        = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Documents');
        $this->persons			= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persons');
        $this->person_names		= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Personnames');
        $this->person_events	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Personevents');
        $this->person_notes		= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Personnotes');
        $this->person_docs_1	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persondocuments');
        $this->person_docs_2	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persondocuments');
        $this->person_citations	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Citations');
        $this->relations		= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relations');
        $this->relation_events	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationevents');
        $this->relation_notes	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationnotes');
        $this->relation_citations = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Citations');
        $this->children			= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relations');

        // $this->logs				= Table::getInstance('LogsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\', array('dbo' => $this->_db));
        $this->logs             = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');


        // set the application id for these tables
        $this->app_id = $app_id;
        $this->admin->set('app_id', $app_id);
        $this->persons->set('app_id', $app_id);
        $this->person_names->set('app_id', $app_id);
        $this->person_events->set('app_id', $app_id);
        $this->person_notes->set('app_id', $app_id);
        $this->person_docs_1->set('app_id', $app_id);
        $this->person_docs_2->set('app_id', $app_id);
        $this->person_citations->set('app_id', $app_id);
        $this->relations->set('app_id', $app_id);
        $this->relation_events->set('app_id', $app_id);
        $this->relation_notes->set('app_id', $app_id);
        $this->relation_citations->set('app_id', $app_id);
        $this->children->set('app_id', $app_id);

        // logs
        $this->logs->set('app_id', $app_id);
        $this->logs->set('object', 'prsn');
    }

    /*
    ** -------------------------------------
    ** ADMIN SETTINGS
    ** -------------------------------------
    */

    /*
    ** Function to set the "show" attribute automatically based on
    ** information about the person.
    */
    private function admin_show_person()
    {
        $show = true;

        return $show;
    }


    /*
    ** Function to set the "living" attribute automatically based on
    ** information about the person.
    */
    private function admin_living_person()
    {
        $living = $this->indLiving;

        return $living;
    }


    /*
    ** Function to set the "link" for a person to his/her own page
    ** Children who died before a certain age (that is parameter "age no page"),
    ** do not have their own page
    */
    private function admin_page_person()
    {
        static $age_no_page;

        if (!isset($age_no_page)) {
            $params           = JoaktreeHelper::getJTParams($this->app_id);
            $age_no_page      = (int) $params->get('age_no_page', 0);
        }

        $show_page = true;

        if (isset($this->birthYear) and isset($this->deathYear) and isset($age_no_page)) {
            $age = $this->deathYear - $this->birthYear;
            if (($age >= 0) and ($age < $age_no_page)) {
                $show_page = false;
            } else {
                $show_page = true;
            }
        } else {
            $show_page = true;
        }


        return $show_page;
    }

    /*
       ** Function to set the default map for a person
       */
    private function admin_map_person()
    {
        $params           = JoaktreeHelper::getJTParams($this->app_id);
        $map      = (int) $params->get('map', 0);
        return $map;
    }


    /*
    ** Function to set administration setting automatically for newly added persons
    ** Settings can later be modified by administrator.
    */
    private function admin_person()
    {
        // check whether person exists
        $this->admin->set('id', $this->persons->get('id'));
        $exists = $this->admin->person_exists();

        if ($exists) {
            // person exists: do nothing do not update admin table
            return true;
        } else {
            // insert person into admin table
            $this->admin->set('published', $this->admin_show_person());
            $this->admin->set('living', $this->admin_living_person() ? 1 : 0);
            $this->admin->set('page', $this->admin_page_person());
            $this->admin->set('map', $this->admin_map_person());

            $ret = $this->admin->insert();

            if (!$ret) {
                $this->errors[] = $this->_db->getError();
                $this->application->enqueueMessage(Text::_('function admin_person: '.$this->errors[count($this->errors) - 1]), 'notice');
            }

            return $ret;
        }
    }

    /*
    ** -------------------------------------
    ** FUNCTIONS FOR PROCESSING CITATIONS, NAMES, EVENTS, NOTES, AND DOCUMENTS
    ** -------------------------------------
    */

    /*
    ** Function to get the unique key for a source
    */
    private function getSourceKey($gedcomSource)
    {
        static $keyCounter;

        $tmp = rtrim(ltrim($gedcomSource, '@'), '@');

        if ($tmp == $gedcomSource) {
            // there is no source key in gedcom, because the @'s are missing
            // for these type of GedCom files, joaktree will create a source key
            // based on the source value.
            $query = 'SELECT   id '
                    .'FROM     #__joaktree_sources '
                    .'WHERE    title = ' . $this->_db->Quote($gedcomSource);

            $this->_db->setQuery($query);
            $tmpId = $this->_db->loadResult();

            if ($tmpId == null) {
                // no record found -> create a new key and insert a new record
                if (!isset($keyCounter)) {
                    // first time: initialize counter
                    $query = 'SELECT   count(id) '
                    .'FROM     #__joaktree_sources '
                    .'WHERE    app_id = ' . $this->_db->Quote($this->app_id);
                    $this->_db->setQuery($query);
                    $cntId = $this->_db->loadResult();
                    if (!$cntId) {
                        $keyCounter = 1;
                    } else {
                        $keyCounter = $cntId + 1;
                    }
                } else {
                    // not first time: increase counter
                    $keyCounter++;
                }

                $sourceKey = 'B'.$keyCounter;
                $query = 'INSERT '
                        .'INTO     #__joaktree_sources '
                        .'         ( app_id, id, title ) '
                        .'VALUES   ( '.$this->app_id.' '
                        .'         , '.$this->_db->Quote($sourceKey).' '
                        .'         , '.$this->_db->Quote($gedcomSource).' '
                        .'         ) ';

                $this->_db->setQuery($query);
                $this->_db->execute($query); //$this->_db->query( $query );

            } else {
                // record found -> this is the key.
                $sourceKey = $tmpId;
            }

        } else {
            // the @'s are stripped. The key is recovered.
            $sourceKey = $tmp;
        }

        return $sourceKey;
    }

    /*
    ** Function to save a citation for a person
    */
    private function setPersonCitation($objectType, $source_id)
    {
        $ret = true;

        // if a previous source exist, this should first be stored
        if ($this->person_citations->get('source_id')) {
            $ret = $this->person_citations->store();
        }

        if ($ret) {
            // fill the source information. source text may follow.
            // source is not processed yet.
            $this->person_citations->set('source_id', $source_id);
            $this->person_citations->set('objectType', $objectType);

            switch ($objectType) {
                case "person":
                    $this->person_citations->set('objectOrderNumber', 0);
                    break;
                case "personName":
                    $this->person_citations->set('objectOrderNumber', $this->namePersonCounter);
                    break;
                case "personEvent":
                    $this->person_citations->set('objectOrderNumber', $this->eventPersonCounter);
                    break;
                case "personNote":
                    $this->person_citations->set('objectOrderNumber', $this->notePersonCounter);
                    break;
                default:
                    $this->person_citations->set('objectOrderNumber', 0);
                    break;
            }

            $this->citationPersonCounter++;
            $this->person_citations->set('orderNumber', $this->citationPersonCounter);
        } else {
            $this->errors[] = $this->person_citations->getError();
            $this->application->enqueueMessage(Text::_('function setPersonCitation: '.$this->errors[count($this->errors) - 1]), 'notice');
        }

        return $ret;
    }

    /*
    ** Function to save a citation for a relation
    */
    private function setRelationCitation($objectType, $source_id)
    {
        $ret = true;

        if ($this->relation_citations->get('person_id_2') ==  null) {
            return $ret;
        }

        // if a previous source exist, this should first be stored
        if ($this->relation_citations->get('source_id') != null) {
            $ret = $this->relation_citations->store();
        }

        if ($ret) {
            // fill the source information. source text may follow.
            // source is not processed yet.
            $this->relation_citations->set('source_id', $source_id);
            $this->relation_citations->set('objectType', $objectType);

            switch ($objectType) {
                case "relation":
                    $this->relation_citations->set('objectOrderNumber', 0);
                    break;
                case "relationEvent":
                    $this->relation_citations->set('objectOrderNumber', $this->eventRelationCounter);
                    break;
                case "relationNote":
                    $this->relation_citations->set('objectOrderNumber', $this->noteRelationCounter);
                    break;
                default:
                    $this->relation_citations->set('objectOrderNumber', 0);
                    break;
            }

            $this->citationRelationCounter++;
            $this->relation_citations->set('orderNumber', $this->citationRelationCounter);
        } else {
            $this->errors[] = $this->relation_citations->getError();
            $this->application->enqueueMessage(Text::_('function setRelationCitation: '.$this->errors[count($this->errors) - 1]), 'notice');
        }

        return $ret;
    }

    /*
    ** Function to save a name of a person
    */
    private function setPersonName($nameType, $name)
    {
        $ret = true;

        // if a previous event exist, this should first be processed
        if ($this->person_names->get('value')) {
            $ret = $this->person_names->store();
        }

        if ($ret) {
            // fill the name information. Name is not processed yet.
            $this->namePersonCounter++;
            $this->person_names->set('orderNumber', $this->namePersonCounter);
            $this->person_names->set('code', $nameType);
            $this->person_names->set('value', $name);
        } else {
            $this->errors[] = $this->person_names->getError();
            $this->application->enqueueMessage(Text::_('function setPersonName: '.$this->errors[count($this->errors) - 1]), 'notice');
        }

        return $ret;
    }


    /*
    ** Function to save an event for a person
    */
    private function setPersonEvent($event, $value)
    {
        // if a previous event exist, this should first be processed
        $ret = true;

        if ($this->person_events->get('code')) {
            $this->person_events->checkLocation();
            $ret = $this->person_events->store();
        }

        if ($ret) {
            // empty old info and fill the event information. Event is not processed yet.
            $this->eventPersonCounter++;
            $this->person_events->reset(); // initialize record
            $this->person_events->set('orderNumber', $this->eventPersonCounter);
            $this->person_events->set('code', $event);
            if ($value && strlen($value) > 1) {
                $this->person_events->set('value', $value);
            }
        } else {
            $this->errors[] = $this->person_events->getError();
            $this->application->enqueueMessage(Text::_('function setPersonEvent: '.$this->errors[count($this->errors) - 1]), 'notice');
        }

        return $ret;
    }

    /*
    ** Function to save a note for a person
    */
    private function setPersonNote($objectType, $value)
    {
        $ret = true;
        if (is_null($value)) {
            return $ret;
        }
        if (($this->person_notes->get('value') != null) or ($this->person_notes->get('note_id') != null)) {
            $ret = $this->person_notes->store();
            if (!$ret) {
                $this->errors[] = $this->person_notes->getError();
                $this->application->enqueueMessage(Text::_('function setPersonNote: '.$this->errors[count($this->errors) - 1]), 'notice');
            }
        }

        // fill the event information. Event is not processed yet.
        $this->notePersonCounter++;
        $this->person_notes->set('orderNumber', $this->notePersonCounter);

        // try to strip @ and save in local value
        $tmpValue = ltrim(rtrim($value, '@'), '@');
        if ($value && $value != $tmpValue) {
            // @ are stripped and local value is therefore different than value
            // this is an note_id
            $this->person_notes->set('note_id', $tmpValue);
        } else {
            // stripping had no effect. values are identical
            // this is a real note
            $this->person_notes->set('value', $value);
        }

        switch ($objectType) {
            case "personName":
                $this->person_notes->set('nameOrderNumber', $this->namePersonCounter);
                $this->person_notes->set('eventOrderNumber', null);
                break;
            case "personEvent":
                $this->person_notes->set('nameOrderNumber', null);
                $this->person_notes->set('eventOrderNumber', $this->eventPersonCounter);
                break;
            default:
                $this->person_notes->set('nameOrderNumber', null);
                $this->person_notes->set('eventOrderNumber', null);
                break;
        }

        return $ret;
    }

    /*
    ** Function to save a document for a person
    */
    private function setDocument($person_id_1, $person_id_2, $value)
    {
        $ret = true;

        if ((!$this->docsFromGedcom) || is_null($value)) {
            // no documents from gedcom file are saved
            return $ret;
        }

        // try to strip @ and save in local value
        $tmpValue = trim($value, '@');
        if ((isset($value)) && (trim($value) != null) && ($value != $tmpValue)) {
            // @ are stripped and local value is therefore different than value
            // this is an object_id

            // save the link between the document and the person
            $this->person_docs_1->set('person_id', $person_id_1);
            $this->person_docs_1->set('document_id', $tmpValue);
            $ret = $this->person_docs_1->store();
            if (!$ret) {
                $this->errors[] = $this->person_docs_1->getError();
                $this->application->enqueueMessage(Text::_('function setDocument 1: '.$this->errors[count($this->errors) - 1]), 'notice');
            }

            // document is linked to a relation
            if ($person_id_2 != null) {
                // save the link between the document and the second person
                $this->person_docs_2->set('person_id', $person_id_2);
                $this->person_docs_2->set('document_id', $tmpValue);
                $ret = $this->person_docs_2->store();
                if (!$ret) {
                    $this->errors[] = $this->person_docs_2->getError();
                    $this->application->enqueueMessage(Text::_('function setDocument 2: '.$this->errors[count($this->errors) - 1]), 'notice');
                }
            }


        } else {
            // stripping had no effect. values are identical
            // this is a real object

            // check whether we have to store a previous document
            if ($this->documents->get('file') != null) {
                // storing previous document
                $this->documents->set('app_id', $this->app_id);
                $document_id = $this->documents->store();

                // store function returned the document-id
                $ret = $document_id;
                if (!$ret) {
                    $this->errors[] = $this->documents->getError();
                    $this->application->enqueueMessage(Text::_('function setDocument 3: '.$this->errors[count($this->errors) - 1]), 'notice');
                }
                // start actions for this docuement
                $this->documents->set('app_id', $this->app_id);

                if ($ret) {
                    // save the link between the document and the person
                    $this->person_docs_1->set('document_id', $document_id);
                    $ret = $this->person_docs_1->store();
                    if (!$ret) {
                        $this->errors[] = $this->person_docs_1->getError();
                        $this->application->enqueueMessage(Text::_('function setDocument 4: '.$this->errors[count($this->errors) - 1]), 'notice');
                    }
                }

                // document is linked to a relation
                if (($ret) and ($this->person_docs_2->get('person_id') != null)) {
                    // save the link between the document and the second person
                    $this->person_docs_2->set('document_id', $document_id);
                    $ret = $this->person_docs_2->store();
                    if (!$ret) {
                        $this->errors[] = $this->person_docs_2->getError();
                        $this->application->enqueueMessage(Text::_('function setDocument 5: '.$this->errors[count($this->errors) - 1]), 'notice');
                    }
                }
            }

            // save the link for the persons of this new document
            $this->person_docs_1->set('person_id', $person_id_1);
            $this->person_docs_2->set('person_id', $person_id_2);

        }

        return $ret;
    }

    /*
    ** Function to save an event for a relation
    */
    private function setRelationEvent($event, $value)
    {
        $ret = true;
        if (!$value) {
            return $ret;
        }
        if ($this->relation_events->get('person_id_2') ==  null) {
            return $ret;
        }

        // if a previous event exist, this should first be stored
        if ($this->relation_events->get('code') != null) {
            $this->relation_events->checkLocation();
            $ret = $this->relation_events->store();
        }

        if ($ret) {
            // fill the event information. Event is not processed yet.
            $this->eventRelationCounter++;
            $this->relation_events->set('code', $event);
            $this->relation_events->set('orderNumber', $this->eventRelationCounter);
            if (strlen($value) > 1) {
                $this->relation_events->set('value', $value);
            }
        } else {
            $this->errors[] = $this->relation_events->getError();
            $this->application->enqueueMessage(Text::_('function setRelationEvent: '.$this->errors[count($this->errors) - 1]), 'notice');
        }

        return $ret;
    }

    /*
    ** Function to save a note for a relation
    */
    private function setRelationNote($objectType, $value)
    {
        $ret = true;
        if (!$value) {
            return $ret;
        }
        if ($this->relation_notes->get('person_id_2') ==  null) {
            return $ret;
        }

        // if a previous event exist, this should first be stored
        if (($this->relation_notes->get('value') != null) or ($this->relation_notes->get('note_id') != null)) {
            $ret = $this->relation_notes->store();
            if (!$ret) {
                $this->errors[] = $this->relation_notes->getError();
                $this->application->enqueueMessage(Text::_('function setRelationNote: '.$this->errors[count($this->errors) - 1]), 'notice');
            }
        }

        // fill the note information and store note
        $this->noteRelationCounter++;
        $this->relation_notes->set('orderNumber', $this->noteRelationCounter);

        // try to strip @ and save in local value
        $tmpValue = ltrim(rtrim($value, '@'), '@');
        if ($value != $tmpValue) {
            // @ are stripped and local value is therefore different than value
            // this is an note_id
            $this->relation_notes->set('note_id', $tmpValue);
        } else {
            // stripping had no effect. values are identical
            // this is a real note
            $this->relation_notes->set('value', $value);
        }

        switch ($objectType) {
            case "relationEvent":
                $this->relation_notes->set('eventOrderNumber', $this->eventRelationCounter);
                break;
            default:
                $this->relation_notes->set('eventOrderNumber', null);
                break;
        }

        return $ret;
    }

    /*
    ** -------------------------------------
    ** FUNCTIONS FOR RELATONS (children, parents and spouses)
    ** -------------------------------------
    */

    /*
    ** Function to save child information in gedcom tables based
    ** on person and family ID.
    ** Function is only used for children with a PEDI tag
    */
    private function keepChild($pid1, $family_id, $pedi)
    {
        $ret = true;

        switch (strtolower($pedi)) {
            case "adopted": $subtype = 'adopted';
                break;
            case "foster":	$subtype = 'foster';
                break;
            case "steph":	// continue
            case "step":	$subtype = 'step';
                break;
            case "legal":	$subtype = 'legal';
                break;
            default:		$subtype = 'natural';
                break;
        }

        // set all attributes for the object line

        $query = 'INSERT   '
                .'INTO     #__joaktree_gedcom_objectlines '
                .'(        object_id '
                .',        order_nr '
                .',        level '
                .',        tag '
                .',        value '
                .',        subtype '
                .') '
                .'VALUES ( '.$this->_db->quote($pid1).' '
                .'       , 0 '
                .'       , 1 '
                .'       , '.$this->_db->quote('FAMC').' '
                .'       , '.$this->_db->quote($family_id).' '
                .'       , '.$this->_db->quote($subtype).' '
                .')';
        $this->_db->setQuery($query);

        // store the object line
        $ret = $this->_db->execute(); //$this->_db->query();
        if (!$ret) {
            $this->application->enqueueMessage(Text::_('function keepChild: person='.$pid1.'; family='.$family_id), 'notice');
        }

        return $ret;
    }

    private function getChildSubtype($family_id, $child_id)
    {
        $query = $this->_db->getQuery(true);

        $query->select(' subtype ');
        $query->from(' #__joaktree_gedcom_objectlines ');
        $query->where(' object_id  = '.$this->_db->quote($child_id).' ');
        $query->where(' tag        = '.$this->_db->quote('FAMC').' ');
        $query->where(' value      = '.$this->_db->quote($family_id).' ');

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        return $result;
    }

    /*
    ** Function to store child with parents information
    */
    private function child($family_id, $father_id, $mother_id, $child_id, $order_nr, $subtype)
    {
        $ret = true;
        if ($subtype = '') {
            $subtype = null;
        }
        if (($ret) and ($father_id != null)) {
            $this->children->set('app_id', $this->app_id);
            $this->children->set('person_id_1', $child_id);
            $this->children->set('person_id_2', $father_id);
            $this->children->set('type', 'father');
            $this->children->set('family_id', $family_id);
            $this->children->set('orderNumber_2', $order_nr);
            if ($subtype) {
                $this->children->set('subtype', $subtype);
            }

            $ret = $this->children->store();
        }

        if (($ret) and ($mother_id != null)) {
            $this->children->set('app_id', $this->app_id);
            $this->children->set('person_id_1', $child_id);
            $this->children->set('person_id_2', $mother_id);
            $this->children->set('type', 'mother');
            $this->children->set('family_id', $family_id);
            $this->children->set('orderNumber_2', $order_nr);
            if ($subtype) {
                $this->children->set('subtype', $subtype);
            }

            $ret = $this->children->store();
        }

        return $ret;
    }


    /*
    ** Function to save spouse information in gedcom tables based
    ** on person and family ID.
    */
    private function spouse($pid1, $family_id, $order_nr)
    {
        $ret = true;

        // set all attributes for the object line
        $query = 'INSERT   '
                .'INTO     #__joaktree_gedcom_objectlines '
                .'(        object_id '
                .',        order_nr '
                .',        level '
                .',        tag '
                .',        value '
                .',        subtype '
                .') '
                .'VALUES ( '.$this->_db->quote($pid1).' '
                .'       , '.$order_nr.' '
                .'       , 1 '
                .'       , '.$this->_db->quote('FAMS').' '
                .'       , '.$this->_db->quote($family_id).' '
                .'       , NULL '
                .')';
        $this->_db->setQuery($query);

        // store the object line
        $ret = $this->_db->execute(); //$this->_db->query();
        if (!$ret) {
            $this->application->enqueueMessage(Text::_('function spouse: person='.$pid1.'; family='.$family_id), 'notice');
        }

        return $ret;
    }

    /*
    ** Function to retreive spouse information.
    */
    private function numberSpouse($person_id, $family_id)
    {
        // initialize values
        $order_nr = 999;

        $query = 'SELECT order_nr '
                .'FROM   #__joaktree_gedcom_objectlines '
                .'WHERE  object_id = '.$this->_db->quote($person_id).' '
                .'AND    value = '.$this->_db->quote($family_id).' ';
        $this->_db->setQuery($query);

        $order_nr = $this->_db->loadResult();

        return $order_nr;
    }

    /*
    ** Main function to process all families from gedcom file.
    */
    public function family(&$family_id, &$fam_lines)
    {
        $ret = true;

        // initialize values
        $this->eventRelationCounter = 0;
        $this->noteRelationCounter = 0;
        $this->citationRelationCounter = 0;
        $event1Type = 'none';
        $event2Type = 'none';
        $event3Type = 'none';

        $person_id_1 = null;
        $person_id_2 = null;
        $orderNumber_1 = 1;
        $orderNumber_2 = 1;
        $childCount    = 0;
        $indRelation = false;

        // first search for the parents
        $father_id = null;
        $mother_id = null;
        foreach ($fam_lines as $fam_line_num => $fam_line) {
            // process only level 1
            if ($fam_line['level'] ==  '1') {
                switch ($fam_line['tag']) {
                    case "HUSB":	// Husband and wife
                    case "WIFE":
                        if ($fam_line['tag'] == 'HUSB') {
                            $person_id_1 = ltrim(rtrim($fam_line['value'], '@'), '@');
                            $father_id = $person_id_1;
                        }
                        if ($fam_line['tag'] == 'WIFE') {
                            $person_id_2 = ltrim(rtrim($fam_line['value'], '@'), '@');
                            $mother_id = $person_id_2;
                        }

                        if (($person_id_1 != null) and ($person_id_2 != null)) {
                            $indRelation = true;
                            $relationtype = '';
                            $event1Type = 'relation';
                            $this->relations->set('type', 'partner');
                            $this->relations->set('family_id', $family_id);

                            if ($person_id_2 < $person_id_1) {
                                // switch order: smallest first
                                $tmpid = $person_id_1;
                                $person_id_1 = $person_id_2;
                                $person_id_2 = $tmpid;
                            }

                            $orderNumber_1 = $this->numberSpouse($person_id_1, $family_id);
                            $orderNumber_2 = $this->numberSpouse($person_id_2, $family_id);
                            $this->relations->set('orderNumber_1', $orderNumber_1);
                            $this->relations->set('orderNumber_2', $orderNumber_2);

                            $this->relations->set('app_id', $this->app_id);
                            $this->relations->set('person_id_1', $person_id_1);
                            $this->relations->set('person_id_2', $person_id_2);

                            $this->relation_events->set('app_id', $this->app_id);
                            $this->relation_events->set('person_id_1', $person_id_1);
                            $this->relation_events->set('person_id_2', $person_id_2);

                            $this->relation_notes->set('app_id', $this->app_id);
                            $this->relation_notes->set('person_id_1', $person_id_1);
                            $this->relation_notes->set('person_id_2', $person_id_2);

                            $this->relation_citations->set('app_id', $this->app_id);
                            $this->relation_citations->set('person_id_1', $person_id_1);
                            $this->relation_citations->set('person_id_2', $person_id_2);
                        }
                        break;
                    default: break;
                } // end of switch
            } // end of if-statement
            if ($indRelation) {
                break;
            }
        } // enf of foreach loop

        // loop through the resulting set of family object lines and extract information
        foreach ($fam_lines as $fam_line_num => $fam_line) {
            // level swicth
            $this->persons->set('indNote', 0); // pascal
            switch ($fam_line['level']) {
                case "1":
                    switch ($fam_line['tag']) {
                        case "CHIL":
                            $childCount++;
                            if ($ret) {
                                $child_id = ltrim(rtrim($fam_line['value'], '@'), '@');
                                $subtype = $this->getChildSubtype($family_id, $child_id);
                                $ret = $this->child($family_id, $father_id, $mother_id, $child_id, $childCount, $subtype);
                            }
                            break;
                        case "ANUL":	// do nothing
                        case "DIV":	// do nothing
                        case "ENGA":	// do nothing
                        case "MARR":	// do nothing
                        case "MARB":	// do nothing
                        case "MARC":	// do nothing
                        case "MARL":	// do nothing
                        case "MARS":	// do nothing
                        case "NCHI":	// do nothing
                        case "EVEN":	$event1Type = 'relationEvent';
                            if ($ret) {
                                $ret = $this->setRelationEvent($fam_line['tag'], $fam_line['value']);
                            }
                            break;
                        case "NOTE":	// Note for relation
                            $event1Type = 'relationNote';
                            if ($ret) {
                                $ret = $this->setRelationNote('relation', $fam_line['value']);
                            }
                            if ($ret === 1) {
                                $this->relations->set('indNote', 1); // pascal
                            } else {
                                $this->relations->set('indNote', 0); // pascal
                            }
                            break;
                        case "SOUR":	// Source for relation
                            $event1Type = 'relationSource';
                            if ($ret) {
                                $sourceKey = $this->getSourceKey($fam_line['value']);
                                $ret = $this->setRelationCitation('relation', $sourceKey);
                                if ($ret) {
                                    $this->relations->set('indCitation', true);
                                }
                                if ($ret) {
                                    $this->persons->set('indCitation', true);
                                }
                            }
                            break;
                        case "OBJE":	// Document for person
                            $event1Type = 'relationDocument';
                            if ($ret) {
                                $ret = $this->setDocument($person_id_1, $person_id_2, $fam_line['value']);
                            }
                            break;
                        default:	$event1Type = 'none';
                            break;
                    }
                    break;

                case "2": // get tag information and execute level 2: tag switch
                    switch ($fam_line['tag']) {
                        case "DATE":	// set the date of event
                            $event2Type = 'none';
                            if ($event1Type == 'relationEvent') {
                                $this->relation_events->set('eventDate', $fam_line['value']);
                            }
                            break;
                        case "PLAC":	// set the place of event
                            $event2Type = 'none';
                            if ($event1Type == 'relationEvent') {
                                $this->relation_events->set('location', $fam_line['value']);
                            }
                            break;
                        case "TYPE":	// type is only used to set the type of marriage
                            $event2Type = 'none';
                            if ($event1Type == 'relationEvent') {
                                $this->relation_events->set('type', $fam_line['value']);

                                switch (strtolower($fam_line['value'])) {
                                    case "registered":			// continue
                                    case "registered partnership":
                                    case "partners":			// continue
                                    case "unknown":				// continue
                                    case "not given":
                                        $relationtype = ($relationtype == 'spouse')
                                                            ? 'spouse'
                                                            : 'partner';
                                        break;
                                    case "civil":				// continue
                                    case "civil marriage":		// continue
                                    case "religious":			// continue
                                    case "religious marriage":	// continue
                                    default:
                                        $relationtype = 'spouse';
                                        break;
                                }


                            }
                            break;
                        case "PAGE":	$event2Type = 'none';
                            if ($event1Type == 'relationSource') {
                                $this->relation_citations->set('page', $fam_line['value']);
                            }
                            break;
                        case "QUAY":	$event2Type = 'none';
                            if ($event1Type == 'relationSource') {
                                $this->relation_citations->set('dataQuality', $fam_line['value']);
                            }
                            break;
                        case "FORM":	$event2Type = 'none';
                            if ($event1Type == 'relationDocument') {
                                $this->documents->set('fileformat', $fam_line['value']);
                            }
                            break;
                        case "FILE":	$event2Type = 'none';
                            if ($event1Type == 'relationDocument') {
                                $this->documents->set('file', $fam_line['value']);
                            }
                            break;
                        case "TITL":	$event2Type = 'none';
                            if ($event1Type == 'relationDocument') {
                                $this->documents->set('title', $fam_line['value']);
                            }
                            break;
                        case "NOTE":	if ($event1Type == 'relationSource') {
                            // do nothing for right now
                            $event2Type = 'none';
                            $this->relation_citations->set('note', $fam_line['value']);
                        } elseif ($event1Type == 'relationDocument') {
                            $event2Type = 'none';

                            // try to strip @ and save in local value
                            $tmpValue = ltrim(rtrim($fam_line['value'], '@'), '@');
                            if ($fam_line['value'] != $tmpValue) {
                                // @ are stripped and local value is therefore different than value
                                // this is an note_id
                                $this->documents->set('note_id', $tmpValue);
                            } else {
                                // stripping had no effect. values are identical
                                // this is a real note
                                $this->documents->set('note', $fam_line['value']);
                            }
                        } else {
                            // Note for event
                            $event2Type = 'relationNote';
                            if ($ret) {
                                $ret = $this->setRelationNote($event1Type, $fam_line['value']);
                            }
                            if ($event1Type == 'relationEvent') {
                                if ($ret === 1) {
                                    $this->relation_events->set('indNote', 1); // pascal
                                } else {
                                    $this->relation_events->set('indNote', 0); // pascal
                                }
                            }
                        }
                            break;
                        case "SOUR":	// collect all sources for this relation
                            $event2Type = 'relationSource';
                            if (($ret) and ($event1Type != 'none')) {
                                $sourceKey = $this->getSourceKey($fam_line['value']);
                                $ret = $this->setRelationCitation($event1Type, $sourceKey);
                                if ($event1Type == 'relationEvent') {
                                    if ($ret) {
                                        $this->relation_events->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                } elseif ($event1Type == 'relationNote') {
                                    if ($ret) {
                                        $this->relation_events->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                } elseif (($event1Type == 'relationDocument')
                                        and ($this->docsFromGedcom)) {
                                    if ($ret) {
                                        $this->documents->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                }
                            }
                            break;
                        default:	break;
                    }
                    break;
                case "3": // get tag information and execute level 2: tag switch
                    switch ($fam_line['tag']) {
                        case "TEXT":	// found source text. save it but do not process it yet.
                            $event3Type = 'none';
                            if ($event1Type == 'relationSource') {
                                $this->relation_citations->set('quotation', $fam_line['value']);
                            }
                            break;
                        case "PAGE":	$event3Type = 'none';
                            if ($event2Type == 'relationSource') {
                                $this->relation_citations->set('page', $fam_line['value']);
                            }
                            break;
                        case "QUAY":	$event3Type = 'none';
                            if ($event2Type == 'relationSource') {
                                $this->relation_citations->set('dataQuality', $fam_line['value']);
                            }
                            break;
                        case "NOTE":	if ($event2Type == 'relationSource') {
                            // do nothing for right now
                            $event3Type = 'none';
                            $this->relation_citations->set('note', $fam_line['value']);
                        }
                            break;
                        case "SOUR":	// collect all sources for this relation
                            $event3Type = 'relationSource';
                            if (($ret) and ($event2Type != 'none')) {
                                $sourceKey = $this->getSourceKey($fam_line['value']);
                                $ret = $this->setRelationCitation($event2Type, $sourceKey);
                                if ($event2Type == 'relationNote') {
                                    if ($ret) {
                                        $this->relation_notes->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                }
                            }
                            break;
                        default:	break;
                    }
                    break;
                case "4": // get tag information and execute level 4: tag switch
                    switch ($fam_line['tag']) {
                        case "TEXT":	// found source text. save it but do not process it yet.
                            if ($event2Type == 'relationSource') {
                                $this->relation_citations->set('quotation', $fam_line['value']);
                            }
                            break;
                        case "PAGE":
                            if ($event3Type == 'relationSource') {
                                $this->relation_citations->set('page', $fam_line['value']);
                            }
                            break;
                        case "QUAY":
                            if ($event3Type == 'relationSource') {
                                $this->relation_citations->set('dataQuality', $fam_line['value']);
                            }
                            break;
                        case "NOTE":	if ($event3Type == 'relationSource') {
                            // do nothing for right now
                            $this->relation_citations->set('note', $fam_line['value']);
                        }
                            break;
                        default:	break;
                    }
                    break;
                case "5": // get tag information and execute level 4: tag switch
                    switch ($fam_line['tag']) {
                        case "TEXT":	// found source text. save it but do not process it yet.
                            if ($event3Type == 'relationSource') {
                                $this->relation_citations->set('quotation', $fam_line['value']);
                            }
                            break;
                        default:	break;
                    }
                    break;
                default:  break;
            } // end level switch
        } // end loop through family object lines

        // insert the spousal information in relation table
        if (($ret) && ($indRelation == true)) {
            if (!$relationtype) {
                $relationtype = null;
            }
            $this->relations->set('subtype', $relationtype);
            $ret = $this->relations->store();
        }
        if (!$ret) {
            $this->errors[] = $this->relations->getError();
            $this->application->enqueueMessage(Text::sprintf('JTGEDCOM_MESSAGE_NOSUCFAMILY', $family_id), 'notice') ;
        }


        if ($ret) {
            $ret = $this->setRelationEvent(null, null);
        }
        if ($ret) {
            $ret = $this->setRelationNote(null, null);
        }
        if ($ret) {
            $ret = $this->setRelationCitation('none', null);
        }

        return $ret;

    }

    /*
    ** -------------------------------------
    ** MAIN FUNCTION
    ** -------------------------------------
    */
    /*
    ** Main function to process all persons from gedcom file.
    */
    public function process($person_id, $row_lines)
    {
        $ret = true;

        // every loop: update counter and start with empty record
        $this->persons->set('app_id', $this->app_id);
        $this->persons->set('id', $person_id);
        $event1Type = 'none';
        $event2Type = 'none';
        $event3Type = 'none';
        $firstTimeNameTag = true;
        // admin settings
        $this->indLiving  = true;
        unset($this->birthYear);
        unset($this->deathYear);

        // check whether a record exists with person id
        try {
            $my_person = $this->persons->load();
        } catch (\Exception $e) {
            $my_person = false;
        }
        if ($my_person !== false) {
            // record exists: existing record will be updated
            $ind_update = true;
        } else {
            // record does not exists: new record will be inserted
            $ind_update = false;
        }
        // spouses
        $spouseCounter = 0;

        // initialize names, events, notes, documents, citations, relations, spouses for this person
        // names
        $retdelete = $this->person_names->deleteNames($person_id);
        $this->person_names->set('app_id', $this->app_id);
        $this->person_names->set('person_id', $person_id);
        $this->namePersonCounter = 0;

        $retdelete = $this->person_events->deleteEvents($person_id);
        $this->person_events->set('app_id', $this->app_id);
        $this->person_events->set('person_id', $person_id);
        $this->eventPersonCounter = 0;

        // notes
        $retdelete = $this->person_notes->deleteNotes($person_id);
        $this->person_notes->set('app_id', $this->app_id);
        $this->person_notes->set('person_id', $person_id);
        $this->notePersonCounter = 0;

        // documents
        $retdelete = $this->person_docs_1->deleteDocuments($person_id);
        $this->documentPersonCounter = 0;

        // sources
        $retdelete = $this->person_citations->deletePersonCitations($person_id);
        $this->person_citations->set('app_id', $this->app_id);
        $this->person_citations->set('person_id_1', $person_id);
        $this->person_citations->set('person_id_2', 'EMPTY');
        $this->citationPersonCounter = 0;

        // logs
        $this->logs->set('object_id', $person_id);

        // loop through lines related to the person
        foreach ($row_lines as $row_line_num => $row_line) {
            $information = $row_line['tag']; // pascal
            switch ($row_line['level']) {
                case "1": // set $information to be used for level 2 info
                    switch ($information) {
                        case "NAME":	$event1Type = 'personName';
                            // extract first name and family name
                            $names  = explode("/", $row_line['value'], 3);
                            if (!isset($names[0])) {
                                $names[0] = null;
                            } else {
                                $names[0] = trim($names[0]);
                            }
                            if (!isset($names[1])) {
                                $names[1] = null;
                            } else {
                                $names[1] = trim($names[1]);
                            }

                            if ($firstTimeNameTag) {
                                // The first time the NAME tag is read -> this is the main name!
                                if ($this->patronymSetting == 9) {
                                    // extract patronym from name field using patronymString-separation
                                    $tmpName = explode($this->patronymString, $names[0], 3);
                                    if (!isset($tmpName[0])) {
                                        $tmpName[0] = null;
                                    } else {
                                        $tmpName[0] = trim($tmpName[0]);
                                    }
                                    if (!isset($tmpName[1])) {
                                        $tmpName[1] = null;
                                    } else {
                                        $tmpName[1] = trim($tmpName[1]);
                                    }
                                    $this->persons->set('firstName', $tmpName[0]);
                                    $this->persons->set('patronym', $tmpName[1]);
                                } else {
                                    $this->persons->set('firstName', $names[0]);
                                }

                                // set the familyName and namePreposition
                                Names::setFamilyName($names[1], $this->persons, $this->familynameSetting);

                                $firstTimeNameTag = false;
                            } else {
                                // concurrent NAME tags -> This are "also known as" names.
                                $information = 'AKA';
                                if ($ret) {
                                    $ret = $this->setPersonName($information, $names[0].' '.$names[1]);
                                }
                            }
                            break;
                        case "SEX":	$event1Type = 'none';
                            $this->persons->set('sex', $row_line['value']);
                            break;
                        case "FAMS":	$event1Type = 'none';
                            // FAMS is the family this person is a spouse
                            // in this family a spouse can be found
                            $spouseCounter++;
                            if ($ret) {
                                $ret = $this->spouse($person_id, rtrim(ltrim($row_line['value'], '@'), '@'), $spouseCounter);
                            }
                            break;
                        case "FAMC":	$event1Type = 'familyChild';
                            $family_id = trim($row_line['value'], '@');
                            break;
                        case "BIRT":	// do nothing
                        case "CHR":	// do nothing
                        case "DEAT":	if ($information == 'DEAT') {
                            $this->indLiving  = false;
                        }
                            // no break
                        case "BURI":	if ($information == 'BURI') {
                            $this->indLiving  = false;
                        }
                            // no break
                        case "CREM":	if ($information == 'CREM') {
                            $this->indLiving  = false;
                        }
                            //case "ADOP":	// do nothing
                            // no break
                        case "BAPM":	// do nothing
                        case "BARM":	// do nothing
                        case "BASM":	// do nothing
                        case "BLES":	// do nothing
                        case "CHRA":	// do nothing
                        case "CONF":	// do nothing
                        case "FCOM":	// do nothing
                        case "NATU":	// do nothing
                        case "EMIG":	// do nothing
                        case "IMMI":	// do nothing
                        case "GRAD":	// do nothing
                        case "RETI":	// do nothing
                        case "EVEN":	// do nothing
                        case "CAST":	// do nothing
                        case "DSCR":	// do nothing
                        case "EDUC":	// do nothing
                        case "NATI":	// do nothing
                        case "OCCU":	// do nothing
                        case "RELI":	// do nothing
                        case "RESI":	// do nothing
                        case "_BRTM":	if ($information == '_BRTM') {
                            $information = 'BRTM';
                        }
                            // no break
                        case "CIRC":	if ($information == 'CIRC') {
                            $information = 'BRTM';
                        }
                            // no break
                        case "_YART":	if ($information == '_YART') {
                            $information = 'YART';
                            $this->indLiving  = false;
                        }
                            // no break
                        case "TITL":	$event1Type = 'personEvent';
                            if ($ret) {
                                $ret = $this->setPersonEvent($information, $row_line['value']);
                            }
                            break;
                        case "SOUR":	// Source for person
                            $event1Type = 'personSource';
                            $sourceKey = $this->getSourceKey($row_line['value']);
                            if ($ret) {
                                $ret = $this->setPersonCitation('person', $sourceKey);
                            }
                            if ($ret) {
                                $this->persons->set('indCitation', true);
                            }
                            break;
                        case "NOTE":	// Note for person
                            $event1Type = 'personNote';
                            $this->persons->set('indNote', 0); // pascal
                            if ($ret) {
                                $ret = $this->setPersonNote('person', $row_line['value']);
                            }
                            if ($ret === 1) {
                                $this->persons->set('indNote', 1);  // pascal
                            } else {
                                $this->persons->set('indNote', 0); // pascal
                            }
                            break;
                        case "OBJE":	// Document for person
                            $event1Type = 'personDocument';
                            if ($ret) {
                                $ret = $this->setDocument($person_id, null, $row_line['value']);
                            }
                            break;
                        case "CHAN": // changeDateTime
                            $event1Type = 'changeDateTime';
                            break;
                        default:	$event1Type = 'none';
                            break;
                    }
                    break;
                case "2":
                    if (($information == 'NAME') || ($information == 'AKA')) {
                        // case "2": if ($information == 'NAME') {
                        $nameTag = $row_line['tag'];
                        switch ($nameTag) {
                            case "GIVN":	// do nothing
                            case "SURN":	// do nothing
                            case "NICK":	// do nothing
                            case "_ADPN": 	if ($nameTag == '_ADPN') {
                                $nameTag = 'ADPN';
                            }
                                // no break
                            case "_AKA": 	if ($nameTag == '_AKA') {
                                $nameTag = 'AKA';
                            }
                                // no break
                            case "_AKAN":	if ($nameTag == '_AKAN') {
                                $nameTag = 'AKA';
                            }
                                // no break
                            case "_CALL":	if ($nameTag == '_CALL') {
                                $nameTag = 'AKA';
                            }
                                // no break
                            case "AKA":	// do nothing
                            case "_BIRN":	if ($nameTag == '_BIRN') {
                                $nameTag = 'BIRN';
                            }
                                // no break
                            case "_CENN":	if ($nameTag == '_CENN') {
                                $nameTag = 'CENN';
                            }
                                // no break
                            case "_CURN":	if ($nameTag == '_CURN') {
                                $nameTag = 'CURN';
                            }
                                // no break
                            case "_FKAN":	if ($nameTag == '_FKAN') {
                                $nameTag = 'FRKA';
                            }
                                // no break
                            case "_FRKA":	if ($nameTag == '_FRKA') {
                                $nameTag = 'FRKA';
                            }
                                // no break
                            case "_HEBN":	if ($nameTag == '_HEBN') {
                                $nameTag = 'HEBN';
                            }
                                // no break
                            case "_INDN":	if ($nameTag == '_INDN') {
                                $nameTag = 'INDG';
                            }
                                // no break
                            case "_INDG":	if ($nameTag == '_INDG') {
                                $nameTag = 'INDG';
                            }
                                // no break
                            case "_MARN":	if ($nameTag == '_MARN') {
                                $nameTag = 'MARN';
                            }
                                // no break
                            case "_MARNM":	if ($nameTag == '_MARNM') {
                                $nameTag = 'MARN';
                            }
                                // no break
                            case "_OTHN":	if ($nameTag == '_OTHN') {
                                $nameTag = 'OTHN';
                            }
                                // no break
                            case "_RELN":	if ($nameTag == '_RELN') {
                                $nameTag = 'RELN';
                            }
                                // no break
                            case "NAMR":	if ($nameTag == 'NAMR') {
                                $nameTag = 'RELN';
                            }
                                $event2Type = 'personName';
                                if ($ret) {
                                    $ret = $this->setPersonName($nameTag, $row_line['value']);
                                }
                                break;
                            case "_PATR":	if ($this->patronymSetting == 2) {
                                $this->persons->set('patronym', $row_line['value']);
                            }
                                break;
                            case "NPFX":	$this->persons->set('prefix', $row_line['value']);
                                break;
                            case "NSFX":	$this->persons->set('suffix', $row_line['value']);
                                break;
                            default:		$event2Type = 'none';
                                break;
                        }
                    }
                    switch ($row_line['tag']) {
                        case "DATE":	// all events
                            $event2Type = 'none';
                            if ($event1Type == 'personEvent') {
                                $this->person_events->set('eventDate', $row_line['value']);

                                switch ($information) {
                                    case "BIRT":	// do nothing
                                    case "CHR":		// do nothing
                                    case "BRTM":	// do nothing
                                    case "BAPM":	// do nothing
                                        if (!isset($this->birthYear)) {
                                            $this->birthYear = (int) substr(trim($row_line['value']), -4);
                                        }
                                        // no break
                                    case "BARM":	// do nothing
                                    case "BASM":	// do nothing
                                    case "BLES":	// do nothing
                                    case "CHRA":	// do nothing
                                        $eventYear = (int) substr(trim($row_line['value']), -4);
                                        if ($eventYear < $this->offsetYear) {
                                            $this->indLiving  = false;
                                        }
                                        break;
                                    case "DEAT":	// do nothing
                                    case "BURI":	// do nothing
                                    case "CREM":	// do nothing
                                        if (!isset($this->deathYear)) {
                                            $this->deathYear = (int) substr(trim($row_line['value']), -4);
                                        }
                                        break;
                                    default:	break;
                                }
                            } elseif ($event1Type == 'changeDateTime') {
                                $this->logs->setChangeDateTime(trim($row_line['value']));
                            }
                            break;
                        case "PLAC":	$event2Type = 'none';
                            if ($event1Type == 'personEvent') {
                                $this->person_events->set('location', $row_line['value']);
                            }
                            break;
                        case "TYPE":	// all other events
                            $event2Type = 'none';
                            if ($event1Type == 'personEvent') {
                                $this->person_events->set('type', $row_line['value']);
                            }
                            break;
                        case "PAGE":	$event2Type = 'none';
                            if ($event1Type == 'personSource') {
                                $this->person_citations->set('page', $row_line['value']);
                            }
                            break;
                        case "QUAY":	$event2Type = 'none';
                            if ($event1Type == 'personSource') {
                                $this->person_citations->set('dataQuality', $row_line['value']);
                            }
                            break;
                        case "FORM":	$event2Type = 'none';
                            if ($event1Type == 'personDocument') {
                                $this->documents->set('fileformat', $row_line['value']);
                            }
                            break;
                        case "FILE":	$event2Type = 'none';
                            if ($event1Type == 'personDocument') {
                                $this->documents->set('file', $row_line['value']);
                            }
                            break;
                        case "TITL":	$event2Type = 'none';
                            if ($event1Type == 'personDocument') {
                                $this->documents->set('title', $row_line['value']);
                            }
                            break;
                        case "NOTE":	if ($event1Type == 'personSource') {
                            // do nothing for right now
                            $event2Type = 'none';
                            $this->person_citations->set('note', $row_line['value']);
                        } elseif ($event1Type == 'personDocument') {
                            $event2Type = 'none';

                            // try to strip @ and save in local value
                            $tmpValue = ltrim(rtrim($row_line['value'], '@'), '@');
                            if ($row_line['value'] != $tmpValue) {
                                // @ are stripped and local value is therefore different than value
                                // this is an note_id
                                $this->documents->set('note_id', $tmpValue);
                            } else {
                                // stripping had no effect. values are identical
                                // this is a real note
                                $this->documents->set('note', $row_line['value']);
                            }
                        } else {
                            // Note for event
                            $event2Type = 'personNote';
                            if ($ret) {
                                $ret = $this->setPersonNote($event1Type, $row_line['value']);
                            }
                            if ($event1Type == 'personEvent') {
                                if ($ret === 1) {
                                    $this->person_events->set('indNote', 1);
                                } else {
                                    $this->person_events->set('indNote', 0);
                                }
                            }
                        }
                            break;
                        case "SOUR":	// collect all sources for this person
                            $event2Type = 'personSource';
                            if (($ret) and ($event1Type != 'none')) {
                                $sourceKey = $this->getSourceKey($row_line['value']);
                                $ret = $this->setPersonCitation($event1Type, $sourceKey);
                                if ($event1Type == 'personEvent') {
                                    if ($ret) {
                                        $this->person_events->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                } elseif ($event1Type == 'personNote') {
                                    if ($ret) {
                                        $this->person_notes->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                } elseif (($event1Type == 'personDocument')
                                        and ($this->docsFromGedcom)) {
                                    if ($ret) {
                                        $this->documents->set('indCitation', true);
                                    }
                                    if ($ret) {
                                        $this->persons->set('indCitation', true);
                                    }
                                }
                            }
                            break;
                        case "ADOP":	// Adopted child - don't know how this tag is used
                        case "FOST":  // Foster child - don't know how this tag is used
                            $row_line['value'] = ($row_line['tag'] == 'ADOP')
                                                    ? 'adopted'
                                                    : 'foster';
                            // continue
                            // no break
                        case "PEDI":
                            if ($event1Type == 'familyChild') {
                                $family_id = trim($row_line['value'], '@'); // pascal
                                $this->keepChild($person_id, $family_id, $row_line['value']);
                                unset($family_id);
                            }
                            break;
                        case "CHAN": // changeDateTime
                            $event2Type = 'changeDateTime';
                            break;
                        default:	break;
                    }
                    break;
                case "3": switch ($row_line['tag']) {
                    case "DATE":	$event3Type = 'none';
                        if (($information == 'NAME') and ($event2Type == 'personName')) {
                            $this->person_names->set('eventDate', $row_line['value']);
                        } elseif ($event2Type == 'changeDateTime') {
                            $this->logs->setChangeDateTime(trim($row_line['value']));
                        }
                        break;
                    case "PAGE":	$event3Type = 'none';
                        if ($event2Type == 'personSource') {
                            $this->person_citations->set('page', $row_line['value']);
                        }
                        break;
                    case "QUAY":	$event3Type = 'none';
                        if ($event2Type == 'personSource') {
                            $this->person_citations->set('dataQuality', $row_line['value']);
                        }
                        break;
                    case "TEXT":	$event3Type = 'none';
                        if ($event1Type == 'personSource') {
                            $this->person_citations->set('quotation', $row_line['value']);
                        }
                        break;
                    case "NOTE":	if ($event2Type == 'personSource') {
                        $event3Type = 'none';
                        $this->person_citations->set('note', $row_line['value']);
                    } else {
                        $event3Type = 'personNote';
                        if ($ret) {
                            $ret = $this->setPersonNote($event2Type, $row_line['value']);
                        }
                        if ($event2Type == 'personName') {
                            if ($ret === 1) {
                                $this->person_names->set('indNote', 1);
                            } else {
                                $this->persons_names->set('indNote', 0);
                            }
                        }
                    }
                        break;
                        // new level for source
                    case "SOUR":	// collect all sources for this person
                        $event3Type = 'personSource';
                        if (($ret) and ($event2Type != 'none')) {
                            $sourceKey = $this->getSourceKey($row_line['value']);
                            $ret = $this->setPersonCitation($event2Type, $sourceKey);
                            if ($event2Type == 'personName') {
                                if ($ret) {
                                    $this->person_names->set('indCitation', true);
                                }
                                if ($ret) {
                                    $this->persons->set('indCitation', true);
                                }
                            } elseif ($event2Type == 'personNote') {
                                if ($ret) {
                                    $this->person_notes->set('indCitation', true);
                                }
                                if ($ret) {
                                    $this->persons->set('indCitation', true);
                                }
                            }
                        }
                        break;
                    case "CHAN": // changeDateTime
                        $event3Type = 'changeDateTime';
                        break;
                    default:	break;
                }
                    break;
                case "4": switch ($row_line['tag']) {
                    case "DATE":	if ($event3Type == 'changeDateTime') {
                        $this->logs->setChangeDateTime(trim($row_line['value']));
                    }
                        break;
                    case "PAGE":	if ($event3Type == 'personSource') {
                        $this->person_citations->set('page', $row_line['value']);
                    }
                        break;
                    case "QUAY":	if ($event3Type == 'personSource') {
                        $this->person_citations->set('dataQuality', $row_line['value']);
                    }
                        break;
                    case "TEXT":	if ($event2Type == 'personSource') {
                        $this->person_citations->set('quotation', $row_line['value']);
                    }
                        break;
                    case "NOTE":	if ($event3Type == 'personSource') {
                        $this->person_citations->set('note', $row_line['value']);
                    }
                        break;
                    default:	break;
                }
                    break;
                case "5": switch ($row_line['tag']) {
                    case "TEXT":	if ($event3Type == 'personSource') {
                        $this->person_citations->set('quotation', $row_line['value']);
                    }
                        break;
                    default:	break;
                }
                    break;
                default:  break;
            } // end of level switch
        } // end of loop throuth lines related to person

        if (!$ind_update) {
            // insert new record for admin table
            if ($ret) {
                $ret = $this->admin_person();
            }
        }

        // store record
        if ($ret) {
            $ret = $this->persons->store();
        }

        // update last person name
        if ($ret) {
            $ret = $this->setPersonName(null, null);
        }

        // update last person event
        if ($ret) {
            $ret = $this->setPersonEvent(null, null);
        }

        // update last person note
        if ($ret) {
            $ret = $this->setPersonNote(null, null);
        }

        // update last person citation
        if ($ret) {
            $ret = $this->setPersonCitation('none', null);
        }

        // update last person document
        if ($ret) {
            $ret = $this->setDocument(null, null, null);
        }

        // log update
        if ($ret) {
            $ret = $this->logs->logChangeDateTime();
        }

        // if insert or update went ok, continue with next source; else stop
        if (!$ret) {
            $this->application->enqueueMessage(Text::sprintf('JTGEDCOM_MESSAGE_NOSUCPERSON', $person_id), 'notice') ;
        }

        return $ret;
    }
}
