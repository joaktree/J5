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
        // A person linked to a menu item the user may not see must not be
        // reachable through another (public) link either.
        // Menu items without a person (lists, search, ...) are generic entry
        // points: Joomla core already checks the access of the active menu item,
        // so they must not block other pages with the same view.
        $reqPersonId = (string) $this->input->get('personId', '');
        if ($reqPersonId !== '') {
            foreach ($items as $item) {
                if (in_array($item->access, $levels)) {
                    continue;
                }
                $personId = str_replace('!', '', (string) $item->getParams()->get('personId', ''));
                if ($personId !== ''
                && $personId === $reqPersonId
                && ($item->query['option'] ?? '') === $this->input->get('option')
                && ($item->query['view'] ?? '') === $this->input->get('view')
                ) {
                    $authorized = false;
                    break;
                }
            }
        }

        if (! $authorized) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

}
