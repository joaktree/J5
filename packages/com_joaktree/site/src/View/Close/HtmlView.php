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

namespace Joaktree\Component\Joaktree\Site\View\Close;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; //replace JViewLegacy

/**
 * This view is displayed after successfull saving of config data.
 * Use it to show a message informing about success or simply close a modal window.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // close a modal window
        // Factory::getApplication()->getDocument()->addScriptDeclaration('window.parent.SqueezeBox.close();');
    }
}
