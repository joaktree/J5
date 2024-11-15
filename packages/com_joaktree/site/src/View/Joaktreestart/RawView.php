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
namespace Joaktree\Component\Joaktree\Site\View\Joaktreestart;
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
/**
 * HTML View class for the Joaktree component
 */
class RawView extends HtmlView {
	function display($tpl = null) {	
		$this->lists 		= array();
		
		// Load the parameters.
		$this->params 		= JoaktreeHelper::getJTParams();		
		
		// Get data from the model
		$this->treeinfo		= $this->get( 'treeinfo' );  
		$menus  			= $this->get( 'menus' ); 
		
		// Id's and settings
		$this->lists['tree_id']		= $this->get( 'treeId' );
		$this->lists['userAccess'] 		= $this->get( 'access' );
		$this->lists['menuItemId'] 		= $menus[ $this->lists['tree_id'] ];
				
		//namelist
		$this->lists['columns']		= (int) $this->params->get('columns', '3');
		$this->namelist	  			= $this->get( 'namelist' );

		$this->lists['numberRows']	= (int) ceil( count($this->namelist) /  $this->lists['columns']);

		$this->lists['link'] 		=  'index.php?option=com_joaktree'
										.'&view=list'
										.'&Itemid='.$this->lists['menuItemId']
										.'&treeId='.$this->lists['tree_id'];								
				
		parent::display($tpl);
	}
}
?>