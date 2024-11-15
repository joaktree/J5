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
namespace Joaktree\Component\Joaktree\Administrator\View\Trees;
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class RawView extends BaseHtmlView {

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{	
		$items			= $this->get( 'assignFamilyTree' );
		$tpl			= 'raw';
        $this->items = $items;
		parent::display($tpl);
	}

}
