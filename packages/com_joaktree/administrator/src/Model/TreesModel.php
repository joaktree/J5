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
use Joaktree\Component\Joaktree\Administrator\Helper\Trees;

class processObject
{
    public $id			= null;
    public $japp_ids	= null;
    public $treeIds	= null;
    public $status		= null;
    public $msg		= null;
    public $start		= null;
    public $current	= null;
    public $end		= null;
}


class TreesModel extends ListModel
{
    public $_data;
    public $_pagination = null;
    public $_total         = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $app = Factory::getApplication();
        $context    = 'com_joaktree.trees.list.';
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

        // select from trees
        $query->select(' jte.id ');
        $query->select(' jte.app_id ');
        $query->select(' jte.holds ');
        $query->select(' jte.published ');
        $query->select(' jte.access ');
        $query->select(' jte.name ');
        $query->select(' jte.indGendex ');
        $query->select(' jte.root_person_id ');
        $query->from(' #__joaktree_trees        jte ');

        // select from applications
        $query->select(' japp.title     AS appTitle ');
        $query->leftJoin(
            ' #__joaktree_applications japp '
                        .' ON (japp.id = jte.app_id) '
        );

        // select from persons
        $query->select(' jpn.firstName ');
        $query->select(JoaktreeHelper::getConcatenatedFamilyName().' AS familyName ');
        $query->leftJoin(
            ' #__joaktree_persons      jpn '
                        .' ON (   jpn.app_id  = jte.app_id '
                        .'    AND jpn.id      = jte.root_person_id '
                        .'    ) '
        );

        // select from access
        $query->select(' vll.title      AS access_level ');
        $query->leftJoin(
            ' #__viewlevels            vll '
                        .' ON ( vll.id = jte.access ) '
        );

        // select from themes
        $query->select(' jth.name       AS theme ');
        $query->leftJoin(
            ' #__joaktree_themes       jth '
                        .' ON ( jth.id = jte.theme_id ) '
        );

        // count number of persons in tree
        $query->select(' COUNT(jtp.id)  AS numberOfPersons ');
        $query->leftJoin(
            ' #__joaktree_tree_persons jtp '
                        .' ON ( jtp.tree_id = jte.id ) '
        );

        // Get the WHERE, ORDER BY and GROUP BY clauses for the query
        $wheres     =  $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }

        $query->group(' jte.id ');
        $query->group(' jte.app_id ');
        $query->group(' jte.holds ');
        $query->group(' jte.published ');
        $query->group(' jte.access ');
        $query->group(' jte.name ');
        $query->group(' jte.indGendex ');
        $query->group(' jte.root_person_id ');
        $query->group(' japp.title ');
        $query->group(' jpn.firstName ');
        $query->group(JoaktreeHelper::getConcatenatedFamilyName());
        $query->group(' vll.title ');
        $query->group(' jth.name ');
        $query->group(' jpn.namePreposition	')	;
        $orderby	=  $this->_buildContentOrderBy();
        $query->order(' '.$orderby.' ');

        return $query;
    }

    private function _buildContentWhere()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.trees.list.';
        $filter_state	= $app->getUserStateFromRequest($context.'filter_state', 'filter_state', '', 'cmd');
        $filter_apptitle = $app->getUserStateFromRequest($context.'filter_apptitle', 'filter_apptitle', 0, 'int');
        $filter_gendex 	= $app->getUserStateFromRequest($context.'filter_gendex', 'filter_gendex', 0, 'int');
        $search			= $app->getUserStateFromRequest($context.'search', 'search', '', 'string');
        $search			= strtolower($search);

        $wheres = array();

        if ($search) {
            $wheres[] = 'LOWER(jte.name) LIKE '.$this->_db->Quote('%'.$search.'%');
        }

        if (!($filter_state === '')) {
            $wheres[] = 'jte.published = '.(int) $filter_state;
        }

        if ($filter_apptitle != 0) {
            $wheres[] = 'jte.app_id = ' . $filter_apptitle;
        }

        if ($filter_gendex != 0) {
            $wheres[] = 'jte.indGendex = ' . $filter_gendex;
        }

        return $wheres;
    }

    private function _buildContentOrderBy()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.trees.list.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jte.id', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order) {
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' ';
        } else {
            $orderby 	= ' jte.id '.$filter_order_Dir.' ';
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

    public function getAction()
    {
        $tmp = Factory::getApplication()->getInput()->get('action');
        return ($tmp == 'assign') ? $tmp : null;
    }

    public function getTreeId()
    {
        $tmp = Factory::getApplication()->getInput()->get('treeId', null, 'int');

        if (empty($tmp)) {
            $query = $this->_db->getQuery(true);

            $query->select(' MAX( id ) ');
            $query->from(' #__joaktree_trees ');

            $this->_db->setQuery($query);
            $tmp = $this->_db->loadResult();
        }
        return (!empty($tmp)) ? (int) $tmp : null;
    }

    public function publish()
    {
        $canDo	= JoaktreeHelper::getActions();

        if ($canDo->get('core.edit')) {
            $cids = Factory::getApplication()->getInput()->get('cid', null, 'array');
            $apps = array();

            foreach ($cids as $cid_num => $cid) {
                $cid = intval($cid);
                $query = $this->_db->getQuery(true);

                $query->update(' #__joaktree_trees ');
                $query->set(' published = !published ');
                $query->where(' id = :id');
                $query->bind(':id', $cid, \Joomla\Database\ParameterType::INTEGER);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $msg = Text::_('JTFAMTREE_MESSAGE_UPDATED');

            $return = $msg;
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    // ============================ //
    private function setProcessObject($procObject)
    {
        $jt_registry	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Registryitems');
        // create a registry item
        if (isset($procObject->msg)) {
            $procObject->msg 		= substr($procObject->msg, 0, 1500);
        }
        $jt_registry->regkey 	= 'ASSIGNFT_OBJECT';
        $jt_registry->value  	= json_encode($procObject);
        $jt_registry->storeUK();
    }

    private function deleteProcessObject()
    {
        $jt_registry	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Registryitems');
        // delete a registry item
        $jt_registry->deleteUK('ASSIGNFT_OBJECT');
    }

    private function initProcessObject($japp_ids = null)
    {
        // create new object
        if (is_array($japp_ids)) {
            $procObject 			= $this->getProcessObject();
            $save_app_id			= $procObject->id;
            $app_ids				= $japp_ids;
        } else {
            $procObject 			= new processObject();

            $tmp     = Factory::getApplication()->getInput()->get('treeId', null, 'string');
            $treeIds = explode('!', $tmp);
            $tmp	 = array_pop($treeIds);

            $query = $this->_db->getQuery(true);
            $query->select(' DISTINCT app_id ');
            $query->from(' #__joaktree_trees ');
            $query->where(' id IN ( '.implode(',', $treeIds).') ');

            $this->_db->setQuery($query);
            $app_ids = $this->_db->loadColumn();
            $procObject->treeIds 	= $treeIds;
        }

        // first delete any existing record
        $this->deleteProcessObject();

        $procObject->id 		= array_shift($app_ids);
        $procObject->japp_ids	= $app_ids;

        if (!$procObject->id) {
            $procObject->status = 'end';
            // we are totally done
            $procObject->current = date('h:i:s');
            //$procObject->current = strftime('%H:%M:%S');
            $procObject->end = $procObject->current;

            $this->deleteProcessObject();
        } else {
            $procObject->status = 'start';
            $procObject->start = date('h:i:s');
            //$procObject->start  = strftime('%H:%M:%S');
            $procObject->msg    = Text::sprintf('JTPROCESS_START_MSG', $procObject->id);
            // save it
            $this->setProcessObject($procObject);
        }

        return $procObject;
    }


    private function getProcessObject()
    {
        $jt_registry	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Registryitems');
        // retrieve registry item
        $jt_registry->loadUK('ASSIGNFT_OBJECT');
        $procObject = json_decode($jt_registry->value);
        unset($procObject->msg);
        return $procObject;
    }
    /*
    ** function for assigning family tree
    ** status: new			- New process. Nothing has happened yet.
    **         progress		- Reading through the GedCom file
    **         endload		- Finished loading GedCom file
    **         endpat		- Finished setting patronyms
    **         endrel		- Finished setting relation indicators
    **         start		- Start assigning family trees
    **         starttree	- Start assigning one tree
    **         progtree		- Processing family trees (setting up link between persons and trees)
    **         endtree		- Finished assigning family trees
    **         treedef_1 	- Finished setting up default trees 1 (1 tree per person)
    **         treedef_2 	- Finished setting up default trees 2 (1 tree per person)
    **         treedef_3 	- Finished setting up default trees 3 (1 father tree per person)
    **         treedef_4 	- Finished setting up default trees 4 (1 mother tree per person)
    **         treedef_5 	- Finished setting up default trees 5 (1 partner tree per person)
    **         treedef_6 	- Finished setting up default trees 6 (lowest tree)
    **         endtreedef 	- Finished setting up default trees 7 (lowest tree)
    **         end			- Finished full process
    **         error		- An error has occured
    */
    public function getAssignFamilyTree()
    {
        $canDo	= JoaktreeHelper::getActions();

        $tmp     = Factory::getApplication()->getInput()->get('init', null, 'int');
        $procObject =  ((int) $tmp == 1) ? $this->initProcessObject() : $this->getProcessObject();

        if (($canDo->get('core.create')) && ($canDo->get('core.edit'))) {

            switch ($procObject->status) {
                case 'start':		// continue
                case 'starttree':	// continue
                case 'progtree':	// continue
                case 'endtree':		// continue
                case 'treedef_1':	// continue
                case 'treedef_2':	// continue
                case 'treedef_3':	// continue
                case 'treedef_4':	// continue
                case 'treedef_5':	// continue
                case 'treedef_6':	// continue
                    $familyTree = new Trees($procObject); // treesHelper
                    $resObject 	= $familyTree->assignFamilyTree();

                    $procObject->current = date('h:i:s');
                    //$resObject->current = strftime('%H:%M:%S');
                    $this->setProcessObject($resObject);
                    $return = json_encode($resObject);
                    break;
                case 'endtreedef':
                    // we are done with this app
                    $resObject = $this->initProcessObject($procObject->japp_ids);
                    $procObject->current = date('h:i:s');
                    //$resObject->current = strftime('%H:%M:%S');
                    $resObject->msg    = Text::sprintf('JTPROCESS_END_MSG', $procObject->id);
                    $return = json_encode($resObject);
                    break;
                    // End: Addition for processing tree-persons
                case 'end':
                case 'error':	// continue
                default:		// continue
                    $procObject->status = 'error';
                    $return = json_encode($procObject);
                    break;
            }
        } else {

            $procObject->status = 'error';
            $procObject->msg    = Text::_('JT_NOTAUTHORISED');
            $return = json_encode($procObject);
        }

        return $return;
    }

}
