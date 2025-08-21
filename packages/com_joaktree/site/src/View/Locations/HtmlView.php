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

namespace Joaktree\Component\Joaktree\Site\View\Locations;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Tree;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('jquery');

HtmlHelper::_('bootstrap.framework', true);
HTMLHelper::_('bootstrap.modal', 'a.modal');
/**
 * HTML View class for the Joaktree component
 */
class HtmlView extends BaseHtmlView
{	//JViewLegacy {
    public function display($tpl = null)
    {
        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        $this->lists 		= array();

        // Load the parameters.
        $this->params 		= JoaktreeHelper::getJTParams();

        $services = $this->params->get('services');
        if (!is_object($services)) {
            $services = json_decode($this->params->get('services'));
        }
        $format = "raw";
        if ($services->interactivemap == "Openstreetmap") {
            $format = "html";
        }
        $document			= Factory::getApplication()->getDocument();
        $app 				= Factory::getApplication('site');
        $input              = $app->getInput();
        // Get data from the model
        $this->treeinfo		= $model->getTreeinfo();
        $menus  			= $model->getMenus();

        // set up style sheets and javascript files
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseStyle('jtthemecss',JoaktreeHelper::joaktreecss($this->params->get('theme')));

        // add script
        $this->lists['interactiveMap'] 	= $model->getInteractiveMap();
        $this->lists['indFilter']	= $model->getLocationFilter();
        $this->lists['tree_id']		= $model->getTreeId();
        $this->lists['script']		= $this->addScript();

        // get text
        $this->articles				= Tree::getArticles($this->lists['tree_id']);

        // Id's and settings
        $this->lists['userAccess'] 	= $model->getAccess();
        $this->lists['menuItemId'] 	= $menus[ $this->lists['tree_id'] ];
        $this->lists['indMap']		= false;
        $tmp						= $model->getMapUrl();
        if ($tmp) {
            $this->lists['indMap']	= true;
            $this->lists['map']		= explode("|", $tmp);
        }

        // distance options
        if ($this->lists['interactiveMap']) {
            $distance					= $app->getUserStateFromRequest('com_joaktree.map.distance', 'distance', 0, 'int');
            $this->lists['distance']	= $this->getDistanceSelect($distance);
        }

        //location list
        $this->lists['index']		= $model->getLocationIndex();
        $this->lists['columns']		= (int) $this->params->get('columnsLoc', '3');
        $this->locationlist  		= $model->getLocationlist();
        $this->lists['numberRows']	= (int) ceil(count($this->locationlist) /  $this->lists['columns']);

        $this->lists['linkMap'] 	= 'index.php?option=com_joaktree'
                                        .'&view=interactivemap'
                                        .'&tmpl=component'
                                        .'&format='.$format
                                        .'&treeId='.$this->lists['tree_id'];
        $this->lists['linkList'] 	=  'index.php?option=com_joaktree'
                                        .'&view=list'
                                        .'&tmpl=component'
                                        .'&layout=location'
                                        .'&treeId='.$this->lists['tree_id'];

        // last update
        $this->lists[ 'lastUpdate' ] = $model->getLastUpdate();

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
        $services = $this->params->get('services');
        if (!is_object($services)) {
            $services = json_decode($this->params->get('services'));
        }
        $format = "raw";
        if ($services->interactivemap == "Openstreetmap") {
            $format = "html";
        }
        $indCookie	= $this->params->get('indCookies', true);
        $router		= Factory::getContainer();
        $mode_sef     = Factory::getApplication()->get('sef', false);

        $base	= 'media/com_joaktree/';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtlocations.js');
        } else {
            $wa->registerAndUseScript('jtlocations', $base.'js/jtlocations.js');
        }
        Factory::getApplication()->getDocument()->addScriptOptions(
            'joaktreelocations',
            array('mode_sef' => $mode_sef,'indCookie' => $indCookie,'lists' => $this->lists,
            'format' => $format
            )
        );
        return '';
    }
    public function getDistanceSelect($distance)
    {
        $html = array();

        $html[] = '<select id="jt-map-distance" class="inputbox" size="1" onchange="jt_upd_radius();">';
        $html[] = '<option value="0" '.(($distance == 0) ? 'selected="selected" ' : '').'>0 km</option>';
        $html[] = '<option value="1" '.(($distance == 1) ? 'selected="selected" ' : '').'>1 km</option>';
        $html[] = '<option value="2" '.(($distance == 2) ? 'selected="selected" ' : '').'>2 km</option>';
        $html[] = '<option value="5" '.(($distance == 5) ? 'selected="selected" ' : '').'>5 km</option>';
        $html[] = '<option value="10" '.(($distance == 10) ? 'selected="selected" ' : '').'>10 km</option>';
        $html[] = '<option value="20" '.(($distance == 20) ? 'selected="selected" ' : '').'>20 km</option>';
        $html[] = '<option value="50" '.(($distance == 50) ? 'selected="selected" ' : '').'>50 km</option>';
        $html[] = '</select>';

        return implode("\n", $html);
    }

}
