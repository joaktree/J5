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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

/**
 * HTML View class for the Joaktree component
 */
class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $params			= JoaktreeHelper::getJTParams();
        $document		= Factory::getApplication()->getDocument();

        // set up style sheets and javascript files
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss($params->get('theme')));

        // Access
        $lists['userAccess'] 	= $this->get('access');
        $lists['treeId'] 		= $this->get('treeId');
        $lists['technology'] 	= $this->get('technology');

        // Person + generations
        $personId	 			= array();
        $this->person			= $this->get('person');
        $personId[]		 		= $this->person->id.'|1';
        $lists[ 'ancestorLevel'] = $params->get('ancestorlevel', 1);
        $lists[ 'startGenNum' ]	= 1;
        $lists[ 'endGenNum' ]	= $lists[ 'ancestorLevel'] + 4;
        $lists[ 'app_id' ]		= $this->person->app_id;

        // show dates
        $lists[ 'showDates'] 	= $params->get('ancestordates', 0);

        // last update
        $lists[ 'lastUpdate' ]	= JoaktreeHelper::lastUpdateDateTimePerson($this->person->lastUpdateDate);

        // copyright
        $lists[ 'CR' ]		= JoaktreeHelper::getJoaktreeCR();

        /*$this->assignRef( 'personId', $personId);
        $this->assignRef( 'lists',	  $lists);*/
        $this->personId = $personId;
        $this->lists = $lists;

        if ($lists['userAccess']) {
            // set title, meta title
            $document->setTitle($this->person->fullName);
            $document->setMetadata('title', $this->person->fullName);

            // set additional meta tags
            if ($params->get('menu-meta_description')) {
                $document->setDescription($params->get('menu-meta_description'));
            }

            if ($params->get('menu-meta_keywords')) {
                $document->setMetadata('keywords', $params->get('menu-meta_keywords'));
            }

            // robots
            if ($this->person->robots > 0) {
                $document->setMetadata('robots', JoaktreeHelper::stringRobots($this->person->robots));
            } elseif ($params->get('robots')) {
                $document->setMetadata('robots', $params->get('robots'));
            }
        }

        parent::display($tpl);
    }
}
