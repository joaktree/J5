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
 * from toolbar.php 22155 2011-09-25 21:04:08Z dextercowley
*/

namespace Joaktree\Component\Joaktree\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;

/**
 * Utility class for the button bar.
 *
 * @package		Joaktree
 * @subpackage
 */
abstract class Jtoolbarcustom
{
    /**
     * Writes a custom option and task button for the button bar.
     *
     * @param	string	$task		The task to perform (picked up by the switch($task) blocks.
     * @param	string	$icon		The image to display.
     * @param	string	$alt		The alt text for the icon image.
     * @param	string  $msg		The warning/question message
     * @param	bool	$listSelect	True if required to check that a standard list item is checked.
     * @since	1.0
     */
    public static function custom($task = '', $icon = '', $alt = '', $msg = '', $listSelect = true)
    {
        $bar = Factory::getApplication()->getDocument()->getToolbar();
        $icon = preg_replace('#\.[^.]*$#', '', $icon);

        // Add a standard button.
        if ($msg) {
            $bar->confirmButton($icon, $alt, $task)
            ->message($msg)
            ->listCheck($listSelect);
        } else {
            $bar->appendButton('Standard', $icon, $alt, $task, $listSelect);
        }
    }

}
