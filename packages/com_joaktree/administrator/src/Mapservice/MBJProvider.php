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

namespace Joaktree\Component\Joaktree\Administrator\Mapservice;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\Component\ComponentHelper;		//replace JComponentHelper
use Joomla\Filesystem\Folder; 	//replace JFolder
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;

/**
 * Provider class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class MBJProvider extends \StdClass
{
    /**
     * The name of the service provider.
     *
     * @var    string
     * @since  1.0
     */
    protected $name;

    /**
     * The copyright.
     *
     * @var    string
     * @since  1.0
     */
    protected static $copyright;

    /**
     * The license for services.
     *
     * @var    string
     * @since  1.0
     */
    protected static $license;

    /**
     * The indication whether API key is needed for services.
     *
     * @var    string
     * @since  1.0
     */
    protected static $needsAPIkey = true;

    /**
     * The API key for services.
     *
     * @var    string
     * @since  1.0
     */
    protected static $APIkey;

    /**
     * @var    The component
     * @since  1.0
     */
    protected static $component = 'com_joaktree';

    /**
     * @var    array  MBJService instances container.
     * @since  11.1
     */
    protected static $instances = array();

    /**
     * Get a list of available provider connectors.  The list will only be populated with connectors that both
     * the class exists and the static test method returns true.  This gives us the ability to have a multitude
     * of connector classes that are self-aware as to whether or not they are able to be used on a given system.
     *
     * @return  array  An array of available provider connectors.
     *
     * @since   1.0
     */
    public static function getConnectors()
    {
        // Instantiate variables.
        $connectors = array();

        // Get a list of types.
        $types = Folder::files(JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice/Provider');

        // Loop through the types and find the ones that are available.
        foreach ((array) $types as $type) {
            // Ignore some files.
            if (($type == 'index.html')) {
                continue;
            }

            // Derive the class name from the type.
            $class = str_ireplace('.php', '', 'Joaktree\\Component\\Joaktree\\Administrator\\Mapservice\\' . ucfirst(trim($type)));

            // If the class doesn't exist, let's look for it and register it.
            if (!class_exists($class)) {
                // Derive the file path for the driver class.
                \JLoader::registerNamespace($class, JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src');
            } else {
                // If it doesn't exist we are at an impasse so move on to the next type.
                continue;
            }

            // If the class still doesn't exist we have nothing left to do but look at the next type.  We did our best.
            if (!class_exists($class)) {
                continue;
            }

            // Sweet!  Our class exists, so now we just need to know if it passes it's test method.
            if (call_user_func_array(array($class, 'test'), array())) {
                $provider = new \StdClass();
                $provider->name 	= call_user_func_array(array($class, 'getName'), array());
                $provider->license 	= call_user_func_array(array($class, 'getLicense'), array());
                $connectors[] = $provider;
                unset($provider);
            }
        }

        return $connectors;
    }

    /**
     * Test to see if this provider is available.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   1.0
     */
    public static function test()
    {
        return false;
    }

    public static function getInstance($options = array())
    {
        // Sanitize the options.
        $options['service']  = (isset($options['service'])) ? $options['service'] : self::$service;

        if (!isset($options['provider'])) {
            $params = ComponentHelper::getParams(self::$component);
            $options['provider'] = $params->get($options['service']);
        }

        // Get the options signature for the provider.
        $signature = md5($options['provider']);

        // If we already have a provider instance for these options then just use that.
        if (empty(self::$instances[$signature])) {

            // Derive the class name from the service.
            $class = 'Joaktree\\Component\\Joaktree\\Administrator\\Mapservice\\Provider\\' .ucfirst($options['provider']);

            // If the class doesn't exist, let's look for it and register it.
            if (!class_exists($class)) {

                // Derive the file path for the driver class.
                $path = JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice/Provider/'.ucfirst($options['provider']).'.php';

                // If the file exists register the class with our class loader.
                if (file_exists($path)) {
                    \JLoader::register($class, $path);
                }
                // If it doesn't exist we are at an impasse so throw an exception.
                else {
                    throw new \Exception(Text::sprintf('MBJ_SERVICE_ERROR_LOAD_PROVIDER', $options['provider']));
                }
            }

            // If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
            if (!class_exists($class)) {
                throw new \Exception(Text::sprintf('MBJ_SERVICE_ERROR_LOAD_PROVIDER', $options['provider']));
            }

            // Create our new MBJService connector based on the options given.
            try {
                $instance = new $class($options);
            } catch (\Exception $e) {
                throw new \Exception(Text::sprintf('MBJ_SERVICE_ERROR_CONNECT_PROVIDER', $e->getMessage()));
            }

            // Set the new connector to the global instances based on signature.
            self::$instances[$signature] = $instance;
        }

        return self::$instances[$signature];
    }

    /**
     * The configuration keys
     *
     * @var    string
     * @since  1.0
     */
    public static function getKeys()
    {
        return MBJService::getKeys();
    }


    /**
     * The name of the provider.
     *
     * @var    string
     * @since  1.0
     */
    public static function getName()
    {
        return self::$name;
    }

    /**
     * The copyright of the provider.
     *
     * @var    string
     * @since  1.0
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * The license for services.
     *
     * @var    string
     * @since  1.0
     */
    public static function getLicense()
    {
        return self::$license;
    }

    /**
     * Needs API key for services.
     *
     * @var    string
     * @since  1.0
     */
    public static function needsAPIkey()
    {
        return self::$needsAPIkey;
    }

    /**
     * The API key for services.
     *
     * @var    string
     * @since  1.0
     */
    public function getAPIkey()
    {
        return self::$APIkey;
    }

}
