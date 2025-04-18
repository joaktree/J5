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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;

class MapsController extends AdminController
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // check token first
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        // create an input object
        $this->input = Factory::getApplication()->input;
        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'maps');
        }
        parent::__construct($config, $factory, $app, $input);
        $this->registerTask('add', 'edit');
        $this->registerTask('remove', 'delete');
    }
    public function getModel($name = 'Maps', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function edit()
    {
        $cids	= $this->input->get('cid', [], 'array');
        if (empty($cids)) {
            $cid = 0;
        } else {
            if (!is_null($cids)) {
                $cid  	= (int) $cids[0];
            } else {
                $cid = 1;
            }
        }
        $this->input->set('id', $cid);
        $this->input->set('view', 'map');
        $this->input->set('layout', 'form');
        parent::display();
    }

    public function delete($id = null)
    {
        $cids	= $this->input->get('cid', [], 'array');
        $model = $this->getModel('Map');
        $msg 	= $model->delete($cids);
        $link = 'index.php?option=com_joaktree&view=maps';
        $this->setRedirect($link, $msg);
    }

    public function apply()
    {
        $form   = $this->input->get('jform', null, 'array');
        $model = $this->getModel('Map');
        $msg = $model->save($form);
        $link = 'index.php?option=com_joaktree&view=map&layout=form&id='.$form['id'];
        $this->setRedirect($link, $msg);
    }
    public function save($recordKey = null)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde controllers/maps");
        }
        $form   = $this->input->get('jform', null, 'array');
        $model = $this->getModel('Map');
        $msg = $model->save($form);
        $link = 'index.php?option=com_joaktree&view=maps';
        $this->setRedirect($link, $msg);
    }

    public function cancel()
    {
        $link = 'index.php?option=com_joaktree&view=maps';
        $this->setRedirect($link);
    }

    public function locations()
    {
        $link = 'index.php?option=com_joaktree&view=locations';
        $this->setRedirect($link);
    }
}
