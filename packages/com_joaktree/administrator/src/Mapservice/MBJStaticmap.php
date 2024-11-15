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
class MBJStaticmap extends MBJService {
	/**
	 * The name of the service.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $service = 'staticmap';
	
	/**
	 * Test to see if service exists.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function test() {
		return false;
	}
	
	public static function getInstance($options = array()) {
		// Sanitize the service connector options.
		$options['service']  = (isset($options['service'])) ?  $options['service'] : self::$service;
		return parent::getInstance($options);
	}
	
	public function fetch($data, $options = array()) {
		return false;
	}
		
	
}
