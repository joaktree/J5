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

namespace Joaktree\Component\Joaktree\Administrator\Controller;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;

class SettingsController extends AdminController
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // first check token
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // create an input object
        $this->input = Factory::getApplication()->getInput();

        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'settings');
        }
        parent::__construct($config, $factory, $app, $input);
        $this->registerTask('unpublish', 'publish');
    }
    public function getModel($name = 'Settings', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function publish()
    {
        $layout = $this->input->get('layout');
        $model = $this->getModel();

        $msg = $model->publish();

        $link = 'index.php?option=com_joaktree&view=settings&layout='.$layout;
        $this->setRedirect($link);
    }

    public function save($recordKey = null)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde controllers/jt_settings");
        }
        $layout = $this->input->get('layout');
        $model 	= $this->getModel();

        $msg 	= $model->save($layout);

        $link 	= 'index.php?option=com_joaktree&view=settings&layout='.$layout;
        $this->setRedirect($link);

    }

}
