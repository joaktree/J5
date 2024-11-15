<?php
/**
 * Joomla! component Joaktree
 * file		jt_theme modelAdmin - jt_theme.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */

namespace Joaktree\Component\Joaktree\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\MVC\Model\AdminModel;	//replace JModelAdmin
use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\Filesystem\File;		//replace JFile
use Joomla\Filesystem\Folder; 	//replace JFolder
use Joomla\Filesystem\Path;	//replace JPath
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

//jimport('joomla.application.component.modeladmin');

class ThemeModel extends AdminModel
{
    protected function canDelete($record)
    {
        if ($record->home) {
            // default record cannot be deleted
            return false;
        } else {
            return parent::canDelete($record);
        }
    }

    public function delete(&$id)
    {
        // retrieve the default value
        $query = $this->_db->getQuery(true);
        $query->select(' home ');
        $query->from(' #__joaktree_themes ');
        $query->where(' id   = '.(int) $id.' ');
        $query->where(' home = 1 ');

        $this->_db->setQuery($query);
        $ret = $this->_db->loadResult();

        if ($ret) {
            // Value is default
            return false;
        } else {
            $ret = $this->deleteSource($id);
            return parent::delete($id);
        }

    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	Table	A database object
     * @since	1.6
     */
    public function getTable($type = 'Themes', $prefix = '', $config = array())
    {
        return Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable($type, $prefix, $config);

        // return Table::getInstance($type, 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\', $config);
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
        $form = $this->loadForm('com_joaktree.theme', 'theme', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.theme.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem();

        if ($item->id) {
            $item->sourcepath	= $this->getSourcePath($item->id);
            $item->source 		= $this->getSource($item->sourcepath);
        }

        return $item;
    }

    public function save($form)
    {
        $canDo	= JoaktreeHelper::getActions();
        Factory::getApplication()->enqueueMessage("Sauvegarde models/jt_theme");
        if ($canDo->get('core.create') || $canDo->get('core.edit')) {
            if (!isset($form['name'])) {
                $form['name'] = isset($form['newname']) ? $form['newname'] : null;
            }

            if (isset($form['source'])) {
                return $this->saveSource($form);
            } else {

                if (isset($form['theme'])) {
                    $this->createSource($form);
                }

                return parent::save($form);
            }
        }

    }

    private function getSourceBase()
    {
        $base = JPATH_SITE.'/components/com_joaktree/themes/';
        return $base;
    }

    private function getSourcePath($id)
    {
        $theme = JoaktreeHelper::getThemeName($id);

        $filePath	= Path::clean($this->getSourceBase().$theme.'/theme.css');

        if (@is_file($filePath)) {
            return $filePath;
        } else {
            return null;
        }
    }

    public function getSource($filePath)
    {
        //return File::read($filePath);
        return  file_get_contents($filePath);
    }

    public function saveSource($form)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/jt_theme savesource");
        }
        // Try to make the file writeable.
        if (Path::isOwner($form['sourcepath']) && !Path::setPermissions($form['sourcepath'], '0755')) {
            $this->setError(Text::_('JTTHEME_ERROR_SOURCE_FILE_NOT_WRITABLE'));
            return false;
        }

        $ret = File::write($form['sourcepath'], $form['source']);

        // Try to make the file unwriteable.
        if (Path::isOwner($form['sourcepath']) && !Path::setPermissions($form['sourcepath'], '0555')) {
            $this->setError(Text::_('JTTHEME_ERROR_SOURCE_FILE_NOT_UNWRITABLE'));
            return false;
        } elseif (!$ret) {
            $this->setError(Text::sprintf('JTTHEME_ERROR_FAILED_TO_SAVE_FILENAME', $form['sourcepath']));
            return false;
        }

        return true;
    }

    public function createSource($form)
    {
        $theme 		 = JoaktreeHelper::getThemeName($form['theme']);
        $source		 = Path::clean($this->getSourceBase().$theme);
        $destination = Path::clean($this->getSourceBase().$form['newname']);

        return Folder::copy($source, $destination);
    }

    public function deleteSource($id)
    {
        $theme 		 = JoaktreeHelper::getThemeName($id);
        $source		 = Path::clean($this->getSourceBase().$theme);

        return Folder::delete($source);
    }
}
