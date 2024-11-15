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
namespace Joaktree\Component\Joaktree\Administrator\Helper;
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Toolbar\Toolbar;
/**
 * Utility class for the button bar.
 *
 * @package		Joaktree
 * @subpackage	
 */
abstract class JToolBarCustom
{
	/**
	 * Writes a custom option and task button for the button bar.
	 *
	 * @param	string	$task		The task to perform (picked up by the switch($task) blocks.
	 * @param	string	$icon		The image to display.
	 * @param	string	$iconOver	The image to display when moused over.
	 * @param	string	$alt		The alt text for the icon image.
	 * @param	string  $msg		The warning/question message
	 * @param	bool	$listSelect	True if required to check that a standard list item is checked.
	 * @since	1.0
	 */
	public static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $msg = '', $listSelect = true)
	{
		$bar = ToolBar::getInstance('toolbar');

		// Strip extension.
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button.
		if ($msg) {
			$bar->appendButton('Confirm', $msg, $icon, $alt, $task, $listSelect);
		} else {
			$bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
		}
	}

}

