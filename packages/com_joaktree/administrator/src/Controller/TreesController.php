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

class TreesController extends AdminController
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // check token first
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        // create an input object
        $this->input = Factory::getApplication()->input;
        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'trees');
        }
        parent::__construct($config, $factory, $app, $input);
        $this->registerTask('saveassign', 'saveassign');
        $this->registerTask('unpublish', 'publish');
        $this->registerTask('add', 'edit');
        $this->registerTask('remove', 'delete');
        $this->registerTask('apply', 'save');
    }
    public function getModel($name = 'Trees', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function saveassign()
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde controllers/trees saveassign");
        }
        $form   = $this->input->get('jform', null, 'array');
        $model = $this->getModel('tree');
        $msg = $model->save($form);
        $link = 'index.php?option=com_joaktree&view=trees&action=assign&treeId='.$form['id'];
        $this->setRedirect($link, $msg);
    }

    public function publish()
    {
        $model = $this->getModel();
        $msg = $model->publish();
        $link = 'index.php?option=com_joaktree&view=trees';
        $this->setRedirect($link, $msg);
    }
    public function edit()
    {
        $cids	= $this->input->get('cid', [], 'array'); // RRG 2021/08/24 puis 2024/07/15
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
        $this->input->set('view', 'tree');
        $this->input->set('layout', 'form');
        parent::display();
    }
    public function delete($id = null)
    {
        $cids	= $this->input->get('cid', null, 'array');
        $model = $this->getModel('tree');
        $msg 	= $model->delete($cids);
        $link = 'index.php?option=com_joaktree&view=trees';
        $this->setRedirect($link, $msg);
    }
    public function save($recordKey = null)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde controllers/Trees");
        }
        $form   = $this->input->get('jform', null, 'array');
        $model = $this->getModel('tree');
        $msg = $model->save($form);
        // Set the redirect based on the task.
        switch ($this->getTask()) {
            case 'apply':
                $cids = $this->input->get('cid', null, 'array');
                $cid  = (int) $cids[0];
                $link = 'index.php?option=com_joaktree&view=tree&layout=form&id='.$cid;
                break;
            case 'save':
            default:
                $link = 'index.php?option=com_joaktree&view=trees';
                break;
        }
        $this->setRedirect($link, $msg);
    }
}
