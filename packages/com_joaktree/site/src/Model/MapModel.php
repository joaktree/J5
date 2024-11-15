<?php
/**
 * Joomla! component Joaktree
 * file		front end map model - map.php
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
namespace Joaktree\Component\Joaktree\Site\Model;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel ; 	//replace JModelLegacy

// import component libraries
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Map;

class MapModel extends BaseDatabaseModel {
	
	protected $map;
	
	function __construct() {
		$id = array();
		$id['map']		= Map::getMapId(true);
		$id['location']	= Map::getLocationId(true);
		$id['distance']	= Map::getDistance(true);
		$id['person']	= JoaktreeHelper::getPersonId(false, true);
		$id['tree']		= JoaktreeHelper::getTreeId(false, true);
		$id['app']		= JoaktreeHelper::getApplicationId(false, true);
		
		$this->map 	= new Map($id);
		parent::__construct();            		
	} 
	
	private function getMapId() {
		return Map::getMapId();
	}
					
	public function getMap() {
		return $this->map;
	}
	
	public function getMapView() {
		return $this->map->getMapView();
	}

	public function getUIControl($mapHtmlId) {
		return $this->map->getUIControl($mapHtmlId);
	}
}
?>