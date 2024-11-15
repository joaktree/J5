<?php
/**
 * @package     Joaktree
 * @subpackage  Extension
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joaktree\Component\Joaktree\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

class ApplicationsComponent extends MVCComponent implements RouterServiceInterface
{
    use RouterServiceTrait;
}
