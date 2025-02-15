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

namespace Joaktree\Component\Joaktree\Site\View\List;

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

        // Load the parameters.
        $app 			= Factory::getApplication('site');
        $params			= JoaktreeHelper::getJTParams();
        $document = Factory::getApplication()->getDocument();

        // Find the value for tech
        $technology	= $model->getTechnology();

        // set up style sheets and javascript files
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss($params->get('theme')));

        // get user info
        $userId			= Factory::getApplication()->getIdentity()->id;
        if (!$userId || $userId == 0) {
            $document->addScript(JoaktreeHelper::joaktreejs('jtform.js'));
        }

        // Get data from the model
        $personlist			= $model->getPersonlist();
        $pagination			= $model->getPagination();
        $tree_id			= $model->getTreeId();
        $patronymSetting	= $model->getPatronymSetting();
        $userAccess			= $model->getAccess();
        $menus1  			= $model->getMenusJoaktree();
        $menus2  			= $model->getMenusList();

        // Id's and settings
        $lists['tree_id']		= $tree_id;
        $lists['relationId']	= $model->getRelationId();
        $lists['menuItemId'] 	= $menus1[ $tree_id ];
        $lists['menuItemId2'] 	= $menus2[ $tree_id ];
        $lists['patronym'] 		= $patronymSetting;
        $lists['userAccess'] 	= $userAccess;
        $lists['technology'] 	= $technology;
        $lists['action']		= JoaktreeHelper::getAction();
        //Filter
        $context			= 'com_joaktree.list.list.';

        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jpn.familyName', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');
        $search1			= $app->getUserStateFromRequest($context.'search1', 'search1', '', 'string');
        $search1			= strtolower($search1);
        $search2			= $app->getUserStateFromRequest($context.'search2', 'search2', '', 'string');
        $search2			= strtolower($search2);
        $search3			= $app->getUserStateFromRequest($context.'search3', 'search3', '', 'string');
        $search3			= strtolower($search3);
        $search4			= $app->getUserStateFromRequest($context.'search4', 'search4', '', 'string');
        $search4    		= base64_decode($search4);

        // table ordering
        $lists['order_Dir']	= $filter_order_Dir;
        $lists['order'] 	= $filter_order;

        // search filter
        $lists['searchWidth']	= (int) $params->get('search_width', '120');
        $lists['search1']		= $search1;
        $lists['search2']		= $search2;
        $lists['search3']		= $search3;
        $lists['search4']		= $search4;

        // last update
        $lists[ 'lastUpdate' ]	= $model->getLastUpdate();

        // copyright
        $lists[ 'CR' ]		= JoaktreeHelper::getJoaktreeCR();

        $this->pagination = $pagination;
        $this->personlist = $personlist;
        $this->lists = $lists;

        //if ((($lists['action'] == 'save') || ($lists['action'] == 'saveparent1')) && (count($personlist) == 0)) {
            //            Factory::getApplication()->getDocument()->addScriptDeclaration('window.parent.jtSavePerson();');
            //        } else {

            if ($lists['userAccess']) {
                // set title, meta title
                if ($params->get('treeName')) {
                    $title = $params->get('treeName');
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

                // robots
                if ($params->get('treeRobots') > 0) {
                    $document->setMetadata('robots', JoaktreeHelper::stringRobots($params->get('treeRobots')));
                } elseif ($params->get('robots')) {
                    $document->setMetadata('robots', $params->get('robots'));
                }
            }

            parent::display($tpl);
       // }
    }
}
