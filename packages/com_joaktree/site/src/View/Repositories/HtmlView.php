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

namespace Joaktree\Component\Joaktree\Site\View\Repositories;

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

        $this->lists	= array();
        $app 			= Factory::getApplication('site');
        $document = Factory::getApplication()->getDocument();

        // Load the parameters.
        $params	= $app->getParams();
        $params->merge(JoaktreeHelper::getGedCom());
        $params->merge(JoaktreeHelper::getTheme(true, true));

        // set up style sheets and javascript files
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss($params->get('theme')));

        // get user info
        $userId			= $this->get('userId');
        if (!$userId || $userId == 0) {
            $document->addScript(JoaktreeHelper::joaktreejs('jtform.js'));
        }

        // Get data from the model
        $this->items				= $this->get('items');
        $this->pagination			= $this->get('pagination');
        $this->lists['app_id']		= $this->get('applicationId');
        $this->lists['userAccess']	= $this->get('access');

        $statusObj					= $this->get('returnObject');
        $this->lists['repo_id']		= (is_object($statusObj)) ? $statusObj->object_id : null;
        $this->lists['status']		= (is_object($statusObj)) ? $statusObj->status : null;
        if ($this->lists['status'] == 'new') {
            $this->newItem			= $this->get('newlyAddedItem');
        }
        $this->lists['action']		= $this->get('action');
        if ($this->lists['action'] == 'select') {
            $this->lists['link'] = 'index.php?option=com_joaktree'
                                  .'&view=repositories'
                                  .'&appId='.$this->lists['app_id']
                                  .'&tmpl=component'
                                  .'&action='.$this->lists['action'];
        } else {
            $this->lists['link'] = 'index.php?option=com_joaktree'
                                  .'&view=repositories'
                                  .'&appId='.$this->lists['app_id'];
        }

        if ($params->get('siteedit', 1)) {
            $this->canDo	= JoaktreeHelper::getActions(false);
        } else {
            $this->canDo	= null;
        }

        //Filter
        $context			= 'com_joaktree.repo.list.';
        $search1			= $app->getUserStateFromRequest($context.'search1', 'search1', '', 'string');
        $search1			= strtolower($search1);

        // search filter
        $this->lists['search1']		= $search1;

        // Find the value for tech
        $this->lists[ 'technology' ] = $this->get('technology');

        // copyright
        $this->lists[ 'CR' ]		 = JoaktreeHelper::getJoaktreeCR();
        $this->lists[ 'showchange' ] = (int) $params->get('indLogging', 0);

        //return
        $retObject				= new \stdClass();
        $retObject->object		= 'repo';
        $this->lists[ 'retId' ]	= base64_encode(json_encode($retObject));

        if ($this->lists['userAccess']) {
            // set title, meta title
            if ($params->get('gedcomName')) {
                $title = $params->get('gedcomName');
                $document->setTitle($title);
                $document->setMetadata('title', $title);
            }

            // set additional meta tags
            if ($params->get('menu-meta_description')) {
                $document->setDescription($params->get('menu-meta_description'));
            }

            if ($params->get('menu-meta_keywords')) {
                $document->setMetadata('keywords', $params->get('menu-meta_keywords'));
            }

            if ($params->get('robots')) {
                $document->setMetadata('robots', $params->get('robots'));
            }
        }

        parent::display($tpl);
    }
}
