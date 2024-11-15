<?php
/**
 * Joomla! component Joaktree
 * file		JoaktreeModel - joaktree.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */
namespace Joaktree\Component\Joaktree\Administrator\Model;
 
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel ; 	//replace JModelLegacy

use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJProvider;

class DefaultModel extends BaseDatabaseModel {

	var $_persons;

	function __construct() {
		parent::__construct();
	}
	
	public static function getProviders() {
		return MBJProvider::getConnectors();
	}
}
?>