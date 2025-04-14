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

namespace Joaktree\Component\Joaktree\Site\View\Joaktreestart;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlview;
use Joomla\CMS\Uri\Uri;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Tree;

/**
 * HTML View class for the Joaktree component
 */
class Htmlview extends BaseHtmlview
{ //HtmlView {
    public function display($tpl = null)
    {
        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        $this->lists 		= array();

        // Load the parameters.
        $this->params 		= JoaktreeHelper::getJTParams();
        $document			= Factory::getApplication()->getDocument();
        $app 				= Factory::getApplication('site');

        // Get data from the model
        $this->treeinfo		= $model->getTreeinfo();
        $menus  			= $model->getMenus();

        // set up style sheets and javascript files
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseStyle('jtthemecss',JoaktreeHelper::joaktreecss($this->params->get('theme')));

        // add script
        $this->lists['indFilter']	= $model->getNameFilter();
        $this->lists['tree_id']		= $model->getTreeId();
        $this->lists['script']		= $this->addScript();

        // get text
        $this->articles		= Tree::getArticles($this->lists['tree_id']);

        // Id's and settings
        $this->lists['userAccess'] 		= $model->getAccess();
        $this->lists['menuItemId'] 		= $menus[ $this->lists['tree_id'] ];

        if ($this->treeinfo->indPersonCount) {
            $this->lists['personCount'] 	= $model->getPersonCount();
        }

        if ($this->treeinfo->indMarriageCount) {
            $this->lists['marriageCount']	= $model->getMarriageCount();
        }

        //namelist
        $this->lists['index']		= $model->getNameIndex();
        $this->lists['columns']		= (int) $this->params->get('columns', '3');
        $this->namelist	  			= $model->getNamelist();

        echo('<br/>');
        $this->lists['numberRows']	= (int) ceil(count($this->namelist) /  $this->lists['columns']);

        $this->lists['link'] 		=  'index.php?option=com_joaktree'
                                        .'&view=list'
                                        .'&Itemid='.$this->lists['menuItemId']
                                        .'&treeId='.$this->lists['tree_id'];

        // last update
        $this->lists[ 'lastUpdate' ]	= $model->getLastUpdate();

        // copyright
        $this->lists[ 'CR' ]		= JoaktreeHelper::getJoaktreeCR();

        if ($this->lists['userAccess']) {
            // set title, meta title
            if ($this->params->get('treeName')) {
                $title = $this->params->get('treeName');
                $document->setTitle($title);
                $document->setMetadata('title', $title);
            }

            // set additional meta tags
            if ($this->params->get('menu-meta_description')) {
                $document->setDescription($this->params->get('menu-meta_description'));
            }

            if ($this->params->get('menu-meta_keywords')) {
                $document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
            }

            // robots
            if ($this->params->get('treeRobots') > 0) {
                $document->setMetadata('robots', JoaktreeHelper::stringRobots($this->params->get('treeRobots')));
            } elseif ($this->params->get('robots')) {
                $document->setMetadata('robots', $this->params->get('robots'));
            }
        }

        parent::display($tpl);
    }

    protected function addScript()
    {

        $script = array();
        $indCookie	= $this->params->get('indCookies', true);
        $base	= 'media/com_joaktree/';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtstart.js');
        } else {
            $wa->registerAndUseScript('jtstart', $base.'js/jtstart.js');
        }

        Factory::getApplication()->getDocument()->addScriptOptions(
            'joaktreestart',
            array('indCookie' => $indCookie,'lists' => $this->lists)
        );
        return '';
    }
}
