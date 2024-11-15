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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Person;

class AncestorsModel extends BaseDatabaseModel {
	
	function __construct() {
		parent::__construct();            		
	} 
	
	public function getUserAccess() {
		return JoaktreeHelper::getUserAccess();
	}
		
 	public function getTreeId() {
		return JoaktreeHelper::getTreeId();
 	}
		
	public function getTechnology() {
		return JoaktreeHelper::getTechnology();
	}
				
	public function getAccess() {
		static $_access;
		
		if (!isset($_access)) {
			$params = JoaktreeHelper::getJTParams();
			$ancestorEnabled = $params->get('ancestorchart', 0);
			
			if ($ancestorEnabled != 1) {
				// chart is not enabled
				$_access = false;
			} else {
				// chart is enabled
				$_access = JoaktreeHelper::getAccess();	
			}				
		}
						
		return $_access;
	}
	
	public function getPerson() {
		static $person;
		
		if (!isset($person)) {
			$id[ 'app_id' ] 	= JoaktreeHelper::getApplicationId();
			$id[ 'person_id' ] 	= JoaktreeHelper::getPersonId(); 
			$person	  =  new Person($id, 'basic');
		}
		
		return $person;
	}
}
?>