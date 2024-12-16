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

namespace Joaktree\Component\Joaktree\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\MVC\Model\AdminModel;	//replace JModelAdmin
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class MapModel extends AdminModel
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	Table	A database object
     * @since	1.6
     */
    public function getTable($type = 'Maps', $prefix = '', $config = array())
    {
        return Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable($type, $prefix, $config);
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
        $form = $this->loadForm('com_joaktree.map', 'map', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.map.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        $item->personName = (!empty($item->person_id))
                             ? $this->getPersonName($item->app_id, $item->person_id)
                             : '';

        $item->descendants 		= ($item->selection == 'tree') ? $item->relations : 0;
        $item->person_relations = ($item->selection == 'person') ? $item->relations : 0;
        if (!is_null($item->excludePersonEvents)) {
            $person_event   	  = (array) json_decode($item->excludePersonEvents);
            $relation_event 	  = (array) json_decode($item->excludeRelationEvents);
            $item->events		  = $this->getIncludeEvents(array_merge($person_event, $relation_event));
        }

        return $item;
    }

    private function getIncludeEvents($excludeCodes = array())
    {
        $query = $this->_db->getQuery(true);

        if (count($excludeCodes)) {
            $exclude = "('NOTE','ENOT','SOUR','ESOU','".implode("','", $excludeCodes)."') ";
        } else {
            $exclude = "('NOTE','ENOT','SOUR','ESOU')";
        }

        $query->select(' code ');
        $query->from(' #__joaktree_display_settings ');
        $query->where(' level IN ( '.$this->_db->quote('person').', '.$this->_db->quote('relation').') ');
        $query->where(' published = true ');
        $query->where(' code NOT IN '.$exclude.' ');

        $this->_db->setQuery($query);
        $includes = $this->_db->loadColumn();
        return $includes;
    }

    private function getPersonName($app_id, $person_id)
    {
        $query = $this->_db->getQuery(true);

        $query->select(' IFNULL( CONCAT_WS('.$this->_db->Quote(' ').' '
                      .'                  , jpn.firstName '
                      .'                  , jpn.namePreposition '
                      .'                  , jpn.familyName '
                      .'                  ) '
                      .'        , '.$this->_db->Quote(Text::_('JTFIELD_PERSON_BUTTON_PERSON')).' '
                      .'        )    AS personName ');
        $query->from(' #__joaktree_persons      jpn ');
        $query->where(' jpn.app_id = :appid');
        $query->where(' jpn.id     = :personid');
        $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $person_id, \Joomla\Database\ParameterType::STRING);

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        return $result;
    }

    public function save($form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/map");
        }
        $canDo	= JoaktreeHelper::getActions();
        $msg = Text::_('JTAPPS_MESSAGE_NOSAVE');
        if ($canDo->get('core.create') || $canDo->get('core.edit')) {
            if (($form['selection'] == 'tree') || ($form['selection'] == 'location')) {
                $form['app_id']  	= $this->getAppId($form['tree']);
                $form['tree_id'] 	= $form['tree'];
                $form['subject']    = $form['familyName'];
                $form['relations']	= $form['descendants'];
                unset($form['tree']);
                unset($form['familyName']);
                unset($form['personName']);
                unset($form['person_id']);
                unset($form['descendants']);
                unset($form['person_relations']);
            }

            if ($form['selection'] == 'person') {
                $form['tree_id']   	= $form['tree'];
                $form['person_id'] 	= $form['person_id'];
                $form['relations']	= $form['person_relations'];
                unset($form['tree']);
                unset($form['personName']);
                unset($form['root_person_id']);
                unset($form['familyName']);
                unset($form['descendants']);
                unset($form['person_relations']);
            }

            if (isset($form['root_person_id'])) {
                $form['person_id'] 	= $form['root_person_id'];
            }
            $form['excludePersonEvents']   = $this->getExcludeEvents('person', $form['events']);
            $form['excludeRelationEvents'] = $this->getExcludeEvents('relation', $form['events']);
            unset($form['events']);

            $ret = parent::save($form);
            Factory::getApplication()->enqueueMessage("Sauvegarde");
            if ($ret) {
                $msg = Text::_('JT_MESSAGE_SAVED');
            }
        }

        return $msg;
    }

    private function getExcludeEvents($type, $includeCodes = array())
    {
        $query = $this->_db->getQuery(true);
        if (is_array($includeCodes) && count($includeCodes)) { // pascal
            $exclude = "('NOTE','ENOT','SOUR','ESOU','".implode("','", $includeCodes)."') ";
        } else {
            $exclude = "('NOTE','ENOT','SOUR','ESOU')";
        }

        $query->select(' code ');
        $query->from(' #__joaktree_display_settings ');
        $query->where(' level = :type');
        $query->where(' published = true ');
        $query->where(' code NOT IN '.$exclude.' ');
        $query->bind(':type', $type, \Joomla\Database\ParameterType::STRING);

        $this->_db->setQuery($query);
        $excludes = $this->_db->loadColumn();

        return json_encode($excludes);
    }

    private function getAppId($tree_id)
    {
        $query = $this->_db->getQuery(true);
        $query->select(' jte.app_id ');
        $query->from(' #__joaktree_trees  jte ');
        $query->where(' jte.id = :treeid');
        $query->bind(':treeid', $tree_id, \Joomla\Database\ParameterType::INTEGER);

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        return $result;
    }
}
