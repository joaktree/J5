<?php
/**
 * Joomla! component Joaktree
 * file		Assignft Button
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */
namespace Joaktree\Component\Joaktree\Administrator\Button; 

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Button\StandardButton;

class AssignftButton extends StandardButton
{
    protected function _getCommand()	//$name, $task, $list)
    {
        HtmlHelper::_('bootstrap.framework');
        $message = Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
        $message = addslashes($message);

        if (isset($list)) {
            //$cmd = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{ Joomla.submitbutton('$task')}";
            $cmd = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{ assignftInit(); }";
        } else {
            //$cmd = "Joomla.submitbutton1('$task')";
            $cmd = "assignftInit();";
        }

        return $cmd;
    }
}
