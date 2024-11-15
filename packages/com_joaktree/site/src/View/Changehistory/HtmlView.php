<?php
/**
 * Joomla! component Joaktree
 * file		view changehistory - view.html.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

namespace Joaktree\Component\Joaktree\Site\View\Changehistory;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; //replace JViewLegacy
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

        $app 			= Factory::getApplication('site');
        $document 		= Factory::getApplication()->getDocument();

        // Load the parameters.
        $params	= $app->getParams();
        $params->merge(JoaktreeHelper::getTheme(true, true));

        // set up style sheets and javascript files
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss($params->get('theme')));

        // get user info
        $userId			= $this->get('userId');
        if (!$userId || $userId == 0) {
            $document->addScript(JoaktreeHelper::joaktreejs('jtform.js'));
        }

        // Logs
        $this->name			= $this->get('personName');
        $this->items 		= $this->get('items');
        $this->pagination	= $this->get('pagination');

        // check display method
        $tmpl				= $this->get('tmpl');
        if ($tmpl) {
            //return
            $retObject		= $this->get('returnObject');
            if (!is_object($retObject)) {
                $retObject			= new \stdClass();
                $retObject->object		= 'prsn';
            }
            $this->lists['link'] = 'index.php?option=com_joaktree'
                                  .'&view=changehistory'
                                  .'&tmpl='.$tmpl
                                  .'&retId='.base64_encode(json_encode($retObject));
        } else {
            $this->lists['link'] = 'index.php?option=com_joaktree'
                                  .'&view=changehistory';
        }

        $this->lists[ 'CR' ] = JoaktreeHelper::getJoaktreeCR();

        if (count($this->items) > 0) {
            // set title, meta title
            $title = ($this->name) ? $this->name : Text::_('JT_CHANGEHISTORY');
            $document->setTitle($title);
            $document->setMetadata('title', $title);

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
