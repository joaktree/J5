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
namespace Joaktree\Component\Joaktree\Site\View\Todaymanyyearsago;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for the Joaktree component
 */
class RawView extends HtmlView
{
    public function display($tpl = null)
    {
        // Include language file from module Today Many Years Ago;
        $jtlang = Factory::getApplication()->getLanguage();
        $jtlang->load('mod_joaktree_todaymanyyearsago');

        //
        $tmya_model = $this->getModel();
        $this->jtlist  = $tmya_model->getList(null);
        $this->title   = $tmya_model->getTitle();
        $this->sorting = $tmya_model->getSorting();

        //$this->assignRef('jtlist',	$jtlist);

        parent::display($tpl);
    }
}
