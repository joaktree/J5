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

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */

class MBJGeocode extends MBJService
{
    /**
     * @var    The component
     */
    protected static $component = 'com_joaktree';
    /**
     * The name of the service.
     *
     * @var    string
     * @since  1.0
     */
    protected static $service = 'geocode';

    /**
     * The max size of calls per set.
     *
     * @var    string
     * @since  1.0
     */
    protected static $maxLoadSize;
    protected static $indSubdiv;
    protected static $myProvider;
    protected static $path;
    protected static $myGeoclass;
    //protected static $resultSet = array();
    protected $resultSet = array();
    protected $log = array();


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

    public static function getInstance($options = array())
    {
        // Sanitize the service connector options.
        $options['service']  = (isset($options['service'])) ? $options['service'] : self::$service;

        return parent::getInstance($options);
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
        $keys = $this->getKeys();

        // Initialise object variables.
        self::$maxLoadSize = (isset($options['size']))
                             ? $options['size']
                             : (
                                 ($keys->maxloadsize)
                                 ? $keys->maxloadsize
                                 : 100
                             );
        self::$indSubdiv  = (isset($keys->indsubdiv)) ? $keys->indsubdiv : $params['indsubdiv']['value'];
        $myProvider  = (isset($keys->geocode)) ? $keys->geocode : $params['geocode']['value'];
        self::$path = JPATH_ADMINISTRATOR.'/components/'.self::$component.'/src/Mapservice/Geocode/'.ucfirst($myProvider).'.php';
        $myGeoclass = 'Staticmap'.$myProvider ;
        //Factory::getApplication()->enqueueMessage(self::$path, 'notice' ) ;
        require_once self::$path; //JPATH_COMPONENT_ADMINISTRATOR.'/services/geocode/google.php';
        //$this->service 	= MBJService::getInstance($options);
        parent::__construct($options);
    }

    public function findLocationBulk($data)
    {
        if (!is_array($data) || !count($data)) {
            // no object
            return false;
        }

        foreach ((array) $data as $item) {
            $status = $this->findLocation($item);
        }

        return true;
    }

    public function findLocation($data)  /// RRG &$data remplacé par $data
    {
        if (!is_object($data)) {
            // no object
            return false;
        }
        $params 	=	ComponentHelper::getParams(self::$component);

        $services = json_decode($params->get('services'));
        if ($services->interactivemap == "Openstreetmap") {
            self::$indSubdiv = 1;
        }

        // set the parameters
        static $delay 		= 0;
        $geocode_pending = true;
        while ($geocode_pending) {
            //Factory::getApplication()->enqueueMessage( self::$indSubdiv, 'notice' ) ;
            $request_url = $this->getUrl($data);

            // RRG 02/01/2017 si paramétré à 0, on supprime la subdivision pour faciliter la géolocalisation
            // indsubdiv est dans les paramètres du composant sous forme 0 ou 1
            if (self::$indSubdiv == 0) {
                $key_url = explode("&", $request_url);
                $key1_url = '&' . $key_url[1];
                $loc_url = explode(",", $key_url[0]);
                //$loc_url = explode("%2C",$request_url);
                $request_url = '';
                $i = 0;
                //while ($i <= 4) { /// RRG 20/04/2017
                while ($i < count($loc_url) - 2) {
                    $locurl = !empty($loc_url[$i]) ? $loc_url[$i] : ''; /// RRG 20/04/2017
                    //$request_url .= $loc_url[$i] . "%2C"; /// RRG 20/04/2017
                    $request_url .= $loc_url[$i] . ",";
                    //$request_url .= $loc_url[$i] . "%2C";
                    $i++;
                };
                $request_url .= $key1_url;
                //Factory::getApplication()->enqueueMessage( $request_url, 'notice' ) ;
            };

            // Try to fetch a response from the service.
            //$xml = simplexml_load_file($request_url) or die($this->service.": url not loading");
            if (!($xml = simplexml_load_file($request_url))) {
                // it is not a file - perhaps a string
                if (!($xml = simplexml_load_string($request_url))) {
                    // it is not a string ... we stop
                    throw new \Exception(Text::sprintf('MBJ_SERVICE_URL_NOT_LOADING', $this->provider->provider, $request_url));
                }
            }
            //$this->count++;		/// RRG 25/07/2024

            $status = $this->getStatus($xml);
            if (strcmp($status, "found") == 0) {
                // Successful geocode
                $geocode_pending = false;

                // Format coordinatest: Longitude, Latitude, Altitude
                $coordinates = $this->getCoordinates($xml);
                $data->longitude = $coordinates['lon'];
                $data->latitude  = $coordinates['lat'];
                $data->results   = $this->getNumberOfHits($xml);
                $data->result_address = $this->getResultAddress($xml);

                $this->resultSet = $this->setResultSet($xml);

            } elseif (strcmp($status, "wait") == 0) {
                // sent geocodes too fast
                $delay += 100000;
            } else {
                // failure to geocode
                $geocode_pending = false;
                //$this->errorNum++;		  		/// RRG 25/07/2024
                $this->log[] = Text::sprintf('JT_GEOCODE_FAILED', $data->value, $status);
                $data->results   = 0;
                $data->result_address = null;

                if (isset($this->resultSet) && is_array($this->resultSet)) {
                    $this->resultSet = array_slice($this->resultSet, 0, 0);
                } else {
                    $this->resultSet = array();
                }
            }
            usleep($delay);
        }

        $data->indServerProcessed = 1;
        return $status;
    }

    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * Mazime size of calls in a set
     *
     * @return  size
     *
     * @since   1.0
     */
    public static function getMaxLoadSize()
    {
        return self::$maxLoadSize;
    }
    public static function getIndSubdiv()
    {
        return self::$indSubdiv;
    }
}
