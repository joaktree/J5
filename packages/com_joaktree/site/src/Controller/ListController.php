<?php
/**
 * Joomla! component Joaktree
 * file		front end joaktreelist controller - joaktreelist.php
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

namespace Joaktree\Component\Joaktree\Site\Controller;

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session; 		//replace JSession
use Joomla\CMS\Router\Route;		//replace JRoute

class ListController extends BaseController
{
    public function __construct()
    {
        // first check token
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // create an input object
        $this->input = Factory::getApplication()->input;

        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'list');
        }

        parent::__construct();

        $this->registerTask('save', 'save');
        $this->registerTask('unpublish', 'publish');
        $this->registerTask('updateLiving', 'living');
        $this->registerTask('updatePage', 'page');
    }
    public function getModel($name = 'List', $prefix = 'Joaktree', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save()
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde site controllers/jlist");
        }
        $model = $this->getModel();
        if (!isset($msg)) {
            $msg = '';
        }
        $link = 'index.php?option=com_joaktree&view=list';
        $this->setRedirect(Route::_($link), $msg);
    }

    public function publish()
    {
        $model = $this->getModel();

        $msg = $model->publish();

        $link = 'index.php?option=com_joaktree&view=list';
        $this->setRedirect(Route::_($link), $msg);
    }

    public function living()
    {
        $model = $this->getModel();

        $msg = $model->living();

        $link = 'index.php?option=com_joaktree&view=list';
        $this->setRedirect(Route::_($link), $msg);
    }

    public function page()
    {
        $model = $this->getModel();

        $msg = $model->page();

        $link = 'index.php?option=com_joaktree&view=list';
        $this->setRedirect(Route::_($link), $msg);
    }
}
