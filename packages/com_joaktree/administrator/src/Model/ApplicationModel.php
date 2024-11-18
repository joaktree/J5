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

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class ApplicationModel extends AdminModel 
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
    public function getTable($type = 'applications', $prefix = 'Table', $config = array())
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        return Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Applications');

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
        $form = $this->loadForm('com_joaktree.application', 'application', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.application.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function save($form)
    {
        $canDo	= JoaktreeHelper::getActions();
        $msg = Text::_('JTAPPS_MESSAGE_NOSAVE');
        if ($canDo->get('core.create') || $canDo->get('core.edit')) {
            if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
                Factory::getApplication()->enqueueMessage("Sauvegarde models/application");
            }
            $ret = parent::save($form);

            if ($ret) {
                if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
                    Factory::getApplication()->enqueueMessage("Sauvegarde models/application : save form");
                }
                $msg = Text::_('JT_MESSAGE_SAVED');
            }

            // Bind the rules.
            if (isset($form['rules'])) {
                $actions = array();
                $tmp 	 = array();
                $tmp[0]  = '';

                foreach ($data['rules'] as $action => $identities) {
                    $identities = array_diff($identities, $tmp);
                    $actions[$action] = $identities;
                }

                $table = $this->getTable();
                $rules = new Rules($actions);
                $table->setRules($rules);
            }

        }

        return $msg;
    }
}
