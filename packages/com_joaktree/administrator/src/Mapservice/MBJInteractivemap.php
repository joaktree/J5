<?php
/**
 * @package     MapsByJoaktree
 * @subpackage  Service
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joaktree\Component\Joaktree\Administrator\Mapservice;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;

/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class MBJInteractivemap extends MBJService
{
    /**
     * The name of the service.
     *
     * @var    string
     * @since  1.0
     */
    private $service = 'interactivemap';

    /**
     * Test to see if service exists.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   1.0
     */
    public static function test()
    {
        return false;
    }

    public function getStyleDeclaration()
    {
        $style = 'html { height: 100% } '."\n"
                .'body { height: 100%; margin: 0; padding: 0 } '."\n"
                .'#map_canvas { height: 100% }'."\n";
        return $style;
    }

    public function fetch($data, $options = array())
    {
        return false;
    }

    public function getToolkit()
    {
        return false;
    }

    public static function isActivated()
    {
        $settings = MBJService::getKeys();
        if (!empty($settings)) {
            $interactivemapAPIkey = $settings->interactivemap.'APIkey';
        }
        if ((empty($settings->interactivemap))
           || ((!empty($settings->interactivemap))
              && isset($settings->$interactivemapAPIkey)
              && empty($settings->$interactivemapAPIkey))
        ) {
            // Interactive maps is not correctly activated
            return false;
        } else {
            // everything seems to be ok
            return true;
        }
    }

}
