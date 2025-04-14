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

namespace Joaktree\Component\Joaktree\Site\View\Descendants;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
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

        $model = $this->getModel();

        $params			= JoaktreeHelper::getJTParams();
        $document		= Factory::getApplication()->getDocument();

        // set up style sheets and javascript files
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseStyle('jtthemecss',JoaktreeHelper::joaktreecss($params->get('theme')));

        // Access
        $lists['userAccess'] 	= $model->getAccess();
        $lists['treeId'] 		= $model->getTreeId();
        $lists['technology'] 	= $model->getTechnology();

        // Person + generations
        $personId	 			= array();
        $this->person			= $model->getPerson();
        $personId[]		 		= $this->person->id.'|1';
        $lists[ 'startGenNum' ]	= 1;
        $lists[ 'endGenNum' ]	= (int) $params->get('descendantlevel', 20);
        $lists[ 'app_id' ]		= $this->person->app_id;

        // last update
        $lists[ 'lastUpdate' ]	= JoaktreeHelper::lastUpdateDateTimePerson($this->person->lastUpdateDate);

        // copyright
        $lists[ 'CR' ]		= JoaktreeHelper::getJoaktreeCR();

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
