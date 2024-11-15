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
class Google extends MBJProvider {

	/**
	 * The indication whether API key is needed for services.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $needsAPIkey = true;
	
	/**
	 * The copyright.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $copyright = '';
		
	/**
	 * The license for services.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $license = 'https://developers.google.com/maps/terms';
	
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
		$keys = self::getKeys();
		static $APIkey;
		// Initialise object variables.
		self::$APIkey = (isset($keys->GoogleAPIkey)) ? $keys->GoogleAPIkey : '';
		// parent::__construct($options);
	}
	
	public static function getName() {
		$name = 'Google';
		return $name;
	}
	
	public static function getLicense() {
		return self::$license;
	}
	
}