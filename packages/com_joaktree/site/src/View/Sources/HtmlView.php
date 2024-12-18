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

namespace Joaktree\Component\Joaktree\Site\View\Sources;

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
        $document->addScript(JoaktreeHelper::joaktreejs('toggle.js'));

        // get user info
        $userId			= Factory::getApplication()->getIdentity()->id;
        if (!$userId || $userId == 0) {
            $document->addScript(JoaktreeHelper::joaktreejs('jtform.js'));
        }

        // Get data from the model
        $this->items				= $model->getItems();
        $this->pagination			= $model->getPagination();
        $this->lists['app_id']		= $model->getApplicationId();
        $this->lists['userAccess']	= $model->getAccess();

        $statusObj					= $model->getReturnObject();
        $this->lists['source_id']	= (is_object($statusObj)) ? $statusObj->object_id : null;
        $this->lists['status']		= (is_object($statusObj)) ? $statusObj->status : null;
        if ($this->lists['status'] == 'new') {
            $this->newItem			= $model->getNewlyAddedItem();
        }
        $this->lists['action']		= $model->getAction();
        if ($this->lists['action'] == 'select') {
            $this->lists['link'] = 'index.php?option=com_joaktree'
                                  .'&view=sources'
                                  .'&appId='.$this->lists['app_id']
                                  .'&tmpl=component'
                                  .'&action='.$this->lists['action'];

            // in case of "select" - what are the details
            $this->lists['counter'] = $model->getCounter();
        } else {
            $this->lists['link'] = 'index.php?option=com_joaktree'
                                  .'&view=sources'
                                  .'&appId='.$this->lists['app_id'];
        }

        if ($params->get('siteedit', 1)) {
            $this->canDo	= JoaktreeHelper::getActions(false);
        } else {
            $this->canDo	= null;
        }

        //Filter
        $context			= 'com_joaktree.source.list.';
        $search1			= $app->getUserStateFromRequest($context.'search1', 'search1', '', 'string');
        $search1			= strtolower($search1);

        // search filter
        $this->lists['search1']		= $search1;

        // Find the value for tech
        $this->lists[ 'technology' ] = $model->getTechnology();

        // copyright
        $this->lists[ 'CR' ]		 = JoaktreeHelper::getJoaktreeCR();
        $this->lists[ 'showchange' ] = (int) $params->get('indLogging', 0);

        //return
        $retObject				= new \stdClass();
        $retObject->object		= 'sour';
        $this->lists[ 'retId' ]		= base64_encode(json_encode($retObject));

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
