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
use Joomla\CMS\MVC\Model\ListModel;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class SourcesModel extends ListModel
{
    public function __construct()
    {
        $this->context	= 'com_joaktree.source.list.';

        parent::__construct();
    }

    public function getApplicationId()
    {
        return JoaktreeHelper::getApplicationId();
    }

    public function getReturnObject()
    {
        return JoaktreeHelper::getReturnObject();
    }

    public function getAction()
    {
        return JoaktreeHelper::getAction();
    }

    public static function getAccess()
    {
        return JoaktreeHelper::getAccessGedCom();
    }

    public function getTechnology()
    {
        return JoaktreeHelper::getTechnology();
    }

    public function getCounter()
    {
        // Get the "counter" in case we are in a form
        $app 		= Factory::getApplication('site');
        $counter	= $app->getUserStateFromRequest($this->context.'counter', 'counter', 0, 'int');
        return $counter;
    }


    protected function getListquery()
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

        // select from repositories
        $query->select(' jry.name AS repository ');
        $query->select(' jry.website ');
        $query->leftJoin(
            ' #__joaktree_repositories  jry '
                        .' ON (   jry.app_id = jse.app_id '
                        .'    AND jry.id     = jse.repo_id '
                        .'    ) '
        );

        // select from citations
        $query->select(' count(jcn.app_id) AS indCitation ');
        $query->leftJoin(
            ' #__joaktree_citations  jcn '
                        .' ON (   jcn.app_id    = jse.app_id '
                        .'    AND jcn.source_id = jse.id '
                        .'    ) '
        );

        // Get the WHERE, GROUP BY and ORDER BY clauses for the query
        $wheres      	= $this->_buildContentWhere();
        foreach ($wheres as $where) {
            $query->where(' '.$where.' ');
        }
        $query->group(' jse.title ');
        $query->group(' jse.author ');
        $query->group(' jse.publication ');
        $query->group(' jse.information ');
        $query->group(' jse.app_id ');
        $query->group(' jse.id ');
        $query->group(' jse.repo_id ');
        $query->group(' jry.name ');
        $query->group(' jry.website ');

        return $query;
    }

    private function _buildContentWhere()
    {
        $app 		= Factory::getApplication('site');
        $appId     	= intval($this->getApplicationId());

        $retObj		= $this->getReturnObject();
        $status		= (is_object($retObj)) ? $retObj->status : null;
        if ((isset($status)) && ($status == 'new')) {
            $sourceId		= $retObj->object_id;
        }

        $search1	= $app->getUserStateFromRequest($this->context.'search1', 'search1', '', 'string');
        $search1	= $this->_db->escape($search1, true);
        $search1	= strtolower($search1);

        $where = array();

        if ($appId) {
            $where[] = ' jse.app_id = '.$appId.' ';
        }

        if (isset($sourceId)) {
            $where[] = ' jry.id <> '.$this->_db->quote($sourceId).' ';
        }

        if ($search1) {
            $where[] = ' (  LOWER(jse.title)       LIKE '.$this->_db->Quote('%'.$search1.'%').' '
                      .' OR LOWER(jse.author)  	   LIKE '.$this->_db->Quote('%'.$search1.'%').' '
                      .' OR LOWER(jse.publication) LIKE '.$this->_db->Quote('%'.$search1.'%').' '
                      .' OR LOWER(jse.information) LIKE '.$this->_db->Quote('%'.$search1.'%').' '
                      .' ) ';
        }

        return $where;
    }

    public function getNewlyAddedItem()
    {
        $appId     	= intval($this->getApplicationId());
        $retObj		= $this->getReturnObject();
        $sourceId	= $retObj->object_id;

        $query = $this->_db->getquery(true);

        // select from sources
        $query->select(' jse.app_id ');
        $query->select(' jse.id ');
        $query->select(' jse.title ');
        $query->select(' jse.author ');
        $query->select(' jse.publication ');
        $query->select(' jse.information ');
        $query->select(' jse.abbr ');
        $query->select(' jse.note ');
        $query->select(' jse.media ');
        $query->select(' jse.www ');
        $query->from(' #__joaktree_sources jse ');

        // select from repositories
        $query->select(' jry.name AS repository ');
        $query->select(' jry.website ');
        $query->leftJoin(
            ' #__joaktree_repositories  jry '
                        .' ON (   jry.app_id = jse.app_id '
                        .'    AND jry.id     = jse.repo_id '
                        .'    ) '
        );

        // select from citations
        $query->select(' count(jcn.app_id) AS indCitation ');
        $query->leftJoin(
            ' #__joaktree_citations  jcn '
                        .' ON (   jcn.app_id    = jse.app_id '
                        .'    AND jcn.source_id = jse.id '
                        .'    ) '
        );

        // Get the WHERE, GROUP BY and ORDER BY clauses for the query
        $query->where(' jse.app_id = '.$appId.' ');
        $query->where(' jse.id = '.$this->_db->quote($sourceId).' ');
        $query->group(' jse.title ');
        $query->group(' jse.author ');
        $query->group(' jse.publication ');
        $query->group(' jse.information ');
        $query->group(' jse.app_id ');
        $query->group(' jse.id ');
        $query->group(' jry.name ');
        $query->group(' jry.website ');

        $this->_db->setquery($query);
        $item = $this->_db->loadObject();

        return $item;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app 		= Factory::getApplication('site');

        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart	= $app->getUserStateFromRequest($this->context.'limitstart', 'limitstart', 0, 'int');
        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        // Get the "counter" in case we are in a form
        $counter	= $app->getUserStateFromRequest($this->context.'counter', 'counter', 0, 'int');

        // List state information
        //		$this->setState('limit', $limit);
        //		$this->setState('limitstart', $limitstart);
        $this->setState('list.limit', $limit);
        $this->setState('list.start', $limitstart);
    }
}
