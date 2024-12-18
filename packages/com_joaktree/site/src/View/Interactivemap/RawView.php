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

namespace Joaktree\Component\Joaktree\Site\View\Interactivemap;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

/**
 * HTML View class for the Joaktree component
 */
class RawView extends HtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        // Load the parameters.
        $this->map 		= $model->getMap();

        if ($this->map->params['service'] == 'interactivemap') {
            $this->lists	= array();
            $app 			= Factory::getApplication('site');

            $this->params	= $app->getParams();
            $this->params->merge(JoaktreeHelper::getTheme(true, true));

            // add javascript
            $this->script = $this->map->getMapScript();
            if ($this->script) {
                $this->toolkit = $this->map->getToolkit();
                //if ($toolkit) {
                //	$document->addScript($toolkit);
                //}

                //$document->addScriptDeclaration($script);
                $this->lists['userAccess']	= true;
            } else {
                $this->lists['userAccess']	= false;
            }

            parent::display($tpl);
        }
    }
}
