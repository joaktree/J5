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
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Table\Table;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\Trees;

class ThemesModel extends ListModel
{
    public $_data;
    public $_pagination 	= null;
    public $_total         = null;

    public function __construct()
    {
        parent::__construct();

        $app = Factory::getApplication();

        $context			= 'com_joaktree.themes.list.';
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
        // Get the WHERE and ORDER BY clauses for the query
        $query = $this->_db->getQuery(true);
        $query->select(' jtmp.* ');
        $query->from(' #__joaktree_themes  jtmp ');

        $wheres     =  $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }

        $orderby	=  $this->_buildContentOrderBy();
        $query->order(' '.$orderby.' ');

        return $query;
    }

    private function _buildContentWhere()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.themes.list.';
        $search			= $app->getUserStateFromRequest($context.'search', 'search', '', 'string');
        $search			= strtolower($search);

        $wheres = array();

        if ($search) {
            $wheres[] = 'LOWER(jtmp.name) LIKE '.$this->_db->Quote('%'.$search.'%');
        }

        return $wheres;
    }

    private function _buildContentOrderBy()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.themes.list.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jtmp.id', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order) {
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' ';
        } else {
            $orderby 	= ' jtmp.id '.$filter_order_Dir.' ';
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
            jimport('joomla.html.pagination');
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    public function setDefault($id)
    {
        $canDo	= JoaktreeHelper::getActions();

        if ($canDo->get('core.edit')) {
            // set id to default
            $query = $this->_db->getQuery(true);
            $query->update(' #__joaktree_themes ');
            $query->set(' home = 1 ');
            $query->where(' id   = :id');
            $query->bind(':d', $id, \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();

            if ($ret) {
                // set other record not to default
                $query->clear();
                $query->update(' #__joaktree_themes ');
                $query->set(' home = 0 ');
                $query->where(' id   <> :id');
                $query->bind(':id', $id, \Joomla\Database\ParameterType::INTEGER);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            if ($ret) {
                $name = JoaktreeHelper::getThemeName($id);
            }

            if ($ret) {
                return Text::sprintf('JTTHEME_MESSAGE_SETDEFAULT', $name);
            } else {
                return Text::sprintf('JTTHEME_ERROR_SETDEFAULT', $id);
            }
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }
    }
}
