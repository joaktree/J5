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
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class IsoModel extends BaseDatabaseModel
{
    public function __construct()
    {
        parent::__construct();

        $app 		= Factory::getApplication('site');

        $context			= 'com_joaktree.list.iso.';
        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');

        $limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');
        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', 1000);
        $this->setState('limitstart', $limitstart);
    }

    public function getUserAccess()
    {
        return JoaktreeHelper::getUserAccess();
    }

    public function getTreeId()
    {
        return JoaktreeHelper::getTreeId();
    }

    public function getRelationId()
    {
        return JoaktreeHelper::getRelationId();
    }

    public function getTechnology()
    {
        return JoaktreeHelper::getTechnology();
    }

    public static function getAccess()
    {
        return JoaktreeHelper::getAccessTree();
    }

    private function _buildquery()
    {
        $treeId     	= intval($this->getTreeId());
        $levels			= JoaktreeHelper::getUserAccessLevels();
        $displayAccess 	= JoaktreeHelper::getDisplayAccess();

        $query = $this->_db->getquery(true);

        // select from persons
        $query->select(' jpn.id ');
        $query->select(' jpn.app_id ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectFirstName().' ) AS firstName ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectPatronym().' ) AS patronym ');
        $query->select(' MIN( '.JoaktreeHelper::getConcatenatedFamilyName().' ) AS familyName ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectBirthYear().' ) AS birthDate ');
        $query->select(' MIN( '.JoaktreeHelper::getSelectDeathYear().' ) AS deathDate ');
        $query->from(' #__joaktree_persons jpn ');

        // select from admin persons
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(false));

        // select from tree x persons
        $query->innerJoin(
            ' #__joaktree_tree_persons  jtp '
                         .' ON (   jtp.app_id    = jpn.app_id '
                         .'    AND jtp.person_id = jpn.id '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_trees         jte '
                         .' ON (   jte.app_id    = jtp.app_id '
                         .'    AND jte.id        = jtp.tree_id '
                         .'    AND jte.published = true '
                         .'    AND jte.access    IN '.$levels.' '
                         .'    ) '
        );

        // select from birth and death
        $query->leftJoin(JoaktreeHelper::getJoinBirth());
        $query->leftJoin(JoaktreeHelper::getJoinDeath());

        // Get the WHERE, GROUP BY and ORDER BY clauses for the query
        $wheres      	= $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }
        $query->group(' jpn.id ');
        $query->group(' jpn.app_id ');
        $query->order(' '.$this->_buildContentOrderBy().' ');


        return $query;
    }

    private function _buildContentWhere()
    {
        $app 		= Factory::getApplication('site');
        $treeId     = intval($this->getTreeId());
        $levels		= JoaktreeHelper::getUserAccessLevels();
        $params 	= JoaktreeHelper::getJTParams();

        $context	= 'com_joaktree.list.iso.';

        $where = array();

        if ($treeId) {
            $where[] = 'jtp.tree_id = ' . $treeId;
        }
        //$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
        return $where;
    }

    private function _buildContentOrderBy()
    {
        $app 				= Factory::getApplication('site');

        $context			= 'com_joaktree.list.iso.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jpn.familyName', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order == 'jpn.familyName') {
            //$orderby 	= ' ORDER BY  jpn.familyName '.$filter_order_Dir.', jpn.firstName '.$filter_order_Dir.' ';
            $orderby 	= ' jpn.familyName '.$filter_order_Dir.', jpn.firstName '.$filter_order_Dir.' ';
        } else {
            //$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', jpn.familyName '.$filter_order_Dir.' ';
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.', jpn.familyName '.$filter_order_Dir.' ';
        }

        return $orderby;
    }

    public function getPersonlist()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_personlist)) {
            $query = $this->_buildquery();
            $this->_personlist = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
            if (!count($this->_personlist) && $this->getState('limitstart') > 0) {
                // fix pagination : not on first page : retry
                $this->setState('limitstart', 0);
                $this->_personlist = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
            }
        }

        return $this->_personlist;
    }

    public function getTree_id()
    {
        return $this->_tree_id;
    }

    public function getPatronymSetting()
    {
        static $_patronymSetting;

        if (!isset($_patronymSetting)) {
            $params = JoaktreeHelper::getJTParams();
            $_patronymSetting	= (int) $params->get('patronym');
        }

        return $_patronymSetting;
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
        } else {
            $this->setState('limitstart', 0);
        }

        return $this->_pagination;
    }

    public function getMenusJoaktree()
    {
        static $_menuTreeId1 	= array();

        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId1) == 0) {
            $_menuTreeId1 = JoaktreeHelper::getMenus('joaktree');
        }

        return $_menuTreeId1;
    }

    public function getMenusList()
    {
        static $_menuTreeId2 	= array();

        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId2) == 0) {
            $_menuTreeId2 = JoaktreeHelper::getMenus('list');
        }

        return $_menuTreeId2;
    }

    public function getLastUpdate()
    {
        return JoaktreeHelper::lastUpdateDateTime();
    }
}
