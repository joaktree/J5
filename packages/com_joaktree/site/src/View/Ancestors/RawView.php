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

namespace Joaktree\Component\Joaktree\Site\View\Ancestors;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

/**
 * HTML View class for the Joaktree component
 */
class RawView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        $params			= JoaktreeHelper::getJTParams();

        // Access
        $lists['userAccess'] 	= $model->getAccess();
        $lists['treeId'] 		= $model->getTreeId();
        $lists['technology'] 	= $model->getTechnology();

        // Person + generations
        $personId	 			= array();
        $this->person			= $model->getPerson();
        $personId[]		 		= $this->person->id.'|1';
        $lists[ 'ancestorLevel'] = $params->get('ancestorlevel', 1);
        $lists[ 'startGenNum' ]	= 1;
        $lists[ 'endGenNum' ]	= $lists[ 'ancestorLevel'] + 4;
        $lists[ 'app_id' ]		= $this->person->app_id;

        // show dates
        $lists[ 'showDates'] 	= $params->get('ancestordates', 0);

        // last update
        $lists[ 'lastUpdate' ]	= Text::_('JT_LASTUPDATED').': '.JoaktreeHelper::convertDateTime($this->person->lastUpdateDate);

        // copyright
        $lists[ 'CR' ]		= JoaktreeHelper::getJoaktreeCR();

        //$this->assignRef( 'personId', $personId);
        //$this->assignRef( 'lists',	  $lists);
        $this->personId = $personId;
        $this->lists = $lists;

        parent::display($tpl);
    }
}
