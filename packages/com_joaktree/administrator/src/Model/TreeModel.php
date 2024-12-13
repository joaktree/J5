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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class TreeModel extends AdminModel
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
    public function getTable($type = 'Trees', $prefix = '', $config = array())
    {
        return Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Trees', $prefix, $config);

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
        $form = $this->loadForm('com_joaktree.tree', 'tree', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.tree.data', array());
        if (empty($data)) {
            $data = $this->getItem();
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
        // Initialise variables.
        $pk		= (!empty($pk)) ? $pk : (int) $this->getState($this->getName().'.id');
        if ($pk > 0) {
            // Attempt to load the row.
            $query = $this->_buildquery($pk);
            $this->_db->setQuery($query);
            $item = $this->_db->loadObject();
            if (is_object($item) && property_exists($item, 'params')) {
                $registry = new Registry();
                $registry->loadString($item->params, 'JSON');
                $item->params = $registry->toArray();
            }
            return $item;
        } else {
            return []; // pascal
        }
    }
    public function save($data)
    {
        $canDo	= JoaktreeHelper::getActions();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug 
            Factory::getApplication()->enqueueMessage("Sauvegarde models/jt_tree");
        }
        Factory::getApplication()->enqueueMessage("Catégorie : " . $data['catid']);
        if ($canDo->get('core.create') || $canDo->get('core.edit')) {
            // initialize tables and records
            $table		= $this->getTable();
            $msg	= '';
            // Bind the form fields to the table
            $table->id             	= $data['id'];
            $table->app_id         	= $data['app_id'];
            $table->holds          	= $data['holds'];
            $table->root_person_id 	= $data['root_person_id'];
            $table->access		 	= $data['access'];
            $table->name           	= $data['name'];
            $table->theme_id       	= $data['theme_id'];
            $table->indGendex      	= $data['indGendex'];
            $table->indPersonCount	= $data['indPersonCount'];
            $table->indMarriageCount	= $data['indMarriageCount'];
            $table->robots      	= (empty($data['robots'])) ? null : $data['robots'];
            $table->catid        	= $data['catid'];
            if (!$table->bind($data)) {
                $this->setError($this->$table->getError()); //$this->_db->getErrorMsg());
                Factory::getApplication()->enqueueMessage($this->setError($this->$table->getError()));
                return false;
            }
            // Make sure the table is valid
            if (!$table->check()) {
                $this->setError($this->$table->getError()); //$this->_db->getErrorMsg());
                return false;
            }
            // Bind the rules.
            if (isset($data['rules'])) {
                $actions = array();
                $tmp 	 = array();
                $tmp[0]  = '';
                foreach ($data['rules'] as $action => $identities) {
                    $identities = array_diff($identities, $tmp);
                    $actions[$action] = $identities;
                }
                $rules = new Rules($actions);
                $table->setRules($rules);
            }
            // Store the table to the database
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }
            $msg .= 'Tree stored: ' . $table->name;
            $return = $msg;
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }
        return $return;
    }

    public function delete(&$pks)
    {
        $canDo	= JoaktreeHelper::getActions();
        if ($canDo->get('core.delete')) {
            $pks            = ArrayHelper::toInteger((array) $pks);
            $deleteTable    = array();
            $deleteTable[] = '#__joaktree_maps';
            $deleteTable[] = '#__joaktree_tree_persons';
            foreach ($pks as $i => $pk) {
                foreach ($deleteTable as $query_num => $table) {
                    $query = 'DELETE FROM '.$table.' WHERE tree_id = '.$pk.' ';
                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }
            }
            parent::delete($pks);
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }
    }
    private function _buildquery($pk)
    {
        $query = $this->_db->getQuery(true);
        $query->select(' jte.* ');
        $query->from(' #__joaktree_trees        jte ');
        $query->where(' jte.id = :id');
        $query->select(' japp.title AS appTitle ');
        $query->leftJoin(' #__joaktree_applications japp '
                        .' ON japp.id = jte.app_id ');
        $query->select(' IFNULL( CONCAT_WS('.$this->_db->Quote(' ').' '
                      .'                  , jpn.firstName '
                      .'                  , jpn.namePreposition '
                      .'                  , jpn.familyName '
                      .'                  ) '
                      .'        , '.$this->_db->Quote(Text::_('JTFIELD_PERSON_BUTTON_PERSON')).' '
                      .'        )    AS rootPersonName ');
        $query->leftJoin(' #__joaktree_persons      jpn '
                        .' ON (   jpn.app_id = jte.app_id '
                        .'    AND jpn.id     = jte.root_person_id '
                        .'    ) ');
        $query->bind(':id', $pk, \Joomla\Database\ParameterType::INTEGER);
                        
        return $query;
    }

}
