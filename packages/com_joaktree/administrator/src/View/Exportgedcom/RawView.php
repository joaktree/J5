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

namespace Joaktree\Component\Joaktree\Administrator\View\Exportgedcom;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class RawView extends BaseHtmlView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $input = Factory::getApplication()->getInput();
        if ($input->get('task') && ($input->get('task') == 'del')) {

            $params		= JoaktreeHelper::getJTParams($input->get('id'));
            $config     = ComponentHelper::getParams('com_joaktree') ;
            $defpath    = $params->get('gedcomfile_path', 'files/com_joaktree/gedfiles');
            $path  		= JPATH_ROOT.'/'.$params->get('gedcomfile_path',$defpath);
            $file       = 'export_' .$params->get('gedcomfile_name');
            $filename	= $path.'/' .$file;
            unlink($filename);
            echo  '<span style="color:green">'.Text::sprintf('JTFIELD_DELEXPORT_DONE', $file).'</span>';
            return true;
        }

        $items			= $this->get('Gedcom');
        $tpl			= 'raw';
        $this->items = $items;

        parent::display($tpl);
    }

}
