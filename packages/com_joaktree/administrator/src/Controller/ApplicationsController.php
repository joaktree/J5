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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\MVC\Controller\AdminController;

class ApplicationsController extends AdminController
{
    public function getModel($name = 'Applications', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
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
        $this->input->set('view', 'application');
        $this->input->set('layout', 'form');
        parent::display();
    }
    public function add()
    {
        $app = Factory::getApplication();
        $this->input->set('id', null);
        $this->input->set('view', 'application');
        $this->input->set('layout', 'form');
        parent::display();
    }
    public function delete($id = null)
    {
        // first: delete the GedCom data
        $model1 = $this->getModel();
        $msg = $model1->deleteGedCom();
        // second: delete the records
        $cids	= $this->input->get('cid', null, 'array');
        $model 	= $this->getModel('application');
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
            $msg .= '<br />'.Text::_('JTAPPS_MESSAGE_DELETED').'; ';
        }
        if ($msgnotdeleted) {
            $msg .= '<br />'.Text::_('JTAPPS_MESSAGE_NOTDELETED');
        }
        $link = 'index.php?option=com_joaktree&view=applications';
        $this->setRedirect($link, $msg);
    }

    public function import()
    {

        $model 	= $this->getModel('importgedcom');
        $model->initialize();
        $msg 	= null;
        $link 	= 'index.php?option=com_joaktree&view=importgedcom';
        $this->setRedirect($link, $msg);
    }

    public function export()
    {
        $model 	= $this->getModel('exportgedcom');
        $model->initialize();
        $msg 	= null;
        $link 	= 'index.php?option=com_joaktree&view=exportgedcom';
        $this->setRedirect($link, $msg);
    }
    public function clearGedCom()
    {
        $model = $this->getModel('applications');
        $msg = $model->clearGedCom();
        $link = 'index.php?option=com_joaktree&view=applications';
        $this->setRedirect($link, $msg);
    }
    public function deleteGedCom()
    {
        $model = $this->getModel('applications');
        $msg = $model->deleteGedCom();
        $link = 'index.php?option=com_joaktree&view=applications';
        $this->setRedirect($link, $msg);
    }
}
