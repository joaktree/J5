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

namespace Joaktree\Component\Joaktree\Site\View\Repository;

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
    protected $form;
    protected $item;

    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        if ($tpl == null) {
            $this->lists = array();
            $app 			= Factory::getApplication('site');
            $document = Factory::getApplication()->getDocument();

            // Load the parameters.
            $this->params	= $app->getParams();
            $this->params->merge(JoaktreeHelper::getGedCom());
            $this->params->merge(JoaktreeHelper::getTheme(true, true));

            if ($this->params->get('siteedit', 1)) {
                $this->canDo	= JoaktreeHelper::getActions(false);
            } else {
                $this->canDo	= null;
            }

            // set up style sheets and javascript files
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
            $wa->registerAndUseStyle('jtthemecss',JoaktreeHelper::joaktreecss($this->params->get('theme')));

            // Initialiase variables.
            $this->form					= $model->getForm();
            $this->item					= $model->getItem();
            $this->lists['userAccess']	= $model->getAccess();

            $this->lists['action']		= $model->getAction();
            if ($this->lists['action'] == 'select') {
                $this->lists['link'] = 'index.php?option=com_joaktree'
                                      .'&view=repository'
                                      .'&tmpl=component'
                                      .'&action='.$this->lists['action'];
            } else {
                $this->lists['link'] = 'index.php?option=com_joaktree'
                                      .'&view=repository';
            }

            $this->lists[ 'CR' ] = JoaktreeHelper::getJoaktreeCR();
        }

        if ($this->lists['userAccess']) {
            // set title, meta title
            if ($this->params->get('gedcomName')) {
                $title = $this->params->get('gedcomName');
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

        parent::display($tpl);
    }
}
