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

namespace Joaktree\Component\Joaktree\Site\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Person;
use Joaktree\Component\Joaktree\Administrator\Helper\Relations;

class PersonformModel extends FormModel
{
    protected $errors = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function getApplicationId()
    {
        return JoaktreeHelper::getApplicationId();
    }

    public function getApplicationName()
    {
        return JoaktreeHelper::getApplicationName();
    }

    public function getTreeId()
    {
        return JoaktreeHelper::getTreeId();
    }

    public function getPersonId()
    {
        return JoaktreeHelper::getPersonId(false, true);
    }

    public function getRelationId()
    {
        return JoaktreeHelper::getRelationId();
    }

    public function getAction()
    {
        return JoaktreeHelper::getAction();
    }

    public function getAccess()
    {
        return JoaktreeHelper::getAccess();
    }

    public function getPerson()
    {
        static $person;
        if (!isset($person)) {
            $id = array();
            $id[ 'person_id' ]	= $this->getPersonId();
            if (isset($id[ 'person_id' ])) {
                $id[ 'app_id' ]		= $this->getApplicationId();
                $id[ 'tree_id' ]	= $this->getTreeId();
                $person	=  new Person($id, 'full');
            }
        }

        return $person;
    }

    public function getRelation()
    {
        static $relation;
        if (!isset($relation)) {
            $id = array();
            $id[ 'app_id' ]		= $this->getApplicationId();
            $id[ 'person_id' ] 	= $this->getRelationId();
            $id[ 'tree_id' ]	= $this->getTreeId();

            $relation	=  new Person($id, 'basic');
        }
        return $relation;
    }

    public function getPicture()
    {
        static $picture;
        if (!isset($picture)) {
            $input	= Factory::getApplication()->input;
            $tmp 	= $input->get('picture', null, 'string');
            if ($tmp) {
                $picture =  json_decode(base64_decode($tmp));
            } else {
                $picture = 1;
            }
        }
        return $picture;
    }

    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	mixed	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_joaktree.joaktree', 'personform', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.joaktree.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            if (!$data) {
                $data = [];
            }
        }
        return $data;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk	The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     * @since   11.1
     */
    public function getItem($pk = null)
    {
        return $this->getPerson();
    }


    public function delete($id)
    {
        $canDo	= JoaktreeHelper::getActions();

        // Edit changes
        if ((is_object($canDo)) && $canDo->get('core.delete')) {
            $tmp = explode("!", $id);
            try {
                $ret = $this->delete_person($tmp[0], $tmp[1]);
            } catch (\Exception $e) {
                $text = Text::_($e->getMessage());
                return $text;
            }
        } else {
            $ret = false;
        }
        $text = ($ret) ? Text::sprintf('JT_DELETED', $ret) : Text::_('JT_NOTAUTHORISED');
        return $text;
    }

    public function save(&$form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform");
        }
        $canDo	= JoaktreeHelper::getActions();
        $ret    = false;

        // Edit changes
        if (is_object($canDo)) {
            // in case we need to set relation indicators
            $this->relationIds = array();

            if ($canDo->get('core.create')) {
                // in case of a new person the id is empty and we are filling it now
                if ($form['person']['status'] == 'new') {
                    // retreive ID and double check that it is not used
                    $continue = true;
                    $i = 0;
                    while ($continue) {
                        $tmpId = JoaktreeHelper::generateJTId();
                        $i++;

                        if ($this->check($tmpId, 'person')) {
                            $form['person']['id'] = $tmpId;
                            $continue = false;
                            break;
                        }
                        if ($i > 100) {
                            $continue = false;
                            return Text::_('JTAPPS_MESSAGE_NOSAVE');
                        }
                    }
                }

                switch ($form['type']) {
                    case "newperson":
                        $ret = $this->save_events($form, 'person');
                        break;
                    case "addparent":
                    case "addpartner":
                    case "addchild":
                        $ret = $this->add_person_relations($form);
                        if ($ret) {
                            $ret = $this->save_events($form, 'person');
                        };
                        break;
                    default:		// continue
                        break;
                }
            }

            if ($canDo->get('core.edit')) {
                switch ($form['type']) {
                    case "pevents": $ret = $this->save_events($form, 'person');
                        break;
                    case "revents": $ret = $this->save_events($form, 'relation');
                        break;
                    case "names": 	$ret = $this->save_names($form, 'all');
                        break;
                    case "notes": 	$ret = $this->save_notes($form, 'person', null);
                        break;
                    case "references":
                        $ret = $this->save_references($form, 'person');
                        break;
                    case "media": 	$ret = $this->save_media($form);
                        break;
                    case "medialist":
                        $ret = $this->save_medialist($form);
                        break;
                    case "parents":
                    case "partners":
                    case "children":
                        $ret = $this->update_person_relations($form);
                        break;
                    default:		// continue
                        break;
                }
            }

            if ($canDo->get('core.edit.state')) {
                // State changes
                switch ($form['type']) {
                    case "state": $ret = $this->save_state($form);
                        break;
                    default:		// continue
                        break;
                }
            }

            // always save / update the person
            if ($ret) {
                $ret = $this->save_person($form);
            }

            // set relation indicators - if applicable
            if (($ret) && (count($this->relationIds) > 0)) {
                $ret = Relations::setRelationIndicators($form['person']['app_id'], $this->relationIds);
            }
        }

        // Deal with errors
        if (count($this->errors) > 0) {
            $text = implode(' ##### ', $this->errors);
            $ret  = false;
        } else {
            $text = ($ret) ? 'JT_SAVED' : 'JT_NOTAUTHORISED';
        }

        return Text::_($text);
    }

    private function delete_person($appId, $personId)
    {
        // $tabPerson	= Table::getInstance('joaktree_persons', 'Table');
        $tabPerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persons');

        // Bind the fields to the table
        $tabPerson->id             	= $personId;
        $tabPerson->app_id         	= $appId;

        // Load the person, so we can save the names for prosperity
        if (!$tabPerson->load()) {
            $this->setError('Error deleting person -> Person not found in table joaktree_persons: '.$appId.'!'.$personId);
            return false;
        }

        // Delete person, including all dependencies (cascading)
        if (!$tabPerson->delete()) {
            $this->setError('Error deleting person -> delete: '.$tabPerson->getError());
            return false;
        }

        // Logging
        // $tabLog	= Table::getInstance('LogsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabLog	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');
        $tabLog->app_id				= $appId;
        $tabLog->object_id			= $personId;
        $tabLog->object				= 'prsn';
        if (!$tabLog->log('D')) {
            $this->setError('Error deleting person -> log: '.$tabLog->getError());
            return false;
        }

        // $tabLogRemoval	= Table::getInstance('LogremovalsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabLogRemoval	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logremovals');
        $tabLogRemoval->app_id		= $appId;
        $tabLogRemoval->object_id	= $personId;
        $tabLogRemoval->object		= 'prsn';
        $names = array();
        $names[] = $tabPerson->firstName;
        $names[] = $tabPerson->namePreposition;
        $names[] = $tabPerson->familyName;
        $names[] = '['.$tabPerson->id.']';
        $name = implode(' ', $names);
        $tabLogRemoval->description	= substr($name, 0, 100);
        if (!$tabLogRemoval->store()) {
            $this->setError('Error deleting person -> remove-log: '.$tabLogRemoval->getError());
            return false;
        }

        return $name;
    }

    private function save_person(&$form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_person");
        }
        $ret = true;

        // Logging
        // $tabLog	= Table::getInstance('LogsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabLog	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');

        $tabLog->app_id				= $form['person']['app_id'];
        $tabLog->object_id			= $form['person']['id'];
        $tabLog->object				= 'prsn';
        if (!$tabLog->log(($form['person']['status'] == 'new') ? 'C' : 'U')) {
            $this->error[] = 'Error saving person -> log: '.$tabLog->getError();
            return false;
        }

        // if it is not a new or updated person - just a new relationship
        // we skip this function
        if ($form['person']['status'] == 'relation') {
            return true;
        }

        // new person - add it to persons-trees, persons, and admin
        if ($form['person']['status'] == 'new') {
            if ($ret) {
                $ret = $this->save_person_trees($form);
            } // Note: the default tree is set in this step
            if ($ret) {
                $ret = $this->save_state($form);
            }		  // Note: the default tree is saved in this step
            if ($ret) {
                $ret = $this->save_names($form, 'main');
            }
        } // end of adding new person

        if ($ret) {
            //$tabPerson	= Table::getInstance('PersonsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
            $tabPerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persons');

            $query = $this->_db->getquery(true);

            // Bind the form fields to the table
            $tabPerson->id             	= $form['person']['id'];
            $tabPerson->app_id         	= $form['person']['app_id'];
            if (isset($form['person']['sex'])) {
                $tabPerson->sex = $form['person']['sex'];

                if ($tabPerson->sex == 'F') {
                    $sextype = 'mother';
                } elseif ($tabPerson->sex == 'M') {
                    $sextype = 'father';
                } else {
                    $sextype = 'unknown';
                }

                // update the relationship table with the new sex
                if ($sextype != 'unknown') {
                    $query->clear();
                    $query->update(' #__joaktree_relations ');
                    $query->set(' type        = :sextype');
                    $query->where(' app_id      = :appid');
                    $query->where(' person_id_2 = :personid'.$this->_db->quote($tabPerson->id).' ');
                    $query->where(' type        IN ('.$this->_db->quote('father').', '.$this->_db->quote('mother').') ');
                    $query->bind(':appid', $tabPerson->app_id, \Joomla\Database\ParameterType::INTEGER);
                    $query->bind(':personid', $tabPerson->id, \Joomla\Database\ParameterType::STRING);
                    $query->bind(':treeid', $sextype, \Joomla\Database\ParameterType::STRING);

                    $this->_db->setquery($query);

                    if (!$this->_db->execute()) {
                        $this->errors[] = 'Error saving person -> joaktree_relations: '.$this->$table->getError();
                    }
                }
            }

            // Make sure the table is valid
            if (!$tabPerson->check()) {
                $this->errors[] = 'Error checking person: ';
                return false;
            }

            // Store the table to the database
            if (!$tabPerson->store(false)) {
                $this->errors[] = 'Error saving person: '.$tabPerson->getError();
                return false;
            }
        }

        return true;
    }

    private function save_state(&$form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_state");
        }
        //$tabPerson	= Table::getInstance('AdminpersonsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabPerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Adminpersons');

        // Bind the form fields to the table
        $tabPerson->id             	= $form['person']['id'];
        $tabPerson->app_id         	= $form['person']['app_id'];
        if (isset($form['person']['livingnew'])) {
            $tabPerson->living = $form['person']['livingnew'];
        }
        $tabPerson->published = (isset($form['person']['published'])) ? $form['person']['published'] : true;
        $tabPerson->page	  = (isset($form['person']['page'])) ? $form['person']['page'] : true;
        $tabPerson->map	  	  = (isset($form['person']['map'])) ? $form['person']['map'] : 0;
        if (isset($form['person']['default_tree_id'])) {
            $tabPerson->default_tree_id = $form['person']['default_tree_id'];
        }

        // Make sure the table is valid
        if (!$tabPerson->check()) {
            $this->errors[] = 'Error checking admin person: ';
            return false;
        }

        // Store the table to the database
        if (!$tabPerson->store(false)) {
            $this->errors[] = 'Error saving admin person: '.$tabPerson->getError();
            return false;
        }

        return true;
    }

    private function save_events(&$form, $level)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_events");
        }
        // person events
        // Store the references to the database
        if (!$this->save_references($form, $level)) {
            return false;
        }

        // Store the notes to the database
        if (!$this->save_notes($form, $level, 'event')) {
            return false;
        }

        // Events
        if (isset($form['person']['events'])) {
            switch ($level) {
                case "relation":	// $tabEvent	= Table::getInstance('RelationeventsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
                    $tabEvent	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationevents');

                    if ($form['person']['id'] < $form['person']['relations']['id'][0]) {
                        $tabEvent->person_id_1	= $form['person']['id'];
                        $tabEvent->person_id_2	= $form['person']['relations']['id'][0];
                    } else {
                        $tabEvent->person_id_2	= $form['person']['id'];
                        $tabEvent->person_id_1	= $form['person']['relations']['id'][0];
                    }
                    break;
                case "person":		// person same as default
                default:	//	$tabEvent	= Table::getInstance('PersoneventsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
                    $tabEvent	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Personevents');
                    $tabEvent->person_id        	= $form['person']['id'];
                    break;
            }

            // Bind the form fields to the table
            $tabEvent->app_id         	= $form['person']['app_id'];

            for ($i = 0; $i < count($form['person']['events']['orderNumber']); $i++) {
                switch ($level) {
                    case "relation":	$code = $form['person']['events']['relcode'][$i];
                        break;
                    case "person":		// person same as default
                    default:		$code = $form['person']['events']['code'][$i];
                        break;
                }

                $tabEvent->orderNumber 	= $form['person']['events']['orderNumber'][$i];
                $tabEvent->code		 	= $code;
                $tabEvent->type		 	= htmlspecialchars($form['person']['events']['type'][$i], ENT_QUOTES, 'UTF-8');

                // create string for eventdate
                $eventDate = '';
                // extended date fields only
                if ($form['person']['events']['eventDateType'][$i] == 'extended') {
                    if ($form['person']['events']['eventDateLabel1'][$i]) {
                        $eventDate .= $form['person']['events']['eventDateLabel1'][$i].' ';
                    }
                }

                // simple + extended date fields
                if (($form['person']['events']['eventDateType'][$i] == 'simple')
                   || ($form['person']['events']['eventDateType'][$i] == 'extended')
                ) {
                    if ($form['person']['events']['eventDateDay1'][$i]) {
                        $eventDate .= $form['person']['events']['eventDateDay1'][$i].' ';
                    }
                    if ($form['person']['events']['eventDateMonth1'][$i]) {
                        $eventDate .= $form['person']['events']['eventDateMonth1'][$i].' ';
                    }
                    if (!empty($form['person']['events']['eventDateYear1'][$i])) {
                        $eventDate .= $form['person']['events']['eventDateYear1'][$i].' ';
                    }
                }

                // extended date fields only
                if ($form['person']['events']['eventDateType'][$i] == 'extended') {
                    if ($form['person']['events']['eventDateLabel2'][$i]) {
                        $eventDate .= $form['person']['events']['eventDateLabel2'][$i].' ';
                    }
                    if ($form['person']['events']['eventDateDay2'][$i]) {
                        $eventDate .= $form['person']['events']['eventDateDay2'][$i].' ';
                    }
                    if ($form['person']['events']['eventDateMonth2'][$i]) {
                        $eventDate .= $form['person']['events']['eventDateMonth2'][$i].' ';
                    }
                    if (!empty($form['person']['events']['eventDateYear2'][$i])) {
                        $eventDate .= $form['person']['events']['eventDateYear2'][$i].' ';
                    }
                }

                // description date fields only
                if ($form['person']['events']['eventDateType'][$i] == 'description') {
                    if (!empty($form['person']['events']['eventDate'][$i])) {
                        $eventDate .= htmlspecialchars($form['person']['events']['eventDate'][$i].' ', ENT_QUOTES, 'UTF-8');
                    }
                }
                $tabEvent->eventDate 	= trim($eventDate);
                // create string for eventdate

                // set the location
                $tabEvent->location	 	= htmlspecialchars($form['person']['events']['location'][$i], ENT_QUOTES, 'UTF-8');

                // set the value
                $tabEvent->value		= htmlspecialchars($form['person']['events']['value'][$i], ENT_QUOTES, 'UTF-8');

                // Make sure the table is valid
                if (!$tabEvent->check()) {
                    $this->errors[] = 'Error checking event: ';
                    return false;
                }

                if (($form['person']['events']['status'][$i] == 'new')
                    || ($form['person']['events']['status'][$i] == 'loaded')
                ) {
                    // Store the table to the database
                    if (!$tabEvent->store(false)) {
                        $this->error[] = 'Error saving event: '.$tabEvent->getError();
                        return false;
                    }
                } elseif ($form['person']['events']['status'][$i] == 'loaded_deleted') {
                    // Delete row from the database
                    if (!$tabEvent->delete()) {
                        $this->errors[] = 'Error deleting event: '.$tabEvent->getError();
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function save_names(&$form, $action = 'all')
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_names");
        }
        $tabPerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persons');

        // Bind the form fields to the table
        $tabPerson->id             	= $form['person']['id'];
        $tabPerson->app_id         	= $form['person']['app_id'];
        $tabPerson->firstName       = isset($form['person']['firstName'])
                                        ? htmlspecialchars($form['person']['firstName'], ENT_QUOTES, 'UTF-8')
                                        : null;
        $tabPerson->patronym        = isset($form['person']['patronym'])
                                        ? htmlspecialchars($form['person']['patronym'], ENT_QUOTES, 'UTF-8')
                                        : null;
        $tabPerson->namePreposition = isset($form['person']['namePreposition'])
                                        ? htmlspecialchars($form['person']['namePreposition'], ENT_QUOTES, 'UTF-8')
                                        : null;
        $tabPerson->familyName      = isset($form['person']['rawFamilyName'])
                                        ? htmlspecialchars($form['person']['rawFamilyName'], ENT_QUOTES, 'UTF-8')
                                        : null;
        $tabPerson->prefix			= isset($form['person']['prefix'])
                                        ? htmlspecialchars($form['person']['prefix'], ENT_QUOTES, 'UTF-8')
                                        : null;
        $tabPerson->suffix      	= isset($form['person']['suffix'])
                                        ? htmlspecialchars($form['person']['suffix'], ENT_QUOTES, 'UTF-8')
                                        : null;
        $tabPerson->sex      		= isset($form['person']['sex'])
                                        ? htmlspecialchars($form['person']['sex'], ENT_QUOTES, 'UTF-8')
                                        : null; /// RRG 22-01-2017

        // Make sure the table is valid
        if (!$tabPerson->check()) {
            $this->errors[] = 'Error checking person name: ';
            return false;
        }

        // Store the table to the database
        if (!$tabPerson->store(false)) {
            $this->error[] = 'Error saving person name: '.$tabPerson->getError();
            return false;
        }

        if ($action == 'all') {
            // Store the references for additional names to the database
            if (!$this->save_references($form, 'person')) {
                return false;
            }

            // Store the notes for additional names to the database
            if (!$this->save_notes($form, 'person', 'name')) {
                return false;
            }

            // additional names
            //$tabName	= Table::getInstance('PersonnamesTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
            $tabName	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Personnames');

            // Bind the form fields to the table
            $tabName->person_id        	= $form['person']['id'];
            $tabName->app_id         	= $form['person']['app_id'];
            if (isset($form['person']['names']['orderNumber'])) {
                for ($i = 0; $i < count($form['person']['names']['orderNumber']); $i++) {
                    $tabName->orderNumber 	= $form['person']['names']['orderNumber'][$i];
                    $tabName->code		 	= $form['person']['names']['code'][$i];
                    $tabName->value			= htmlspecialchars($form['person']['names']['value'][$i], ENT_QUOTES, 'UTF-8');
                    // Make sure the table is valid
                    if (!$tabName->check()) {
                        $this->error[] = 'Error checking name: ';
                        return false;
                    }
                    if (($form['person']['names']['status'][$i] == 'new')
                        || ($form['person']['names']['status'][$i] == 'loaded')
                    ) {
                        // Store the table to the database
                        if (!$tabName->store(false)) {
                            $this->errors[] = 'Error saving name: '.$tabName->getError();
                            return false;
                        }
                    } elseif ($form['person']['names']['status'][$i] == 'loaded_deleted') {
                        // Delete row from the database
                        if (!$tabName->delete()) {
                            $this->errors[] = 'Error deleting name: '.$tabName->getError();
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function save_references(&$form, $level)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_references");
        }
        if (!isset($form['person']['references']) || !is_array($form['person']['references'])) {
            // no references
            return true;
        }

        // Citations
        // $tabRef	= Table::getInstance('CitationsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabRef	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Citations');

        // Bind the form fields to the table
        switch ($level) {
            case "relation":	if ($form['person']['id'] < $form['person']['relations']['id'][0]) {
                $tabRef->person_id_1	= $form['person']['id'];
                $tabRef->person_id_2	= $form['person']['relations']['id'][0];
            } else {
                $tabRef->person_id_2	= $form['person']['id'];
                $tabRef->person_id_1	= $form['person']['relations']['id'][0];
            }
                break;
            case "person":		// person same as default
            default:		$tabRef->person_id_1 = $form['person']['id'];
                $tabRef->person_id_2 = 'EMPTY';
                break;
        }

        $tabRef->app_id         	= $form['person']['app_id'];

        for ($i = 0; $i < count($form['person']['references']['objectOrderNumber']); $i++) {
            $tabRef->objectType		= $form['person']['references']['objectType'][$i];
            $tabRef->objectOrderNumber
                                    = $form['person']['references']['objectOrderNumber'][$i];
            $tabRef->source_id		= $form['person']['references']['app_source_id'][$i];
            $tabRef->orderNumber	= $form['person']['references']['orderNumber'][$i];
            $tabRef->dataQuality	= (!empty($form['person']['references']['dataQuality'][$i]))
                                        ? $form['person']['references']['dataQuality'][$i]
                                        : null;
            $tabRef->page			= (!empty($form['person']['references']['page'][$i]))
                                        ? htmlspecialchars($form['person']['references']['page'][$i], ENT_QUOTES, 'UTF-8')
                                        : null;
            $tabRef->quotation		= (!empty($form['person']['references']['quotation'][$i]))
                                        ? htmlspecialchars($form['person']['references']['quotation'][$i], ENT_QUOTES, 'UTF-8')
                                        : null;
            $tabRef->note			= (!empty($form['person']['references']['note'][$i]))
                                        ? htmlspecialchars($form['person']['references']['note'][$i], ENT_QUOTES, 'UTF-8')
                                        : null;
            //$tabRef->cit = true;
            // Make sure the table is valid
            if (!$tabRef->check(false)) {
                $this->errors[] = 'Error checking reference: ';
                //$tabRef->cit=false;
                return false;
            }

            if (($form['person']['references']['status'][$i] == 'new')
                || ($form['person']['references']['status'][$i] == 'loaded')
            ) {
                // Store the table to the database
                if (!$tabRef->store(false)) {
                    $this->errors[] = 'Error saving reference: '.$tabRef->getError();
                    return false;
                }
            } elseif ($form['person']['references']['status'][$i] == 'loaded_deleted') {
                // Delete row from the database
                if (!$tabRef->delete()) {
                    $this->errors[] = 'Error deleting reference: '.$tabRef->getError();
                    return false;

                }
            }
        }

        return true;
    }

    private function save_notes(&$form, $level, $event)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_notes");
        }
        if (!isset($form['person']['notes']) || !is_array($form['person']['notes'])) {
            // no notes
            return true;
        }

        // Notes
        switch ($level) {
            case "relation":	// $tabNot	= Table::getInstance('RelationnotesTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
                $tabNot	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationnotes');
                if ($form['person']['id'] < $form['person']['relations']['id'][0]) {
                    $tabNot->person_id_1	= $form['person']['id'];
                    $tabNot->person_id_2	= $form['person']['relations']['id'][0];
                } else {
                    $tabNot->person_id_2	= $form['person']['id'];
                    $tabNot->person_id_1	= $form['person']['relations']['id'][0];
                }
                break;
            case "person":		// person same as default
            default:		// $tabNot	= Table::getInstance('PersonnotesTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
                $tabNot	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Personnotes');
                $tabNot->person_id   = $form['person']['id'];
                break;
        }

        $tabNot->app_id         	= $form['person']['app_id'];
        $indNotesTable = JoaktreeHelper::getIndNotesTable($tabNot->app_id);

        if ($indNotesTable) {
            // $tabNot2	= Table::getInstance('NotesTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
            $tabNot2	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Notes');
            $tabNot2->app_id       	= $form['person']['app_id'];
        }
        $tabNot->indCitation		= 0; // pascal
        for ($i = 0; $i < count($form['person']['notes']['orderNumber']); $i++) {
            // person
            $tabNot->orderNumber		= $form['person']['notes']['orderNumber'][$i];
            if ($event == 'name') {
                $tabNot->nameOrderNumber	= $form['person']['notes']['objectOrderNumber'][$i];
                $tabNot->eventOrderNumber	= null;
            } elseif ($event == 'event') {
                $tabNot->nameOrderNumber	= null;
                $tabNot->eventOrderNumber	= $form['person']['notes']['objectOrderNumber'][$i];
            } else {
                $tabNot->nameOrderNumber	= null;
                $tabNot->eventOrderNumber	= null;
            }

            if (isset($form['person']['notes']['text'][$i])) {
                $noteText = str_replace(
                    $form['lineEnd'],
                    "&#10;&#13;",
                    htmlspecialchars(
                        $form['person']['notes']['text'][$i],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
            } else {
                $noteText = null;
            }

            if ($indNotesTable) {
                if (isset($form['person']['notes']['note_id'][$i])) {
                    $tabNot->note_id = $form['person']['notes']['note_id'][$i];
                } else {
                    // retreive ID and double check that it is not used
                    $continue = true;
                    $i = 0;
                    while ($continue) {
                        $tmpId = JoaktreeHelper::generateJTId();
                        $i++;

                        if ($this->check($tmpId, 'note')) {
                            $tabNot->note_id = $tmpId;
                            $continue = false;
                            break;
                        }
                        if ($i > 100) {
                            $continue = false;
                            return false;
                        }
                    }
                }

                $tabNot2->id 	 = $tabNot->note_id;
                $tabNot2->value	 = $noteText;
            } else {
                $tabNot->value   = $noteText;
            }

            // Make sure the table is valid
            if (!$tabNot->check()) {
                $this->errors[] = 'Error checking note: ';
                return false;
            }

            if (($form['person']['notes']['status'][$i] == 'new')
                || ($form['person']['notes']['status'][$i] == 'loaded')
            ) {
                // Store the table to the database
                if (!$tabNot->store(false)) {
                    $this->errors[] = 'Error saving note (1): '.$tabNot->getError();
                    return false;
                }
                if ($indNotesTable) {
                    if (!$tabNot2->store(false)) {
                        $this->errors[] = 'Error saving note (2): '.$tabNot2->getError();
                        return false;
                    }
                }
            } elseif ($form['person']['notes']['status'][$i] == 'loaded_deleted') {
                // Delete row from the database
                if ($indNotesTable) {
                    if (!$tabNot2->delete()) {
                        $this->errors[] = 'Error deleting note (2): '.$tabNot2->getError();
                        return false;
                    }
                }
                if (!$tabNot->delete()) {
                    $this->errors[] = 'Error deleting note (1): '.$tabNot->getError();
                    return false;
                }
            }
        }

        return true;
    }

    private function getSex($appId, $personId)
    {
        $query = $this->_db->getquery(true);
        $query->select(' jpn.sex ');
        $query->from(' #__joaktree_persons  jpn ');
        $query->where(' jpn.app_id = :appid');
        $query->where(' jpn.id     = :personid');
        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $personId, \Joomla\Database\ParameterType::STRING);

        $this->_db->setquery($query);
        $sex = $this->_db->loadResult();

        return $sex;
    }

    private function add_person_relations(&$form)
    {
        //$tabRel	= Table::getInstance('RelationsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabRel	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relations');
        // Bind the form fields to the table
        $tabRel->app_id			= $form['person']['app_id'];

        // check family
        $famtmp = explode('!', $form['person']['relations']['family'][0]);
        $relation_id = $famtmp[0];
        $family_id  = $famtmp[1];

        if (!$family_id || $family_id == '0') {
            // retreive ID and double check that it is not used
            $continue = true;
            $i = 0;
            while ($continue) {
                $tmpId = JoaktreeHelper::generateJTId();
                $i++;

                if ($this->check($tmpId, 'family')) {
                    $family_id = $tmpId;
                    $continue = false;
                    break;
                }
                if ($i > 100) {
                    $continue = false;
                    return false;
                }
            }
        }

        if (($form['action'] == 'addparent') || ($form['action'] == 'addchild')) {
            if ($form['action'] == 'addparent') {
                // adding parent
                $tabRel->person_id_1	= $form['person']['relations']['id'][0];
                $tabRel->person_id_2	= $form['person']['id'];
                $sex = $form['person']['sex'];
            } else {
                // adding child
                $tabRel->person_id_1	 = $form['person']['id'];
                $tabRel->person_id_2	 = $form['person']['relations']['id'][0];
                $sex = null;
            }

            //			// check family
            //			$famtmp = explode('!', $form['person']['relations']['family'][0]);
            //			$relation_id = $famtmp[0];
            //			$family_id  = $famtmp[1];

            // If it is a second parent, this uniqueness of family_id has to be checked.
            if (($relation_id != '0') && ($family_id != '0')) {
                $family_id = $this->checkFamilyId($tabRel->app_id, $relation_id, $tabRel->person_id_2, $family_id);
            }

            if (!$sex) {
                $sex = $this->getSex($tabRel->app_id, $tabRel->person_id_2);
            }
            $tabRel->type			= ($sex == 'F') ? 'mother' : 'father';
            $tabRel->subtype		= $form['person']['relations']['relationtype'][0];
            $tabRel->family_id		= $family_id;
            $tabRel->orderNumber_1 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_1, 'parent');
            $tabRel->orderNumber_2 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_2, 'child');
        } elseif ($form['action'] == 'addpartner') {
            if ($form['person']['id'] < $form['person']['relations']['id'][0]) {
                $tabRel->person_id_1	= $form['person']['id'];
                $tabRel->person_id_2	= $form['person']['relations']['id'][0];
            } else {
                $tabRel->person_id_2	= $form['person']['id'];
                $tabRel->person_id_1	= $form['person']['relations']['id'][0];
            }

            //			// check family
            //			$famtmp = explode('!', $form['person']['relations']['family'][0]);
            //			$relation_id = $famtmp[0];
            //			$family_id  = $famtmp[1];

            $tabRel->type			= 'partner';
            $tabRel->subtype		= $form['person']['relations']['partnertype'][0];
            $tabRel->family_id		= $family_id;
            $tabRel->orderNumber_1 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_1, 'partner');
            $tabRel->orderNumber_2 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_2, 'partner');
        }

        // Make sure the table is valid
        if (!$tabRel->check()) {
            $this->errors[] = 'Error checking relation (1): ';
            return false;
            // Else store the table to the database
        } elseif (!$tabRel->store(false)) {
            $this->errors[] = 'Error saving relation (1): '.$tabRel->getError();
            return false;
        }

        $this->relationIds[] = $form['person']['relations']['id'][0];
        $this->relationIds[] = $form['person']['id'];

        // If it is a second parent, this relationship has to be added too.
        if ((isset($relation_id))
           && ($relation_id != '0')
           && (
               ($form['action'] == 'addparent')
              || ($form['action'] == 'addchild')
           )
        ) {
            if ($form['action'] == 'addparent') {
                // update the relation between the two parents
                if ($form['person']['id'] < $relation_id) {
                    $tabRel->person_id_1	= $form['person']['id'];
                    $tabRel->person_id_2	= $relation_id;
                } else {
                    $tabRel->person_id_2	= $form['person']['id'];
                    $tabRel->person_id_1	= $relation_id;
                }
                $tabRel->orderNumber_1 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_1, 'partner');
                $tabRel->orderNumber_2 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_2, 'partner');
                $tabRel->type			= 'partner';
                $tabRel->subtype		= 'spouse';

                // Make sure the table is valid
                if (!$tabRel->check()) {
                    $this->errors[] = 'Error checking relation (2): ';
                    return false;
                    // Else store the table to the database
                } elseif (!$tabRel->store(false)) {
                    $this->errors[] = 'Error saving relation (2): '.$tabRel->getError();
                    return false;
                }
            }

            if ($form['action'] == 'addparent') {
                // update the relation between the child and second parent
                $tabRel->person_id_1	= $form['person']['relations']['id'][0];
            } elseif ($form['action'] == 'addchild') {
                // update the relation between the child and second parent
                $tabRel->person_id_1	= $form['person']['id'];
            }

            $tabRel->person_id_2	= $relation_id;
            $tabRel->orderNumber_1 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_1, 'parent');
            $tabRel->orderNumber_2 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_2, 'child');
            $sex = $this->getSex($form['person']['app_id'], $relation_id);
            $tabRel->type			= ($sex == 'F') ? 'mother' : 'father';
            $tabRel->subtype		= $form['person']['relations']['relationtype'][0];

            // Make sure the table is valid
            if (!$tabRel->check()) {
                $this->errors[] = 'Error checking relation (3): ';
                return false;
                // Else store the table to the database
            } elseif (!$tabRel->store(false)) {
                $this->errors[] = 'Error saving relation (3): '.$tabRel->getError();
                return false;
            }

            $this->relationIds[] = $relation_id;
        }
        // end of storing relationship between two parents / second parent - child

        // If it is a second parent of 1 or more children, this relationship has to be added too.
        if ((isset($family_id)) && ($family_id != '0') && ($form['action'] == 'addpartner')) {
            $children = $this->getChildren($tabRel->app_id, $family_id);

            $tabRel->person_id_2	= $form['person']['id'];
            $sex = $form['person']['sex'];
            $tabRel->type			= ($sex == 'F') ? 'mother' : 'father';
            $tabRel->subtype		= $form['person']['relations']['relationtype'][0];

            foreach ($children as $child) {
                $tabRel->person_id_1	= $child->id;
                $tabRel->orderNumber_1 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_1, 'parent');
                $tabRel->orderNumber_2 	= $this->getOrderNumber($tabRel->app_id, $tabRel->person_id_2, 'child');

                // Make sure the table is valid
                if (!$tabRel->check()) {
                    $this->errors[] = 'Error check relation (4): ';
                    return false;
                    // Else store the table to the database
                } elseif (!$tabRel->store(false)) {
                    $this->errors[] = 'Error saving relation (4): '.$tabRel->getError();
                    return false;
                }

                $this->relationIds[] = $child->id;
            }
        }
        // end of storing relationship between second parent - child

        return true;
    }

    private function update_person_relations(&$form)
    {
        $query = $this->_db->getquery(true);
        $this->relationIds[] = $form['person']['id'];

        // $tabRel	= Table::getInstance('RelationsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabRel	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relations');

        // Bind the form fields to the table
        $tabRel->app_id			= $form['person']['app_id'];

        $numberRelations = count($form['person']['relations']['id']);
        $counter = 1;
        $parentId = '';
        for ($i = 0, $n = $numberRelations; $i < $n; $i++) {
            $this->relationIds[] = $form['person']['relations']['id'][$i];

            if ($form['person']['relations']['status'][$i] == 'loaded_deleted') {
                // Remove this person from the family
                $query->clear();

                // select the affected relationships
                $query->select(' jrn.person_id_1 ');
                $query->select(' jrn.person_id_2 ');
                $query->from(' #__joaktree_relations  jrn ');
                $query->where(' jrn.app_id = :appid');
                $query->where(' jrn.family_id = :familyid');
                $query->where(
                    ' (  jrn.person_id_1 = :personid'
                             . ' OR jrn.person_id_2 = :personid'
                             . ' ) '
                );
                $query->bind(':appid', $form['person']['app_id'], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':familyid', $form['person']['relations']['familyid'][$i], \Joomla\Database\ParameterType::STRING);
                $query->bind(':personid', $this->_db->quote($form['person']['relations']['id'][$i]), \Joomla\Database\ParameterType::STRING);

                $this->_db->setquery($query);
                $pairs = $this->_db->loadObjectList();

                // save the affected relationships for later update
                foreach ($pairs as $pair) {
                    $this->relationIds[] = $pair->person_id_1;
                    $this->relationIds[] = $pair->person_id_2;
                }

                // delete the affected relationships
                $query->clear();
                $query->delete(' #__joaktree_relations ');
                $query->where(' app_id = :appid');
                $query->where(' family_id = :familyid');
                $query->where(
                    ' (  person_id_1 = :personid '
                             . ' OR person_id_2 = :personid'
                             . ' ) '
                );
                $query->bind(':appid', $form['person']['app_id'], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':familyid', $form['person']['relations']['familyid'][$i], \Joomla\Database\ParameterType::STRING);
                $query->bind(':personid', $this->_db->quote($form['person']['relations']['id'][$i]), \Joomla\Database\ParameterType::STRING);

                $this->_db->setquery($query);
                try {
                    $this->_db->execute();
                } catch (\Exception $e) {
                    $this->errors[] = 'Error deleting relation: '.$e->getMessage();
                    return false;
                }

            } else {
                // just update the relationship
                switch ($form['type']) {
                    case "partners"	: 	if ($form['person']['id'] < $form['person']['relations']['id'][$i]) {
                        $tabRel->person_id_1	= $form['person']['id'];
                        $tabRel->person_id_2	= $form['person']['relations']['id'][$i];
                        $tabRel->orderNumber_1	= $counter++;
                    } else {
                        $tabRel->person_id_1	= $form['person']['relations']['id'][$i];
                        $tabRel->person_id_2	= $form['person']['id'];
                        $tabRel->orderNumber_2	= $counter++;
                    }
                        $tabRel->subtype		= $form['person']['relations']['partnertype'][$i];
                        break;
                    case "parents"	: 	$tabRel->person_id_1	= $form['person']['id'];
                        $tabRel->person_id_2	= $form['person']['relations']['id'][$i];
                        $tabRel->orderNumber_1	= $counter++;
                        $tabRel->subtype		= $form['person']['relations']['relationtype'][$i];
                        break;
                    case "children"	:
                    default:
                        if ($parentId != $form['person']['relations']['parentid'][$i]) {
                            // new parent
                            $parentId = $form['person']['relations']['parentid'][$i];
                            $counter  = 1;
                            $this->relationIds[] = $form['person']['relations']['parentid'][$i];
                        }
                        $tabRel->person_id_1	= $form['person']['relations']['id'][$i];
                        $tabRel->person_id_2	= $form['person']['id'];
                        $tabRel->orderNumber_2	= $counter++;
                        $tabRel->subtype		= $form['person']['relations']['relationtype'][$i];
                        break;
                }

                if (!$tabRel->store(false)) {
                    $this->errors[] = 'Error saving relation (5): '.$tabRel->getError();
                    return false;
                }

                if (($form['type'] == 'children') && (!empty($form['person']['relations']['parentid'][$i]))) {
                    // in case of children - update the order of the second parent too
                    $tabRel->person_id_2	= $form['person']['relations']['parentid'][$i];
                    if (!$tabRel->store(false)) {
                        $this->errors[] = 'Error saving relation (6): '.$tabRel->getError();
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function save_person_trees(&$form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_person_trees");
        }
        // Linking person to trees
        $personTrees	= array();

        // $tabTreePerson	= Table::getInstance('TreepersonsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabTreePerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Treepersons');

        // Bind the form fields to the table
        $tabTreePerson->app_id      = $form['person']['app_id'];
        $tabTreePerson->person_id	= $form['person']['id'];

        // new person without relation
        if (!isset($form['person']['relations']['id'][0])) {
            $tabTreePerson->id          = $form['person']['id'].'+'.$form['person']['default_tree_id'];
            $tabTreePerson->tree_id     = $form['person']['default_tree_id'];
            $tabTreePerson->type 	    = 'R';
            $tabTreePerson->lineage		= null;

            // Make sure the table is valid
            if (!$tabTreePerson->check()) {
                $this->setError('Error checking tree-person: ');
                return false;
                // Else store the table to the database
            } elseif (!$tabTreePerson->store(false)) {
                $this->setError('Error saving tree-person: '.$tabTreePerson->getError());
                return false;
            }

            $personTrees[] = $form['person']['default_tree_id'];

        } else {
            // fetch the trees of the relation
            $relationTrees 	= $this->getTrees($form['person']['app_id'], $form['person']['relations']['id'][0]);

            foreach ($relationTrees as $tree) {
                $tabTreePerson->id          = $form['person']['id'].'+'.$tree->tree_id;
                $tabTreePerson->tree_id     = $tree->tree_id;

                // check type of tree
                $indSave = false;
                if ($tree->holds == 'all') {
                    $tabTreePerson->type 	    = 'R';
                    $tabTreePerson->lineage		= null;
                    $indSave 					= true;
                } else {
                    // check type of action
                    switch ($form['action']) {
                        case "addchild":	$tabTreePerson->type 	    = 'C';
                            $tabTreePerson->lineage
                                = $tree->lineage.' '.$form['person']['id'];
                            $indSave 					= true;
                            break;
                        case "addpartner":	$tabTreePerson->type 	    = 'P';
                            $tabTreePerson->lineage		= null;
                            $indSave 					= true;
                            break;
                        case "addparent": 	// continue
                        default:			// do nothing
                            $indSave 					= false;
                            break;
                    }
                }

                if ($indSave) {
                    // Make sure the table is valid
                    if (!$tabTreePerson->check()) {
                        $this->errors[] = 'Error check tree-person: ';
                        return false;
                        // Else store the table to the database
                    } elseif (!$tabTreePerson->store(false)) {
                        $this->errors[] = 'Error saving tree-person: '.$tabTreePerson->getError();
                        return false;
                    }
                    // keep the tree for finding the possible default tree later on.
                    $personTrees[] = $tree->tree_id;
                }
            }
        }

        // find the default tree
        if (!in_array($form['person']['default_tree_id'], $personTrees)) {
            // the given default tree is not a tree for this person, we have to try to find another one.
            $form['person']['default_tree_id'] = (count($personTrees)) ? $personTrees[0] : null;
        } // Else: the given default tree is also a tree for this person: we keep it!

        return true;
    }

    private function save_media(&$form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_media");
        }
        // saving link for 1 picture

        if (!empty($form['person']['media']['path_file'][0])) {
            $docId = ($form['person']['media']['status'][0] == 'new')
                        ? null
                        : $form['person']['media']['id'][0];

            // Store the document
            // $tabMedia	= Table::getInstance('DocumentsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
            $tabMedia	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Documents');

            // Bind the form fields to the table
            $tabMedia->app_id	= $form['person']['app_id'];
            $tabMedia->id		= $docId;

            // file
            $params	= JoaktreeHelper::getJTParams();
            $gedcomroot = $params->get('gedcomDocumentRoot', '');
            $joomlaroot = $params->get('joomlaDocumentRoot', '');
            if (($gedcomroot) && ($joomlaroot)) {
                $tabMedia->file 	= str_replace($joomlaroot, $gedcomroot, $form['person']['media']['path_file'][0]);
            } elseif (($gedcomroot) && (!$joomlaroot)) {
                $tabMedia->file 	= $gedcomroot.$form['person']['media']['path_file'][0];
            } elseif ((!$gedcomroot) && ($joomlaroot)) {
                $tabMedia->file 	= str_replace($joomlaroot, '', $form['person']['media']['path_file'][0]);
            } else {
                $tabMedia->file 	= $form['person']['media']['path_file'][0];
            }
            //			$tabMedia->file 	= str_replace($joomlaroot, $gedcomroot, $form['person']['media']['path_file'][0] );

            $tabMedia->title	= htmlspecialchars($form['person']['media']['title'][0], ENT_QUOTES, 'UTF-8');

            // fileformat
            $format = explode('.', $form['person']['media']['path_file'][0]);
            $format = explode('#', $format[1]); // Joomla 4 add # image width/height
            $tabMedia->fileformat = strtoupper($format[0]);

            // not used yet
            $tabMedia->indCitation 	= 0; // pascal
            $tabMedia->note_id 		= null;
            $tabMedia->note 		= null; //htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

            // Make sure the table is valid
            if (!$tabMedia->check()) {
                $this->errors[] = 'Error checking media: ';
                return false;
                // Else store the table to the database
            } else {
                $docId = $tabMedia->store(false);
                if (!$docId) {
                    $this->errors[] = 'Error saving media: '.$tabMedia->getError();
                    return false;
                }
            }

            // link document to person
            // $tabDocPerson	= Table::getInstance('PersondocumentsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
            $tabDocPerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persondocuments');

            $tabDocPerson->app_id		= $form['person']['app_id'];
            $tabDocPerson->person_id	= $form['person']['id'];
            $tabDocPerson->document_id	= $docId;

            // Make sure the table is valid
            if (!$tabDocPerson->check()) {
                $this->errors[] = 'Error checking document-person: ';
                return false;
                // Else store the table to the database
            } elseif (!$tabDocPerson->store(false)) {
                $this->errors[] = 'Error saving document-person: '.$tabDocPerson->getError();
                return false;
            }
        }

        return true;
    }

    private function save_medialist(&$form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/personform save_medialist");
        }
        // deleting links for pictures

        // $tabDocPerson	= Table::getInstance('PersondocumentsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabDocPerson	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persondocuments');

        $tabDocPerson->app_id		= $form['person']['app_id'];
        $tabDocPerson->person_id	= $form['person']['id'];

        // $tabMedia	= Table::getInstance('DocumentsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $tabMedia	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Documents');

        $tabMedia->app_id	= $form['person']['app_id'];

        // setup query
        $query = $this->_db->getquery(true);

        for ($i = 0; $i < count($form['person']['media']['id']); $i++) {
            $tabDocPerson->document_id	= $form['person']['media']['id'][$i];
            if ($form['person']['media']['status'][$i] == 'loaded_deleted') {
                // Delete row from the database
                if (!$tabDocPerson->delete()) {
                    $this->errors[] = 'Error deleting document-person: '.$tabDocPerson->getError();
                    return false;
                }

                $query->clear();
                $query->select(' count( document_id ) AS number ');
                $query->from(' #__joaktree_person_documents ');
                $query->where(' app_id = :appid');
                $query->where(' document_id = :documentid');
                $query->bind(':appid', $form['person']['app_id'], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':documentid', $form['person']['media']['id'][$i], \Joomla\Database\ParameterType::STRING);

                $this->_db->setquery($query);

                $result = $this->_db->loadResult();

                if (!$result) {
                    $tabMedia->id		= $form['person']['media']['id'][$i];
                    // Delete row from the database
                    if (!$tabMedia->delete()) {
                        $this->errors[] = 'Error deleting media: '.$tabMedia->getError();
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function getTrees($appId, $personId)
    {
        $query = $this->_db->getquery(true);

        $query->select(' jtp.tree_id ');
        $query->select(' jtp.lineage ');
        $query->from(' #__joaktree_tree_persons  jtp ');
        $query->where(' jtp.app_id    = :appid');
        $query->where(' jtp.person_id = :personid');

        $query->select(' jte.holds ');
        $query->innerJoin(
            ' #__joaktree_trees  jte '
                         .' ON (   jte.app_id = jtp.app_id '
                         .'    AND jte.id     = jtp.tree_id '
                         .'    ) '
        );
        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $personId, \Joomla\Database\ParameterType::STRING);

        $this->_db->setquery($query);
        $trees = $this->_db->loadObjectList();

        return $trees;
    }

    private function getOrderNumber($appId, $personId, $type)
    {
        $query = $this->_db->getquery(true);

        switch ($type) {
            case "child":
                $query->select(' MAX(IFNULL(jrn.orderNumber_2, 0)) AS count ');
                $query->where(' jrn.person_id_2 = :personid');
                $query->where(' jrn.type IN ('.$this->_db->quote('father').', '.$this->_db->quote('mother').') ');
                break;
            case "parent":
                $query->select(' MAX(IFNULL(jrn.orderNumber_1, 0)) AS count ');
                $query->where(' jrn.person_id_1 = :personid');
                $query->where(' jrn.type IN ('.$this->_db->quote('father').', '.$this->_db->quote('mother').') ');
                break;
            case "partner":
                $query->select(
                    ' MAX( IF( IFNULL(jrn.orderNumber_1, 0) > IFNULL(jrn.orderNumber_2, 0) '
                              .'        , IFNULL(jrn.orderNumber_1, 0) '
                              .'        , IFNULL(jrn.orderNumber_2, 0) '
                              .'        ) '
                              .'    ) AS count '
                );
                $query->where(
                    ' (  jrn.person_id_1 = :personid'
                             . ' OR jrn.person_id_2 = :personid'
                             . ' ) '
                );
                $query->where(' jrn.type = '.$this->_db->quote('partner').' ');
                break;
            default: break;
        }

        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id      = :appid');

        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $personId, \Joomla\Database\ParameterType::STRING);

        $this->_db->setquery($query);
        $result = $this->_db->loadResult();
        $result = ((int) $result) + 1;

        return $result;
    }

    private function check($tmpId, $type)
    {
        $query = $this->_db->getquery(true);
        $query->select(' 1 ');

        switch ($type) {
            case 'person':
                $query->from(' #__joaktree_persons ');
                $query->where(' id   = :tmpid');
                break;

            case 'note':
                $query->from(' #__joaktree_notes ');
                $query->where(' id   = :tmpid');
                break;

            case 'family':
                $query->from(' #__joaktree_relations ');
                $query->where(' family_id   = :tmpid');
                break;
            default:
                $query->from(' dual ');
                break;
        }
        $query->bind(':personid', $tmpId, \Joomla\Database\ParameterType::STRING);
        $this->_db->setquery($query);
        $result = $this->_db->loadResult();

        // ID is alreadey used -> return false
        // ID is not used in the selected table -> return true
        return ($result) ? false : true;
    }

    private function checkFamilyId($appId, $pid1, $pid2, $familyId)
    {
        $query = $this->_db->getquery(true);

        $query->select(' jrn.family_id ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id      = :appid');
        $query->where(' jrn.family_id   = :familyid');
        $query->where(' jrn.type        = '.$this->_db->quote('partner').' ');
        $query->where(' jrn.person_id_1 IN (:pid1, :pid2) ');
        $query->where(' jrn.person_id_2 NOT IN (:pid1, :pid2) ');

        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':pid1', $pid1, \Joomla\Database\ParameterType::STRING);
        $query->bind(':pid2', $pid2, \Joomla\Database\ParameterType::STRING);
        $query->bind(':familyid', $familyId, \Joomla\Database\ParameterType::INTEGER);

        $this->_db->setquery($query);
        $result = $this->_db->loadResult();

        if ($result) {
            // retreive ID and double check that it is not used
            $continue = true;
            $i = 0;
            while ($continue) {
                $tmpId = JoaktreeHelper::generateJTId();
                $i++;

                if ($this->check($tmpId, 'family')) {
                    $familyId = $tmpId;
                    $continue = false;
                    break;
                }
                if ($i > 100) {
                    $continue = false;
                    return false;
                }
            }
        }

        return $familyId;
    }

    private function getChildren($appId, $familyId)
    {
        $query = $this->_db->getquery(true);

        $query->select(' jrn.person_id_1 AS id ');
        $query->from(' #__joaktree_relations  jrn ');
        $query->where(' jrn.app_id      = :appid');
        $query->where(' jrn.family_id   = :familyid)');
        $query->where(' jrn.type        IN ('.$this->_db->quote('father').', '.$this->_db->quote('mother').') ');

        $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':familyid', $familyId, \Joomla\Database\ParameterType::INTEGER);

        $this->_db->setquery($query);
        $result = $this->_db->loadObjectList();

        return $result;
    }
}
