<?php
/**
 * Joomla! component Joaktree
 * file		administrator jt_import_gedcom view - view.raw.php
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
namespace Joaktree\Component\Joaktree\Administrator\View\Importgedcom;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class RawView extends BaseHtmlView {

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	
		$items			= $this->get( 'Gedcom' );
		$tpl			= 'raw';
		//$this->assignRef( 'items',  $items );
		$this->items=$items;
		
		parent::display($tpl);
	}

}
