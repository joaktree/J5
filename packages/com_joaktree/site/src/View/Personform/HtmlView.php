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

namespace Joaktree\Component\Joaktree\Site\View\Personform;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;
    //protected $state;


    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        // First load the treeId!
        $this->lists['treeId']		= $model->getTreeId();

        // Load the parameters.
        $this->params	= JoaktreeHelper::getJTParams(true);
        if ($this->params->get('siteedit', 1)) {
            $this->canDo	= JoaktreeHelper::getActions();
        } else {
            $this->canDo	= null;
        }

        // set up style sheets and javascript files
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('jtcss',JoaktreeHelper::joaktreecss());
        $wa->registerAndUseStyle('jtthemecss',JoaktreeHelper::joaktreecss($this->params->get('theme')));
        $document		= Factory::getApplication()->getDocument();
        $wa->registerAndUseScript('togglejs',JoaktreeHelper::joaktreejs('toggle.js'));

        $this->lists['lineEnd'] = $document->_getLineEnd();

        // Initialiase variables.
        $this->form					= $model->getForm();
        $this->lists['appId']		= $model->getApplicationId();
        $this->lists['appName']		= $model->getApplicationName();

        $relationId					= $model->getRelationId();
        if (isset($relationId)) {
            $this->relation					= $model->getRelation();
            $this->lists['action']			= $model->getAction();
        }
        $this->lists['action'] = (isset($this->lists['action'])) ? $this->lists['action'] : 'new';


        $this->picture			= $model->getPicture();

        $personId = $model->getPersonId();
        if (isset($personId)) {
            $this->lists['userAccess'] 	= $model->getAccess();
            $this->item					= $model->getItem();
            $this->lists['indLiving']	= $this->item->living;
        } else {
            $this->lists['userAccess'] 	= true;
            $this->lists['indLiving']	= false;
        }

        $patronym = $this->params->get('patronym', 0);
        $this->lists['indPatronym'] = ($patronym == 0) ? false : true;
        $familyname = $this->params->get('familyname', 0);
        $this->lists['indNamePreposition'] = ($familyname == 1) ? true : false;

        $this->lists[ 'CR' ] = JoaktreeHelper::getJoaktreeCR();

        if ($this->lists['userAccess']) {
            // set title
            if (isset($personId)) {
                $title = $this->item->firstName.' '.$this->item->familyName;
                $document->setTitle($title);
            }
        }

        parent::display($tpl);
    }
}
