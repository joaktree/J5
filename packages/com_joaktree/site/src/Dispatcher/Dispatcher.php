<?php

/**
 * @package     Joaktree
 * @subpackage  Site
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joaktree\Component\Joaktree\Site\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Component\ComponentHelper;

\defined('_JEXEC') or die;

/**
 * ComponentDispatcher class for com_joaktree
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Dispatch a controller task. Redirecting the user if appropriate.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function checkAccess()
    {
        parent::checkAccess();
        $component = ComponentHelper::getComponent('com_joaktree');
        // get all menus items
        $items     = $this->app->getMenu()->getItems(['component_id','access'], [$component->id,null]);
        $authorized = true;
        $user = $this->app->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        // check if access for request
        foreach ($items as $item) {
            $personId = str_replace('!', '', $item->getParams()->get('personId', ''));
            if ($item->query['option'] == $this->input->get('option')
            && $item->query['view'] == $this->input->get('view')
            && $personId == $this->input->get('personId')
            && !in_array($item->access, $levels)
            ) {
                $authorized = false;
            }
        }

        if (! $authorized) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

}
