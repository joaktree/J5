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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;

class LocationModel extends AdminModel
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
    public function getTable($type = 'Locations', $prefix = '', $config = array())
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
        $form = $this->loadForm('com_joaktree.location', 'location', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.location.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getGeocodeResultSet()
    {
        $settings 		= MBJGeocode::getKeys();

        if (isset($settings->geocode)) {
            $geocodeAPIkey	= $settings->geocode.'APIkey';

            if ((empty($settings->geocode))
               || ((!empty($settings->geocode)) && isset($settings->$geocodeAPIkey) && empty($settings->$geocodeAPIkey))
            ) {
                // we cannot execute geocode search
                $resultSet = array();

            } else {
                $data 		= $this->loadFormData();
                $service 	= MBJGeocode::getInstance();
                $status 	= $service->_('findLocation', $data);
                $resultSet 	= $service->_('getResultSet');
            }
        } else {
            $resultSet = array();
        }

        return $resultSet;
    }

    public function save($data)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/location");
        }
        $canDo	= JoaktreeHelper::getActions();
        $msg = Text::_('JTAPPS_MESSAGE_NOSAVE');

        if ($canDo->get('core.create') || $canDo->get('core.edit')) {
            // Initialise variables;
            $table = $this->getTable();
            $key = $table->getKeyName();
            $pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
            $isNew = ($pk > 0) ? false : true;

            // Allow an exception to be thrown.
            try {
                // Bind the data.
                if (!$table->bind($data)) {
                    $table->setError($table->getError());
                    return $msg;
                }

                // Store the data.
                if (!$table->store(true)) {
                    $table->setError($table->getError());
                    return $msg;
                }

                // Clean the cache.
                $this->cleanCache();

            } catch (\Exception $e) {
                $table->setError($e->getMessage());
                return $msg;
            }

            $pkName = $table->getKeyName();
            if (isset($table->$pkName)) {
                $this->setState($this->getName() . '.id', $table->$pkName);
            }
            $this->setState($this->getName() . '.new', $isNew);

            return Text::_('JT_MESSAGE_SAVED');
        }

        return $msg;
    }
}
