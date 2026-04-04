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
        $table	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Locations');
        // check if address already in locations table
        $info = $table->checkLocationExists($data->value);
        if ($info && property_exists($info, 'longitude') && $info->longitude) {
            $data->longitude = $info->longitude;
            $data->latitude  = $info->latitude;
            $data->results   = 1;
            $data->result_address = $info->resultValue;
            $resultSet  = array();
            $object		 = new \stdClass();
            $object->lon = (float)  $info->longitude;
            $object->lat = (float)  $info->latitude;
            $object->adr = (string) $info->resultValue;
            $resultSet[] = $object;
            $this->resultSet = $resultSet;
            return "found";
        }
        // set the parameters
        static $delay 		= 0;
        $geocode_pending = true;
        $response = "";
        $subdiv = false;
        $retry = 0;
        while ($geocode_pending) {
            if (!$subdiv) {
                $request_url = $this->getUrl($data);
            }
            libxml_use_internal_errors(true);
            // Try to fetch a response from the service.
            if (strpos($request_url, 'openstreetmap')) {
                $xml = $this->get_xml($request_url);
                $response = $xml;
            } else { // google
                $xml = simplexml_load_file($request_url);
            }
            if ($xml) {
                $xml = simplexml_load_string($xml);
            }
            libxml_clear_errors();
            if (!$xml) {
                JoaktreeHelper::addLog(Text::sprintf('JT_GEOCODE_FAILED', $data->value, $status), 'joaktreemap');
                if (strpos($request_url, 'openstreetmap')) {
                    $titleerror = "";
                    if (strpos($response,'title')) {
                        $titlepos = strpos($response,'title') + 6;
                        $endtitle = strpos($response,'</title>');
                        $titlelength = $endtitle - $titlepos;
                        $titleerror = substr($response,$titlepos,$titlelength);
                    }
                    if ($titleerror == "429 Too many requests") {
                        if (!$retry) { // retry once
                            $retry += 1; 
                            JoaktreeHelper::addLog(Text::sprintf('JT_GEOCODE_FAILED', $titleerror, "Retry openstreetmap"), 'joaktreemap');
                            sleep(2);
                            continue; // retry
                        }
                    }
                    JoaktreeHelper::addLog(Text::sprintf('JT_GEOCODE_FAILED', $titleerror, "other error openstreetmap"), 'joaktreemap');
                }
                $geocode_pending = false;
                $status = 'notfound';
                $data->results   = 0;
                $data->result_address = null;
                continue;
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
                $delay = 1; // 1 second
            } else {
                // RRG 02/01/2017 si paramétré à 0, on supprime la subdivision pour faciliter la géolocalisation
                // indsubdiv est dans les paramètres du composant sous forme 0 ou 1
                if (!$subdiv) { // not found, try to remove subdivision
                    $subdiv = true;// retry it once if $indSubdiv = 0
                    $retry = 0;
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
                        if (count($loc_url) > 1) { // more than one field
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
                    }
                    if (self::$indSubdiv == 0 && strpos($request_url, 'openstreetmap')) {
                        sleep(2); // add delay for openstreetmap : 1 request per second max.
                        $key_url = explode("&", $request_url);
                        $loc_url = explode("%2C", $key_url[count($key_url) - 1]); // address
                        $url = '';
                        $i = 0;
                        if (count($loc_url) > 1) {
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
                        }
                    }
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
            sleep($delay);
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
    public static function get_xml($url)
    {
        $agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Exécuter la requête et obtenir la réponse
        $response = curl_exec($ch);
        return $response;
    }
}
