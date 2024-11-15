<?php
/**
 * @version		$Id: view.html.php 20228 2011-01-10 00:52:54Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
