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

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class SettingsModel extends ListModel
{
    public $_dataPersName;
    public $_dataPersEvent;
    public $_dataRelaEvent;
    public $_level;
    public $_namePagination = null;
    public $_personPagination = null;
    public $_relationPagination = null;
    public $_total         = null;

    private function _buildquery()
    {
        $query = $this->_db->getQuery(true);

        $query->select(' jds.* ');
        $query->from(' #__joaktree_display_settings jds ');
        $query->where(' jds.level = :level');
        $query->bind(':level', $this->_level, \Joomla\Database\ParameterType::STRING);

        $query->select(' vll1.title AS access_level ');
        $query->leftJoin(
            ' #__viewlevels vll1 '
                        .' ON (vll1.id = jds.access) '
        );

        $query->select(' IFNULL( vll2.title, '.$this->_db->quote('None').') AS access_level_living ');
        $query->leftJoin(
            ' #__viewlevels vll2 '
                        .' ON (vll2.id = jds.accessLiving) '
        );

        $query->select(' IFNULL( vll3.title, '.$this->_db->quote('None').') AS access_level_alttext ');
        $query->leftJoin(
            ' #__viewlevels vll3 '
                        .' ON (vll3.id = jds.altLiving) '
        );

        $query->order(' jds.level ');
        $query->order(' jds.ordering ');

        return $query;
    }

    public function getDataPersName()
    {
        $this->_level = 'name';

        // Lets load the content if it doesn't already exist
        if (empty($this->_dataPersName)) {
            $query = $this->_buildquery();
            $this->_dataPersName = $this->_getList($query);
        }

        return $this->_dataPersName;
    }


    public function getDataPersEvent()
    {
        $this->_level = 'person';

        // Lets load the content if it doesn't already exist
        if (empty($this->_dataPersEvent)) {
            $query = $this->_buildquery();
            $this->_dataPersEvent = $this->_getList($query);
        }

        return $this->_dataPersEvent;
    }


    public function getDataRelaEvent()
    {
        $this->_level = 'relation';

        // Lets load the content if it doesn't already exist
        if (empty($this->_dataRelaEvent)) {
            $query = $this->_buildquery();
            $this->_dataRelaEvent = $this->_getList($query);
        }

        return $this->_dataRelaEvent;
    }

    public function publish()
    {
        $canDo	= JoaktreeHelper::getActions();

        if ($canDo->get('core.edit')) {
            $cids	= Factory::getApplication()->getInput()->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $cid = intval($cid);
                $query = $this->_db->getQuery(true);

                $query->update(' #__joaktree_display_settings ');
                $query->set(' published = !published ');
                $query->where(' id = :id');
                $query->bind(':id', $cid, \Joomla\Database\ParameterType::INTEGER);

                $this->_db->setQuery($query);
                $msg = $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTSETTINGS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function getTotal()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildquery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    public function getNamePagination()
    {
        $this->_level = 'name';

        // Lets load the content if it doesn't already exist
        if (empty($this->_namePagination)) {
            $this->_namePagination = new Pagination($this->getTotal(), 0, 100);
        }

        return $this->_namePagination;
    }

    public function getPersonPagination()
    {
        $this->_level = 'person';

        // Lets load the content if it doesn't already exist
        if (empty($this->_personPagination)) {
            $this->_personPagination = new Pagination($this->getTotal(), 0, 100);
        }

        return $this->_personPagination;
    }

    public function getRelationPagination()
    {
        $this->_level = 'relation';

        // Lets load the content if it doesn't already exist
        if (empty($this->_relationPagination)) {
            $this->_relationPagination = new Pagination($this->getTotal(), 0, 100);
        }

        return $this->_relationPagination;
    }

    private function setOrder($layout)
    {

        if ($layout == 'personname') {
            $where = ' level = '.$this->_db->quote('name').' ';
        } elseif ($layout == 'personevent') {
            $where = ' level = '.$this->_db->quote('person').' ';
        } elseif ($layout == 'relationevent') {
            $where = ' level = '.$this->_db->quote('relation').' ';
        } else {
            $where = ' level = '.$this->_db->quote('person').' ';
        }

        // $settings 	= Table::getInstance('DisplaysettingsTable','Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $settings = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Displaysettings');
        $jtids		= Factory::getApplication()->getInput()->get('jtid', null, 'array');

        $query = $this->_db->getQuery(true);
        $i = 0;

        foreach ($jtids as $jtid) {
            $query->clear();
            $i++;

            $query->update(' #__joaktree_display_settings ');
            $query->set(' ordering = '.$i.' ');
            $query->where(' id = :id');
            $query->where(' '.$where.' ');
            $query->bind(':id', $jtid, \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setQuery($query);

            if (!$this->_db->execute()) { //$this->_db->query()) {
                Factory::getApplication()->enqueueMessage('500' . $this->$table->getError(), 'error'); //$this->_db->getErrorMsg() );
            }
        }

        $settings->reorder($where);
        return '';
    }

    public function save($layout)
    {
        $canDo	= JoaktreeHelper::getActions();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde models/jt_settings");
        }
        if ($canDo->get('core.edit')) {
            // initialize tables and records
            // $row   	= Table::getInstance('DisplaysettingsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
            $row = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Displaysettings');

            // get the input object
            $input = Factory::getApplication()->getInput();

            $cids	= $input->get('cid', null, 'array');
            if (!is_null($cids)) {
                // We are only doing it when we have items to be updated
                if ((count($cids) > 0) and ($cids[0] > 0)) {
                    for ($i = 0, $n = count($cids); $i < $n; $i++) {
                        // Bind the form fields to the table
                        $row->id		= intval($cids[$i]);

                        $tmp 			= $input->get('access'.$row->id, null, 'string');
                        $tmp 			= (int) substr($tmp, 0, 3);
                        $row->access	= $tmp;

                        $tmp 			= $input->get('accessLiving'.$row->id, null, 'string');
                        $tmp 			= (int) substr($tmp, 0, 3);
                        $row->accessLiving	= $tmp;

                        $tmp 			= $input->get('altLiving'.$row->id, null, 'string');
                        $tmp 			= (int) substr($tmp, 0, 3);
                        $row->altLiving	= $tmp;

                        //$code 			= $input->get( 'code'.$row->id, null, 'string' );

                        // Make sure the table is valid
                        try {
                            $row->check();
                        } catch (\Exception $e) {
                            $row->$this->setError($e->getMessage());
                            return false;
                        }

                        // Store the table to the database
                        try {
                            $row->store();
                        } catch (\Exception $e) {
                            $row->$this->setError($e->getMessage());
                            return false;
                        }

                        //					$retmsg .= $model->store($post, $code).';&nbsp;';
                    }
                }
            }

            $this->setOrder($layout);



            $return = '';
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

}
