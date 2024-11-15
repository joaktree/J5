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
namespace Joaktree\Component\Joaktree\Site\Controller;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session; 
use Joomla\CMS\MVC\Controller\BaseController;
		
class ChangehistoryController extends BaseController {
	function __construct() {
		// first check token
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		
		// create an input object
		$this->input = Factory::getApplication()->input;

		//Get View
		if($this->input->get('view') == '') {
			$this->input->set('view', 'changehistory');
		}
		
		parent::__construct();
		
	}
}
?>