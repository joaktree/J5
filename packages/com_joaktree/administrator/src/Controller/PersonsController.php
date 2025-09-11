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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session; 		//replace JSession

class PersonsController extends AdminController
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // check token first
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        // create an input object
        $this->input = Factory::getApplication()->getInput();
        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'persons');
        }
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('save', 'save');
        // three tasks for publishing
        $this->registerTask('publish', 'publish');
        $this->registerTask('unpublish', 'unpublish');
        $this->registerTask('publishAll', 'publishAll');
        $this->registerTask('unpublishAll', 'unpublishAll');
        // three tasks for living
        $this->registerTask('updateLiving', 'living');
        $this->registerTask('livingAll', 'livingAll');
        $this->registerTask('notLivingAll', 'notLivingAll');
        // three tasks for page switch
        $this->registerTask('updatePage', 'page');
        $this->registerTask('pageAll', 'pageAll');
        $this->registerTask('noPageAll', 'noPageAll');
        // four tasks for map switch
        $this->registerTask('changeMap', 'changeMap');
        $this->registerTask('mapStatAll', 'mapStatAll');
        $this->registerTask('mapDynAll', 'mapDynAll');
        $this->registerTask('noMapAll', 'noMapAll');
    }
    public function getModel($name = 'Persons', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    public function publish()
    {
        $model = $this->getModel();
        $msg = $model->publish();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function unpublish()
    {
        $model = $this->getModel();
        $msg = $model->unpublish();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function publishAll()
    {
        $model = $this->getModel();
        $msg = $model->publishAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function unpublishAll()
    {
        $model = $this->getModel();
        $msg = $model->unpublishAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function living()
    {
        $model = $this->getModel();
        $msg = $model->living();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function livingAll()
    {
        $model = $this->getModel();
        $msg = $model->livingAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function notLivingAll()
    {
        $model = $this->getModel();
        $msg = $model->notLivingAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function page()
    {
        $model = $this->getModel();
        $msg = $model->page();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function pageAll()
    {
        $model = $this->getModel();
        $msg = $model->pageAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function noPageAll()
    {
        $model = $this->getModel();
        $msg = $model->noPageAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }

    public function changeMap()
    {
        $model = $this->getModel();
        $msg = $model->changeMap();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }
    public function mapStatAll()
    {
        $model = $this->getModel();
        $msg = $model->mapStatAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }
    public function mapDynAll()
    {
        $model = $this->getModel();
        $msg = $model->mapDynAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }
    public function noMapAll()
    {
        $model = $this->getModel();
        $msg = $model->noMapAll();
        $link = 'index.php?option=com_joaktree&view=persons';
        $this->setRedirect($link, $msg);
    }
}
