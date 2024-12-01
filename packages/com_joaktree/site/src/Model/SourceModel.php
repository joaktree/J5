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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class SourceModel extends FormModel
{
    public function getApplicationId()
    {
        return JoaktreeHelper::getApplicationId();
    }

    public function getSourceId()
    {
        return JoaktreeHelper::getSourceId();
    }

    public function getAction()
    {
        return JoaktreeHelper::getAction();
    }

    public static function getAccess()
    {
        return JoaktreeHelper::getAccessGedCom();
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
        $form = $this->loadForm('com_joaktree.source', 'source', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = Factory::getApplication()->getUserState('com_joaktree.edit.source.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem()
    {
        $query = $this->_db->getquery(true);

        // select from sources
        $query->select(' jse.app_id ');
        $query->select(' jse.id ');
        $query->select(' jse.repo_id ');
        $query->select(' jse.title ');
        $query->select(' jse.author ');
        $query->select(' jse.publication ');
        $query->select(' jse.information ');
        $query->select(' jse.abbr ');
        $query->select(' jse.media ');
        $query->select(' jse.note ');
        $query->select(' jse.www ');
        $query->from(' #__joaktree_sources jse ');

        // Get the WHERE, GROUP BY and ORDER BY clauses for the query
        $wheres      	= $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }

        $this->_db->setquery($query);
        $item = $this->_db->loadObject();

        if (!is_object($item)) {
            $item = new \stdClass();
            $item->app_id = intval($this->getApplicationId());
        } else {
            $item->title		= htmlspecialchars_decode($item->title, ENT_QUOTES);
            $item->author		= htmlspecialchars_decode($item->author, ENT_QUOTES);
            $item->publication	= htmlspecialchars_decode($item->publication, ENT_QUOTES);
            $item->information	= htmlspecialchars_decode($item->information, ENT_QUOTES);
            $item->abbr	        = htmlspecialchars_decode($item->abbr, ENT_QUOTES);
            $item->note     	= htmlspecialchars_decode($item->note, ENT_QUOTES);
            $item->note         = str_replace('&#10;&#13;', PHP_EOL, $item->note);
            $item->media     	= htmlspecialchars_decode($item->media, ENT_QUOTES);
            $item->www      	= htmlspecialchars_decode($item->www, ENT_QUOTES);
        }

        $item->app_repo_id = $item->app_id.'!'.((isset($item->repo_id)) ? $item->repo_id : null);
        return $item;
    }

    private function _buildContentWhere()
    {
        $appId     	= intval($this->getApplicationId());
        $sourceId     = $this->getSourceId();
        $where = array();

        if ($appId) {
            $where[] = ' jse.app_id = '.$appId.' ';
        }

        if ($sourceId) {
            $where[] = ' jse.id = '.$this->_db->quote($sourceId).' ';
        }
        return $where;
    }

    public function save($form)
    {
        Factory::getApplication()->enqueueMessage("Sauvegarde models/source save");
        // Load the parameters.
        $params	= ComponentHelper::getParams('com_joaktree');
        if ($params->get('siteedit', 1)) {
            $canDo	= JoaktreeHelper::getActions(false);
        }

        if ((is_object($canDo)) &&
                (
                    $canDo->get('core.create')
                || $canDo->get('core.edit')
                )
        ) {

            //$table	= Table::getInstance('SourcesTable', $prefix, $config);;
            $table	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Sources');

            // Bind the form fields to the table
            if (empty($form['id'])) {
                // retreive ID and double check that it is not used
                $continue = true;
                $i = 0;
                while ($continue) {
                    $tmpId = JoaktreeHelper::generateJTId();
                    $i++;

                    if ($this->check($tmpId)) {
                        $form['id'] = $tmpId;
                        $continue = false;
                        break;
                    }
                    if ($i > 100) {
                        $continue = false;
                        return false;
                    }
                }

                $status		= 'new';
                $crud		= 'C';
            } else {
                $status		= 'changed';
                $crud		= 'U';
            }

            $table->id			= $form['id'];
            $table->app_id		= $form['app_id'];
            $table->title		= htmlspecialchars($form['title'], ENT_QUOTES, 'UTF-8');
            $table->author		= htmlspecialchars($form['author'], ENT_QUOTES, 'UTF-8');
            $table->publication	= htmlspecialchars($form['publication'], ENT_QUOTES, 'UTF-8');
            $table->information	= htmlspecialchars($form['information'], ENT_QUOTES, 'UTF-8');
            $table->abbr    	= htmlspecialchars($form['abbr'], ENT_QUOTES, 'UTF-8');
            $table->media    	= htmlspecialchars($form['media'], ENT_QUOTES, 'UTF-8');
            $table->note    	= htmlspecialchars($form['note'], ENT_QUOTES, 'UTF-8');
            $table->www     	= htmlspecialchars($form['www'], ENT_QUOTES, 'UTF-8');

            // repo id
            $tmp = explode('!', $form['app_repo_id']);
            if (count($tmp) == 2) {
                $table->repo_id = $tmp[1];
            } else {
                $table->repo_id = null;
            }

            // Make sure the data is valid
            if (!$table->check()) {
                return false;
            }

            // Store the table to the database
            if (!$table->store(true)) {
                $this->setError($this->$table->getError()); //_db->getErrorMsg());
                return false;
            }

            // log
            // $log	= Table::getInstance('LogsTable', $prefix, $config);
            $log	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');

            $log->app_id		= $form['app_id'];
            $log->object_id		= $form['id'];
            $log->object		= 'sour';
            $log->log($crud);

            //return
            $ret				= new \stdClass();
            $ret->object		= 'source';
            $ret->app_id		= $form['app_id'];
            $ret->object_id		= $form['id'];
            $ret->status		= $status;
            $statusObj			= $this->get('returnObject');
            $ret->action		= $statusObj->action;
            $return				= base64_encode(json_encode($ret));

        } else {
            $return = false;
        }

        return $return;
    }

    public function delete($appId, $sourceId)
    {
        // Load the parameters.
        $params	= ComponentHelper::getParams('com_joaktree');
        if ($params->get('siteedit', 1)) {
            $canDo	= JoaktreeHelper::getActions(false);
        }
        if ((is_object($canDo)) && ($canDo->get('core.delete'))) {

            //$table	= Table::getInstance('SourcesTable', $prefix, $config);
            $table	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Sources');

            //$table->id         = $repoId; RRG 26/01/2017 d'où vient $repoId ?
            $table->id         = $sourceId;
            $table->app_id     = $appId;

            // retrieve row - for display later on
            if (!$table->load()) {
                $this->setError($this->$table->getError()); //_db->getErrorMsg());
                return false;
            }
            // Delete the row from the database
            if (!$table->delete()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }

            // log
            // $log	= Table::getInstance('LogsTable', $prefix, $config);
            $log	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logs');
            $log->app_id		= $appId;
            $log->object_id		= $sourceId;
            $log->object		= 'sour';
            $log->log('D');
            //$logDel	= Table::getInstance('LogremovalsTable', $prefix, $config);
            $logDel	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Logsremovals');
            $logDel->app_id		= $appId;
            $logDel->object_id	= $sourceId;
            $logDel->object		= 'sour';
            $logDel->description = $table->title;
            if ($logDel->check()) {
                $logDel->store();
            }

            //return
            $return				= $table->title;

        } else {
            $return = false;
        }
        return $return;
    }

    private function check($tmpId)
    {
        $query = $this->_db->getquery(true);
        $query->select(' 1 ');
        $query->from(' #__joaktree_sources ');
        $query->where(' id   = '.$this->_db->quote($tmpId).' ');

        $this->_db->setquery($query);
        $result = $this->_db->loadResult();

        // ID is alreadey used -> return false
        // ID is not used in the selected table -> return true
        return ($result) ? false : true;
    }
}
