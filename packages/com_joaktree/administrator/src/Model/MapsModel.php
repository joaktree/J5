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
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;

use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class MapsModel extends ListModel {

	var $_data;
	var $_pagination 	= null;
	var $_total         = null;

	function __construct() {
		parent::__construct();		

		$app = Factory::getApplication();
			
		$context			= 'com_joaktree.maps.list.';
		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $context.'limitstart',	'limitstart',	0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	private function _buildquery() {
		$query = $this->_db->getQuery(true);
		
		$query->select(' jmp.id ');
		$query->select(' jmp.name ');
		$query->select(' jmp.period_start ');
		$query->select(' jmp.period_end ');
		$query->select(' jmp.app_id ');
		$query->select(' japp.title AS appTitle ');
		
		$query->select(' CASE jmp.service '
					  .'   WHEN '.$this->_db->quote('staticmap').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_TYPE_STATIC')).' '
					  .'   WHEN '.$this->_db->quote('interactivemap').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_TYPE_INTERACTIVE')).' '
					  .'   ELSE '.$this->_db->quote(Text::_('JTMAP_TYPE_STATIC')).' '
					  .' END  AS service '
					  );
		
		$query->select(' CASE jmp.selection '
					  .'   WHEN '.$this->_db->quote('application').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_SELECTION_APP')).' '
					  .'   WHEN '.$this->_db->quote('tree').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_SELECTION_TREE')).' '
					  .'   WHEN '.$this->_db->quote('location').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_SELECTION_LOCATION')).' '
					  .'   WHEN '.$this->_db->quote('person').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_SELECTION_PERSON')).' '
					  .'   WHEN '.$this->_db->quote('persons').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_SELECTION_PERSONS')).' '
					  .'   WHEN '.$this->_db->quote('name').' '
					  .'   THEN '.$this->_db->quote(Text::_('JTMAP_SELECTION_NAME')).' '
					  .'   ELSE '.$this->_db->quote(Text::_('JTMAP_SELECTION_APP')).' '
					  .' END  AS selection '
					  );
		$query->select(' CASE jmp.selection '
					  .'   WHEN '.$this->_db->quote('application').' '
					  .'   THEN japp.title '
					  .'   WHEN '.$this->_db->quote('tree').' '
					  .'   THEN jte.name '
					  .'   WHEN '.$this->_db->quote('location').' '
					  .'   THEN jmp.subject '
					  .'   WHEN '.$this->_db->quote('person').' '
					  .'   THEN '.JoaktreeHelper::getConcatenatedFullName().' '
					  .'   ELSE jmp.subject '
					  .' END  AS subject '
					  );
		
		$query->from(  ' #__joaktree_maps  jmp ');				
		$query->innerJoin(  ' #__joaktree_applications  japp '
						 .  ' ON ( japp.id = jmp.app_id ) '
						 );		
		$query->leftJoin(  ' #__joaktree_trees  jte '
						 .  ' ON ( jte.id = jmp.tree_id ) '
						 );
		$query->leftJoin(  ' #__joaktree_persons  jpn '
						 . ' ON (   jpn.app_id = jmp.app_id '
						 . '    AND jpn.id     = jmp.person_id '
						 . '    ) '
						 );
						 
						 
		// WHERE, GROUP BY and ORDER BY clauses for the query
		$wheres     =  $this->_buildContentWhere();
		foreach ($wheres as $where) {
			$query->where(' '.$where.' ');
		}

		$query->order(' '.$this->_buildContentOrderBy().' ');
		
		return $query;			
	}

	private function _buildContentWhere() {
		$app = Factory::getApplication();
		
		$context		= 'com_joaktree.maps.list.';
		$search			= $app->getUserStateFromRequest( $context.'search',			'search',			'',				'string' );
		$search			= strtolower( $search );
		
		$where = array();
		
		if ($search) {
			$where[] =   'LOWER(jmp.name) LIKE '.$this->_db->Quote('%'.$search.'%').' ';
		}
				
		return $where;
	}

	private function _buildContentOrderBy() {
		$app = Factory::getApplication();
		
		$context		= 'com_joaktree.maps.list.';
		$filter_order		= $app->getUserStateFromRequest( $context.'filter_order',		'filter_order',		'jmp.name',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $context.'filter_order_Dir',	'filter_order_Dir',	'',		'word' );
		
		if ($filter_order){
			$orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' ';
		} else {
			$orderby 	= ' jmp.name '.$filter_order_Dir.' ';
		}
		
		return $orderby;
	}

	public function getData() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildquery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
		}
		
		return $this->_data;
	}

	public function getTotal() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildquery();
			$this->_total = $this->_getListCount($query);
		}
		
		return $this->_total;
	}

	public function getPagination() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new Pagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		
		return $this->_pagination;
	}
	
	public function getMapSettings() {
		$settings = MBJService::getKeys();
		MBJService::setLanguage();
		
		// count locations without coordinates
		$query	= $this->_db->getQuery(true);
		
		$query->select(' COUNT(id) ');
		$query->from(  ' #__joaktree_locations ');
		$this->_db->setQuery($query);
		$total = $this->_db->loadResult();

		$query->clear();
		$query->select(' COUNT(id) ');
		$query->from(  ' #__joaktree_locations ');
		$query->where( ' latitude  IS NOT NULL ');
		$query->where( ' latitude  <> 0 ');
		$query->where( ' longitude IS NOT NULL ');
		$query->where( ' longitude <> 0 ');
		$this->_db->setQuery($query);
		$valid = $this->_db->loadResult();
		
		$settings->valid     = (int) $valid;
		$settings->total     = (int) $total;
		$settings->invalid   = $settings->total - $settings->valid;
		$settings->validpc   = ($settings->total) ? round(100 * ($settings->valid / $settings->total), 0) : 0;
		$settings->invalidpc = ($settings->total) ? round(100 * ($settings->invalid / $settings->total), 0) : 0;
		
		return $settings;
	}
	
	

}
?>