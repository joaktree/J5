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

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\Component\ComponentHelper;		//replace JComponentHelper
use Joomla\Filesystem\Folder; 	//replace JFolder

/**
 * Service interface class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
interface MBJServiceInterface
{
    /**
     * Test to see if the connector is available.
     * @return  boolean  True on success, false otherwise.
     * @since   1.0
     */
    public static function test();
}

/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
abstract class MBJService implements MBJServiceInterface
{
    /**
     * The name of the service.
     *
     * @var    string
     * @since  1.0
     */
    private $service;

    /**
     * The name of the service driver.
     *
     * @var    string
     * @since  1.0
     */
    public $name;


    /**
     * @var    The service provider
     * @since  1.0
     */
    protected $provider;

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
     * Get a list of available services.  The list will only be populated with services that
     * the class exists
     *
     * @return  array  An array of available services.
     *
     * @since   1.0
     */
    public static function getServices()
    {
        // Instantiate variables.
        $services = array();

        // Get a list of types.
        $types = Folder::files(JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice');

        // Loop through the types and find the ones that are available.
        foreach ((array) $types as $type) {
            // Ignore some files.
            if (($type == 'index.html')
               || ($type == 'MBJService.php')
               || ($type == 'MBJProvider.php')
            ) {
                continue;
            }
            $service = str_ireplace('.php', '', $type);
            $service = strtolower(str_ireplace('MBJ', '', $service));
            $connectors = self::getConnectors($service);

            if (count($connectors)) {
                $services[] = $service;
            }
        }

        return $services;
    }

    /**
     * Get a list of available service connectors.  The list will only be populated with connectors that both
     * the class exists and the static test method returns true.  This gives us the ability to have a multitude
     * of connector classes that are self-aware as to whether or not they are able to be used on a given system.
     *
     * @return  array  An array of available service connectors.
     *
     * @since   1.0
     */
    public static function getConnectors($service = null)
    {
        // Instantiate variables.
        $connectors = array();

        // Instantiate variables.
        if (empty($service)) {
            $service = $this->service;
        }
        if (!class_exists('JLoader')) {
            require_once JPATH_LIBRARIES . '/loader.php';
        }

        // Get a list of types.
        $types = Folder::files(JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice/'.ucfirst($service));

        // Loop through the types and find the ones that are available.
        foreach ((array) $types as $type) {
            // Ignore some files.
            if (($type == 'index.html')) {
                continue;
            }

            // Derive the class name from the type.
            $class = str_ireplace('.php', '', 'Joaktree\\Component\\Joaktree\\Administrator\\Mapservice\\' . ucfirst(trim($service)) .'\\'. ucfirst(trim($type)));
            //Factory::getApplication()->enqueueMessage( $class, 'notice' ) ;
            // If the class doesn't exist, let's look for it and register it.
            if (!class_exists($class)) {
                // Derive the file path for the driver class.
                $path = JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice/'.$service.'/'.$type;

                // If the file exists register the class with our class loader.
                if (file_exists($path)) {
                    \JLoader::registerNamespace($class, JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src');
                }
                // If it doesn't exist we are at an impasse so move on to the next type.
                else {
                    continue;
                }
            }

            // If the class still doesn't exist we have nothing left to do but look at the next type.  We did our best.
            if (!class_exists($class)) {
                continue;
            }

            // Sweet!  Our class exists, so now we just need to know if it passes it's test method.
            if (call_user_func_array(array($class, 'test'), array())) {
                $connectors[] = str_ireplace('.php', '', $type);
            }
        }

        return $connectors;
    }

    /**
     * Method to return a MBJService instance based on the given options.  There are two global options.
     * Instances are unique to the given options and new objects are only created when a unique options array is
     * passed into the method.  This ensures that we don't end up with unnecessary resources.
     *
     * @param   array  $options  Parameters to be passed to the service driver.
     * @return  MJBService  A service object.
     * @since   1.0
     */
    public static function getInstance($options = array())
    {
        // Sanitize the service connector options.
        $options['service']  = (isset($options['service'])) ? $options['service'] : self::$service;
        $myService = $options['service'];
        //throw new MBJServiceException(Text::sprintf($options['service']));
        if (!isset($options['provider'])) {
            $keys = self::getKeys();
            //$options['provider'] = $keys->$options['service'];
            $options['provider'] = (isset($keys->$myService)) ? $keys->$myService : $params[$myService]['value']; //$keys->$options['geocode'];
            $myProvider  = $options['provider'];
            //$myService = (isset($keys->service)) ? $keys->service : $params[0]['value'];
        }
        // Get the options signature for the database connector.
        $signature = md5(serialize($options));

        // If we already have a database connector instance for these options then just use that.
        if (empty(self::$instances[$signature])) {

            // Derive the class name from the service.
            $class = 'Joaktree\\Component\\Joaktree\\Administrator\\Mapservice\\' . ucfirst(trim($options['service'])) .'\\'. ucfirst(trim($options['provider']));
            //$class = 'MBJService' . ucfirst($options['service']).ucfirst($myProvider);
            //$class = 'MBJService' . ucfirst($myService).ucfirst($myProvider);
            // If the class doesn't exist, let's look for it and register it.
            if (!class_exists($class)) {

                // Derive the file path for the driver class.
                $path = JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice/'.ucfirst($options['service']).'/'.ucfirst($options['provider']).'.php';
                //$path = JPATH_COMPONENT_ADMINISTRATOR.'/services'.DS.$options['service'].DS.$options['provider'].'.php';
                //throw new MBJServiceException(Text::sprintf($path));

                // If the file exists register the class with our class loader.
                if (file_exists($path)) {
                    \JLoader::registerNamespace($class, JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src');
                }
                // If it doesn't exist we are at an impasse so throw an exception.
                else {
                    throw new \Exception(Text::sprintf('MBJ_SERVICE_ERROR_LOAD_SERVICE_DRIVER', $options['service'], $options['provider']));
                    //throw new MBJServiceException(Text::sprintf('fichier non trouvé'));
                }
            }

            // If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
            if (!class_exists($class)) {
                throw new \Exception(Text::sprintf('MBJ_SERVICE_ERROR_LOAD_SERVICE_DRIVER', $options['service'], $options['provider']));
                //throw new MBJServiceException(Text::sprintf('classe non trouvée', $class));
            }

            // Create our new MBJService connector based on the options given.
            try {
                $instance = new $class($options);
            } catch (\Exception $e) {
                throw new \Exception(Text::sprintf('MBJ_SERVICE_ERROR_CONNECT_DATABASE', $e->getMessage()));
            }

            // Set the new connector to the global instances based on signature.
            self::$instances[$signature] = $instance;
        }

        return self::$instances[$signature];
    }

    /**
     * Constructor.
     *
     * @param   array  $options  List of options used to configure the object
     *
     * @since   1.0
     */
    protected function __construct($options)
    {
        // Initialise object variables.
        $this->service = (isset($options['service'])) ? $options['service'] : '';
        $this->provider = (isset($options['provider'])) ? MBJProvider::getInstance($options) : '';
        /*$this->count = 0;
        $this->errorNum = 0;
        $this->log = array();*/
        self::setLanguage();
    }

    /**
     * Method that retrieves the set of keys from the config.
     *
     * @return  keys
     *
     * @since   1.0
     */
    public static function getKeys()
    {
        static $keys;

        if (!isset($keys)) {
            $params = ComponentHelper::getParams(self::$component);
            $keys = new \stdClass();
            if ($params->get('services')) {
                $keys = json_decode($params->get('services'));
            }
        }
        return $keys;
    }

    /**
     * Method that set the specific language file
     *
     * @return  boolean
     *
     * @since   1.0
     */
    public static function setlanguage()
    {
        static $language_is_set;

        if (!isset($language_is_set)) {
            //load the GedCom language file
            $lang 	= Factory::getLanguage();
            $lang->load(self::$component.'.services');
            $language_is_set = true;
        }

        return true;
    }

    /**
     * Method that provides access to the service.
     *
     * @return  service
     *
     * @since   1.0
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Method that provides access to the provider.
     *
     * @return  provider
     *
     * @since   1.0
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get the total number of calls executed by the service.
     *
     * @return  integer
     *
     * @since   1.0
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Get the service call log.
     *
     * @return  array  Calls executed by the service.
     *
     * @since   1.0
     */
    public function getLog()
    {
        return $this->log;
    }

    //	/**
    //	 * Get the version of the service connector
    //	 *
    //	 * @return  string  The service connector version.
    //	 *
    //	 * @since   1.0
    //	 */
    //	abstract public function getVersion();
    public function getVersion()
    {
        return $this->version;
    }

    public function _($method, &$data = null, $options = array())
    {

        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method), $data, $options);
        } else {
            return false;
        }
    }

}
