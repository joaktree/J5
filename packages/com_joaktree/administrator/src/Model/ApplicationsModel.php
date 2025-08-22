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
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomfile2;

class ApplicationsModel extends ListModel
{
    public $_data;
    public $_pagination 	= null;
    public $_total         = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $app = Factory::getApplication();

        $context			= 'com_joaktree.applications.list.';
        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    private function _buildquery()
    {
        $query = $this->_db->getQuery(true);

        $query->select(' japp.id ');
        $query->select(' japp.title ');
        $query->select(' japp.description ');
        $query->select(' japp.programName ');
        $query->from(' #__joaktree_applications  japp ');

        $query->select(' COUNT(jpn.id) AS NumberOfPersons ');
        $query->leftJoin(
            ' #__joaktree_persons       jpn '
                        .' ON ( jpn.app_id = japp.id ) '
        );

        // WHERE, GROUP BY and ORDER BY clauses for the query
        $wheres     =  $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }
        $query->group(' japp.id ');
        $query->group(' japp.title ');
        $query->group(' japp.description ');
        $query->group(' japp.programName ');
        $query->order(' '.$this->_buildContentOrderBy().' ');

        return $query;
    }

    private function _buildContentWhere()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.applications.list.';
        $search			= $app->getUserStateFromRequest($context.'search', 'search', '', 'string');
        $search			= strtolower($search);

        $where = array();

        if ($search) {
            $where[] =   'LOWER(japp.title) LIKE '.$this->_db->Quote('%'.$search.'%').' '
                        .'OR LOWER(japp.description) LIKE '.$this->_db->Quote('%'.$search.'%').' '
                        .'OR LOWER(japp.programName) LIKE '.$this->_db->Quote('%'.$search.'%').' ';
        }

        return $where;
    }

    private function _buildContentOrderBy()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.applications.list.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'japp.id', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order) {
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' ';
        } else {
            $orderby 	= ' japp.id '.$filter_order_Dir.' ';
        }

        return $orderby;
    }

    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildquery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
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

    public function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    /*
    ** function for processing the gedcom file
    */
    public function getGedcom()
    {
        $canDo	= JoaktreeHelper::getActions();
        $localMsg = '';
        $this->input = Factory::getApplication()->getInput();
        if (($canDo->get('core.create')) && ($canDo->get('core.edit'))) {
            $cids = $this->input->get('cid', null, 'array');

            if (count($cids) == 0) {
                // no applications are selected
                $return = Text::_('JTGEDCOM_MESSAGE_NOAPPLICATIONS');

            } else {

                foreach ($cids as $cid_num => $app_id) {
                    $procObject = new processObject();

                    $current = date('h:i:s');
                    //$current = strftime('%H:%M:%S');
                    $procObject->msg = Text::_('JTPROCESS_START').':'.$current;

                    $procObject->id = (int) $app_id;
                    $procObject->msg .= '<br />'.Text::sprintf('JTPROCESS_START_MSG', $procObject->id);

                    $params         = JoaktreeHelper::getJTParams($procObject->id);
                    $processStep  	= (int) $params->get('processStep', 9);

                    // as of version 1.2: new method
                    if ($processStep == 4) {
                        $gedcomfile = new Gedcomfile2($procObject);
                        $procObject = $gedcomfile->process('person');
                    }

                    if ($processStep == 5) {
                        $gedcomfile = new Gedcomfile2($procObject);
                        $procObject = $gedcomfile->process('family');
                        $ret = Gedcomfile2::clear_gedcom();
                        if ($ret) {
                            $procObject->msg .= '<br />'.$ret;
                        }
                    }

                    if ($processStep == 6) {
                        $gedcomfile = new Gedcomfile2($procObject);
                        $procObject = $gedcomfile->process('source');
                    }

                    if ($processStep == 7) {
                        $gedcomfile = new Gedcomfile2($procObject);
                        $procObject = $gedcomfile->process('repository');
                        $procObject = $gedcomfile->process('note');
                        $procObject = $gedcomfile->process('document');
                    }

                    if ($processStep == 9) {
                        $gedcomfile = new Gedcomfile2($procObject);
                        $procObject = $gedcomfile->process('all');
                        $ret = Gedcomfile2::clear_gedcom();
                        if ($ret) {
                            $procObject->msg .= '<br />'.$ret;
                        }
                    }

                    if ($procObject->status != 'error') {
                        $this->setInitialChar();
                        $this->setLastUpdateDateTime();

                        if ($procObject->persons > 0) {
                            $procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_PERSONS', $procObject->persons);
                        }
                        if ($procObject->families > 0) {
                            $procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_FAMILIES', $procObject->families);
                        }
                        if ($procObject->sources > 0) {
                            $procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_SOURCES', $procObject->sources);
                        }
                        if ($procObject->repos > 0) {
                            $procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_REPOS', $procObject->repos);
                        }
                        if ($procObject->notes > 0) {

                            $procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOTES', $procObject->notes);
                        }
                        if ($procObject->unknown > 0) {
                            $procObject->msg .= '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_UNKNOWN', $procObject->unknown);
                        }

                        $procObject->msg .= '<br />'.Text::sprintf('JTPROCESS_END_MSG', $procObject->id);
                    } else {
                        $return = $procObject->msg;
                    }

                    $current = date('h:i:s');
                    //$current = strftime('%H:%M:%S');
                    $procObject->msg .= '<br />'.Text::_('JTPROCESS_END').':'.$current;

                    $localMsg .= $procObject->msg;
                }

                $return = $localMsg;
            }
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    /*
    ** function for clearing the tables for a specfic gedcom file, with exception
    ** of the admin table
    */
    public function clearGedCom()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.delete')) {
            $cids = $this->input->get('cid', null, 'array');
            $msg = '';

            foreach ($cids as $cid_num => $app_id) {
                $app_id	= (int) $app_id;
                $msg   .= '+'.Gedcomfile2::deleteGedcomData($app_id, false);
            }

            $return = $msg;

        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    /*
    ** function for clearing the tables for a specfic gedcom file
    */
    public function deleteGedCom()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.delete')) {
            $cids = $this->input->get('cid', null, 'array');
            $msg = '';

            foreach ($cids as $cid_num => $app_id) {
                $app_id	= (int) $app_id;
                $msg   .= '+'.Gedcomfile2::deleteGedcomData($app_id, true);
                JoaktreeHelper::addLog('Delete Data : '.$msg.' : '.$app_id);
            }
            $return = $msg;

        } else {
            $return = Text::_('JT_NOTAUTHORISED');
            JoaktreeHelper::addLog('Delete Data : '.$return);
        }

        return $return;
    }

    private function setLastUpdateDateTime()
    {
        $query = $this->_db->getQuery(true);
        $query->update(' #__joaktree_registry_items ');
        $query->set(' value  = NOW() ');
        $query->where(' regkey = '.$this->_db->quote('LAST_UPDATE_DATETIME').' ');

        $this->_db->setQuery($query);
        $this->_db->execute(); //$this->_db->query();
    }

    private function setInitialChar()
    {
        // update register with 0, meaning NO "initial character" present
        $query = $this->_db->getQuery(true);
        $query->update(' #__joaktree_registry_items ');
        $query->set(' value  = '.$this->_db->quote('0').' ');
        $query->where(' regkey = '.$this->_db->quote('INITIAL_CHAR').' ');

        $this->_db->setQuery($query);
        $this->_db->execute(); //$this->_db->query();
    }
}
class processObject
{
    public $id			= null;
    public $start		= null;
    public $current	= null;
    public $end		= null;
    public $cursor		= 0;
    public $persons	= 0;
    public $families	= 0;
    public $sources	= 0;
    public $repos		= 0;
    public $notes		= 0;
    public $docs  		= 0;
    public $unknown	= 0;
    public $japp_ids	= null;
    public $status		= 'new';
    public $msg		= null;
}

