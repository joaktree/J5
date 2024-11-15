<?php
/**
 * @package     Joaktree
 * @subpackage  Controller
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joaktree\Component\Joaktree\Administrator\Controller;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

class ApplicationController extends FormController
{
    public function save($key = null, $urlVar = null)
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde applications");
        }
        $form   = $this->input->get('jform', null, 'array');
        $model = $this->getModel('application');
        $msg = $model->save($form);

        Factory::getApplication()->enqueueMessage($msg);
        // Set the redirect based on the task.
        switch ($this->getTask()) {
            case 'apply':
                $cids = $this->input->get('cid', null, 'array');
                $cid  = (int) $cids[0];
                $link = 'index.php?option=com_joaktree&view=application&layout=form&id='.$cid;
                break;
            case 'save':
            default:
                $link = 'index.php?option=com_joaktree&view=applications';
                break;
        }
        $this->setRedirect($link, $msg);
    }

}
