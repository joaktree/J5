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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

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
    protected $resultSet = array();

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
        $params 	=	ComponentHelper::getParams(self::$component);
        // load component's language file
        $lang       = Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree');

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


        // set the parameters
        static $delay 		= 0;
        $geocode_pending = true;
        $subdiv = false;
        while ($geocode_pending) {
            if (!$subdiv) {
                $request_url = $this->getUrl($data);
            }
            // Try to fetch a response from the service.
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
                // RRG 02/01/2017 si paramétré à 0, on supprime la subdivision pour faciliter la géolocalisation
                // indsubdiv est dans les paramètres du composant sous forme 0 ou 1
                if (!$subdiv) { // not found, try to remove subdivision
                    $subdiv = true;// retry it once if $indSubdiv = 0
                    if (self::$indSubdiv == 0 && strpos($request_url, 'google')) {
                        $key_url = explode("&", $request_url);
                        $googlekey = '&' . $key_url[count($key_url) - 1]; // google key
                        $loc_url = explode(",", $key_url[count($key_url) - 2]);
                        $request_url = '';
                        $i = 0;
                        // rebuild beginning of url
                        while ($i < count($key_url) - 2) {
                            $request_url .= $key_url[$i] . "&";
                            $i++;
                        };
                        $i = 0;
                        // remove subdivision in last position
                        while ($i < count($loc_url) - 1) {
                            if ($i > 0) {
                                $request_url .= ',';
                            }
                            $request_url .= $loc_url[$i] ;
                            $i++;
                        };
                        $request_url .= $googlekey;
                        continue; // try again
                    }
                    if (self::$indSubdiv == 0 && strpos($request_url, 'openstreetmap')) {
                        $key_url = explode("&", $request_url);
                        $loc_url = explode("%2C", $key_url[count($key_url) - 1]); // address
                        $url = '';
                        $i = 0;
                        // remove subdivision in last position
                        while ($i < count($loc_url) - 1) {
                            if ($i > 0) {
                                $url .= "%2C";
                            }
                            $url .= $loc_url[$i] ;
                            $i++;
                        };
                        if (!$url) { // not enough info: restore
                            $url = $key_url[count($key_url) - 1];
                        }
                        $request_url = "";
                        for ($i = 0; $i <= count($key_url) - 2; $i++) {
                            $request_url .= $request_url ? "&" : '';
                            $request_url .= $key_url[$i];
                        }
                        $request_url .= '&'.$url;
                        continue; // try again
                    };
                }
                // failure to geocode
                $geocode_pending = false;
                //$this->errorNum++;		  		/// RRG 25/07/2024
                JoaktreeHelper::addLog(Text::sprintf('JT_GEOCODE_FAILED', $data->value, $status), 'joaktreemap');
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
