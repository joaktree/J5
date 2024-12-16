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

class SourceController extends BaseController
{

    public function getModel($name = 'Source', $prefix = 'Joaktree', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function edit()
    {
        $appId 	= $this->input->get('appId', [], 'int');
        $cids	= $this->input->get('cid', [], 'array');
        $action = $this->input->get('action');

        $link =  'index.php?option=com_joaktree'
                        .'&view=source'
                        .'&layout=form_source'
                        .'&appId='.$appId
                        .'&sourceId='.$cids[0];

        if ($action == 'select') {
            $link .= '&tmpl=component'
                    .'&action='.$action;
        }
        if (!isset($msg)) {
            $msg = '';
        }
        $this->setRedirect(Route::_($link), $msg);
    }

    public function cancel()
    {
        $appId 	= $this->input->get('appId', null, 'int');
        $action = $this->input->get('action');

        $link = 'index.php?option=com_joaktree'
                        .'&view=sources'
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
            Factory::getApplication()->enqueueMessage("Sauvegarde site controllers/source");
        }
        $model = $this->getModel();

        $form   = $this->input->get('jform', null, 'array');
        $appId 	= $this->input->get('appId', null, 'int');
        $action = $this->input->get('action');

        $ret = $model->save($form);

        if ($ret) {
            $link =  'index.php?option=com_joaktree'
                    .'&view=sources'
                    .'&appId='.$appId
                    .'&retId='.$ret;

            if ($action == 'select') {
                $link .= '&tmpl=component'
                        .'&action='.$action;
            }

            $msg = '';
        } else {
            $link =  'index.php?option=com_joaktree'
                    .'&view=sources'
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

        $ret = $model->delete($appId, $cids[0]);

        if ($ret) {
            $msg = Text::sprintf('JT_DELETED', $ret);
        } else {
            $msg = Text::_('JT_NOTAUTHORISED');
        }

        $link =  'index.php?option=com_joaktree'
                .'&view=sources'
                .'&appId='.$appId;
        if (!isset($msg)) {
            $msg = '';
        }
        $this->setRedirect(Route::_($link), $msg);
    }
}
