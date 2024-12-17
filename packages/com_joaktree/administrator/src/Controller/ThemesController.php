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

class ThemesController extends AdminController
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // check token first
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        parent::__construct($config, $factory, $app, $input);
        //Get View
        if ($this->input->getCmd('view') == '') {
            $this->input->set('view', 'themes');
        }
        $this->registerTask('add', 'edit');
        $this->registerTask('remove', 'delete');
        $this->registerTask('apply', 'save');
    }
    public function getModel($name = 'Themes', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    public function setDefault()
    {
        $cids	= $this->input->get('cid', array(), 'array');
        $model	= $this->getModel();
        $msg  	= Text::_('');
        $cid	= (int) $cids[0];
        $msg	= $msg . $model->setDefault($cid);
        $link	= 'index.php?option=com_joaktree&view=themes';
        $this->setRedirect($link, $msg);
    }
    public function cancel()
    {
        $link = 'index.php?option=com_joaktree&view=themes';
        $this->setRedirect($link, null);
    }
    public function edit()
    {
        $cids	= $this->input->get('cid', array(), 'array');
        $cid	= isset($cids[0]) ? (int) $cids[0] : '';
        $this->input->set('id', $cid);
        $this->input->set('view', 'theme');
        $this->input->set('layout', 'form');
        parent::display();
    }
    public function delete($id = null)
    {
        $cids	= $this->input->get('cid', array(), 'array');
        $model 	= $this->getModel('Theme');
        $msgdeleted = false;
        $msgnotdeleted = false;
        foreach ($cids as $cid_num => $cid) {
            $id  = (int) $cid;
            $ret = $model->delete($id);
            if (!$ret) {
                $msgnotdeleted = true;
            } else {
                $msgdeleted = true;
            }
        }
        if ($msgdeleted) {
            $msg = Text::_('JTTHEME_MESSAGE_DELETED').'; ';
        }
        if ($msgnotdeleted) {
            $msg = Text::_('JTTHEME_MESSAGE_NOTDELETED');
        }
        $link = 'index.php?option=com_joaktree&view=themes';
        $this->setRedirect($link, $msg);
    }
    public function save($recordKey = null)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde controllers/themes");
        }
        $form	= $this->input->get('jform', array(0), 'post', 'array');
        $model = $this->getModel('Theme');
        $msg = $model->save($form);
        // Set the redirect based on the task.
        switch ($this->getTask()) {
            case 'apply':
                $cid = $model->getState('theme' . '.id');
                $caller	= $this->input->get('caller');
                $link 	= 'index.php?option=com_joaktree&view=theme&layout='.$caller.'&id='.$cid;
                break;
            case 'save':
            default:
                $link = 'index.php?option=com_joaktree&view=themes';
                break;
        }
        $this->setRedirect($link, null);
    }
    public function edit_css()
    {
        $cids	= $this->input->get('cid', array(), 'array');
        $cid  = (int) $cids[0];
        $this->input->set('id', $cid);
        $this->input->set('view', 'theme');
        $this->input->set('layout', 'editcss');
        parent::display();
    }
}
