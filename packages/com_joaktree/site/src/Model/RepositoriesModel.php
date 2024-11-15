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

class RepositoriesModel extends ListModel {

	function __construct() {
		$this->context			= 'com_joaktree.repo.list.';
		parent::__construct();	
	}

	public function getApplicationId() {
		return JoaktreeHelper::getApplicationId();
	}
		
 	public function getReturnObject() {
 		return JoaktreeHelper::getReturnObject();
	}
			
 	public function getAction() {
 		return JoaktreeHelper::getAction();
	}
			
	public static function getAccess() {
		return JoaktreeHelper::getAccessGedCom();
	}
	
	public function getTechnology() {
		return JoaktreeHelper::getTechnology();
	}

	protected function getListquery()
	{
		$query = $this->_db->getquery(true);
		
		// select from repositories
		$query->select(' jry.app_id ');
		$query->select(' jry.id ');
		$query->select(' jry.name ');
		$query->select(' jry.website ');
		$query->from(  ' #__joaktree_repositories jry ');
		
		// select from sources
		$query->select(' count(jse.id) AS indSource ');		
		$query->leftJoin(' #__joaktree_sources  jse ' 
					 	.' ON (   jse.app_id  = jry.app_id '
						.'    AND jse.repo_id = jry.id '
						.'    ) '
						);
		
		// Get the WHERE, GROUP BY and ORDER BY clauses for the query
		$wheres      	= $this->_buildContentWhere();
		foreach ($wheres as $where) {
			$query->where(' '.$where.' ');
		}
		$query->group(' jry.name ');
		$query->group(' jry.website ');
		$query->group(' jry.id ');
		$query->group(' jry.app_id ');
							
		return $query;
	}

	private function _buildContentWhere()
	{
		$app 		= Factory::getApplication('site');
		$appId     	= intval( $this->getApplicationId() );
		
		$retObj		= $this->getReturnObject();
		$status		= (is_object($retObj)) ? $retObj->status : null;
		if ((isset($status)) && ($status == 'new')) {
			$repoId		= $retObj->object_id;
		}
		
		$search1	= $app->getUserStateFromRequest( $this->context.'search1',	'search1',	'',	'string' );
		$search1	= $this->_db->escape( $search1, true );
		$search1	= strtolower( $search1 );
		
		$where = array();
		
		if ($appId) {
			$where[] = ' jry.app_id = '.$appId.' ';
		}
		
		if (isset($repoId)) {
			$where[] = ' jry.id <> '.$this->_db->quote($repoId).' ';
		}
		
		if ($search1) {
			$where[] = ' (  LOWER(jry.name)    LIKE '.$this->_db->Quote('%'.$search1.'%').' '
					  .' OR LOWER(jry.website) LIKE '.$this->_db->Quote('%'.$search1.'%').' '
					  .' ) ';
		}

		return $where;
	}
	
	public function getNewlyAddedItem()
	{
		$appId     	= intval( $this->getApplicationId() );
		$retObj		= $this->getReturnObject();
		$repoId		= $retObj->object_id;
		
		$query = $this->_db->getquery(true);
		
		// select from repositories
		$query->select(' jry.app_id ');
		$query->select(' jry.id ');
		$query->select(' jry.name ');
		$query->select(' jry.website ');
		$query->from(  ' #__joaktree_repositories jry ');
		
		// select from sources
		$query->select(' count(jse.id) AS indSource ');		
		$query->leftJoin(' #__joaktree_sources  jse ' 
					 	.' ON (   jse.app_id  = jry.app_id '
						.'    AND jse.repo_id = jry.id '
						.'    ) '
						);
		
		// Get the WHERE, GROUP BY and ORDER BY clauses for the query
		$query->where(' jry.app_id = '.$appId.' ');
		$query->where(' jry.id = '.$this->_db->quote($repoId).' ');
		
		$query->group(' jry.name ');
		$query->group(' jry.website ');
		$query->group(' jry.id ');
		$query->group(' jry.app_id ');
									
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
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $this->context.'limitstart',	'limitstart',	0, 'int' );
		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		// List state information
//		$this->setState('limit', $limit);
//		$this->setState('limitstart', $limitstart);
		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart);
	}	
}
?>