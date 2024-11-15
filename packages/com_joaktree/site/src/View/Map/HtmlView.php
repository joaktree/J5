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

namespace Joaktree\Component\Joaktree\Site\View\Map;

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
        $document 		= Factory::getApplication()->getDocument();

        // Load the parameters.
        $this->map 		= $this->get('map');
        $this->params	= $app->getParams();
        $this->params->merge(JoaktreeHelper::getTheme(true, true));


        // set up style sheets and javascript files
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss($this->params->get('theme')));

        if (isset($this->map->params) && $this->map->params['service'] == 'staticmap') {
            // Get data from the model
            $this->mapview 				= $this->get('mapView');
            $this->lists['userAccess']	= ($this->mapview) ? true : false;

            if ($this->lists['userAccess']) {
                // set title, meta title
                if ($this->map->params['name']) {
                    $title = $this->map->params['name'];
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

                if ($this->params->get('robots')) {
                    $document->setMetadata('robots', $this->params->get('robots'));
                }
            }
        }

        if (isset($this->map->params) && $this->map->params['service'] == 'interactivemap') {
            $this->lists[ 'href' ] = 'index.php?option=com_joaktree'
                                              .'&tmpl=component'
                                              .'&format=raw'
                                              .'&view=interactivemap'
                                              .'&mapId='.$this->map->params['id'];
        }

        // user interaction
        $this->lists[ 'mapHtmlId' ] = 'jt-map-id';
        $this->lists[ 'uicontrol' ] = $this->map->getUIControl($this->lists[ 'mapHtmlId' ]);

        // copyright
        $this->lists[ 'CR' ]		 = JoaktreeHelper::getJoaktreeCR();

        parent::display($tpl);
    }

}
