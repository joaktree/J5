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

namespace Joaktree\Component\Joaktree\Site\Controller;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\BaseController;

class RepositoryController extends BaseController
{
    public function getModel($name = 'Repository', $prefix = '', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function display($cachable = false, $urlparams = array())
    {
        $action = $this->input->get('action');

        if ($action == 'select') {
            $this->input->set('tmpl', 'component');
            $this->input->set('action', $action);
        }

        parent::display();
    }

    public function edit()
    {

        $appId 	= $this->input->get('appId', null, 'int');
        $cids	= $this->input->get('cid', null, 'array');
        $action = $this->input->get('action');
        $link =  'index.php?option=com_joaktree'
                        .'&view=repository'
                        .'&layout=form_repository'
                        .'&appId='.$appId
                        .'&repoId='.$cids[0];

        if ($action == 'select') {
            $link .= '&tmpl=component'
                    .'&action='.$action;
        }
        $msg = ($msg ?? '') ;

        $this->setRedirect(Route::_($link), $msg);
    }

    public function cancel()
    {
        $appId 	= $this->input->get('appId', null, 'int');
        $action = $this->input->get('action');

        $link = 'index.php?option=com_joaktree'
                        .'&view=repositories'
                        .'&appId='.$appId;

        if ($action == 'select') {
            $link .= '&tmpl=component'
                    .'&action='.$action;
        }
        if (!isset($msg)) {
            $msg = '';
        }
        $this->setRedirect(Route::_($link), $msg);
    }

    public function save()
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde site controllers/repository");
        }
        $model = $this->getModel();

        $form   = $this->input->get('jform', null, 'array');
        $appId 	= $this->input->get('appId', null, 'int');
        $action = $this->input->get('action');

        $ret = $model->save($form);

        if ($ret) {
            $link =  'index.php?option=com_joaktree'
                    .'&view=repositories'
                    .'&appId='.$appId
                    .'&retId='.$ret;

            if ($action == 'select') {
                $link .= '&tmpl=component'
                        .'&action='.$action;
            }

            $msg = '';
        } else {
            $link =  'index.php?option=com_joaktree'
                    .'&view=repositories'
                    .'&appId='.$appId;
            $msg = Text::_('JT_NOTAUTHORISED');
        }
        $this->setRedirect(Route::_($link), $msg);
    }

    public function delete()
    {
        $model = $this->getModel();

        $form   = $this->input->get('jform', null, 'array');
        $appId 	= $this->input->get('appId', null, 'int');
        $cids	= $this->input->get('cid', null, 'array');

        $msg = $model->delete($appId, $cids[0]);

        $link =  'index.php?option=com_joaktree'
                .'&view=repositories'
                .'&appId='.$appId;
        $this->setRedirect(Route::_($link), $msg);
    }
}
