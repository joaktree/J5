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
namespace Joaktree\Component\Joaktree\Administrator\Mapservice\Provider;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJProvider;

/**
 * Provider class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class Openstreetmap extends MBJProvider {

	/**
	 * The indication whether API key is needed for services.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $needsAPIkey = false;
	
	/**
	 * The copyright.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $copyright = 'Map &copy;&nbsp; OpenStreetMap</a>-auteurs,&nbsp; CC BY-SA';
		
	/**
	 * The license for services.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $license = 'http://wiki.openstreetmap.org/wiki/Nominatim_usage_policy';
	
	/**
	 * Test to see if this provider is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function test()
	{
		return true;
	}
	
	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   11.1
	 */
	public function __construct($options)
	{
		// parent::__construct($options);
	}
		
	/**
	 * Needs API key for services.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public static function needsAPIkey() {
		return self::$needsAPIkey;
	}
	
	public static function getName() {
		$name = 'OpenStreetMap';
		return $name;
	}
	
	public static function getLicense() {
		return self::$license;
	}
	
}