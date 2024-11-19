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

namespace Joaktree\Component\Joaktree\Administrator\View\Viewlogs;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    protected $state;
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Initialiase variables.
        $this->form		= $this->get('Form');
        $this->item		= $this->get('Item');
        $this->state	= $this->get('State');

        parent::display($tpl);
    }
}
