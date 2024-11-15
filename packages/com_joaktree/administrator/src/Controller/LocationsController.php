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

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session; 		//replace JSession

class LocationsController extends AdminController
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // check token first
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        // create an input object
        $this->input = Factory::getApplication()->input;
        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'locations');
        }
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('apply', 'save');
        $this->registerTask('purgelocations', 'purge');
    }
    public function getModel($name = 'Locations', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function edit()
    {
        $cids	= $this->input->get('cid', null, 'array');
        $cid  = (int) $cids[0];
        $this->input->set('id', $cid);

        $this->input->set('view', 'location');
        $this->input->set('layout', 'form');

        parent::display();
    }

    public function save()
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde controllers/locations");
        }
        $form   = $this->input->get('jform', null, 'array');

        $model = $this->getModel('location');
        $msg = $model->save($form);
        $link 	= 'index.php?option=com_joaktree&view=locations';

        $this->setRedirect($link, $msg);
    }

    public function purge()
    {
        $model 	= $this->getModel();

        $msg 	= $model->purgeLocations();
        $link 	= 'index.php?option=com_joaktree&view=locations';

        $this->setRedirect($link, $msg);
    }

    public function geocode()
    {
        $model 	= $this->getModel();

        $msg 	= $model->geocode();
        $link 	= 'index.php?option=com_joaktree&view=locations';

        $this->setRedirect($link, $msg);
    }

    public function resetlocation()
    {
        $model 	= $this->getModel();

        $msg 	= $model->resetlocation();
        $link 	= 'index.php?option=com_joaktree&view=locations';

        $this->setRedirect($link, $msg);
    }


}
