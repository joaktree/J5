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
namespace Joaktree\Component\Joaktree\Administrator\Mapservice\Geocode;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;

/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class Google extends MBJGeocode {

	/**
	 * Test to see if service for this provider is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function test()
	{
		return true;
	}
	
	public static function parameters() {
		static $params;
		
		if (!isset($params)) {
			$params = array();
			
			$params['indHttps']['type']  = 'boolean';
			$params['indHttps']['value'] = null;
			$params['format']['type']    = 'string';
			$params['format']['value']   = 'xml';
			$params['sensor']['type']    = 'boolean';
			$params['sensor']['value']   = 'false';
			$params['country']['type']   = 'string';
			$params['country']['value']  = null;
			$params['language']['type']  = 'string';
			$params['language']['value'] = null;
			
		}
		
		return $params;
	}
	
	protected static function getBaseUrl() {
		static $baseUrl;
		
		if (!isset($baseUrl)) {
			$keys   = self::getKeys();

			$params = self::parameters(); 	
			$base_url = '';
			
			$indHttps  = (isset($keys->indHttps)) ? $keys->indHttps : $params['indHttps']['value'];
			$base_url .= (($indHttps) ? 'https' : 'http').'://';
			//$base_url .= 'maps.google.com/maps/geo';
			$base_url .= 'maps.googleapis.com/maps/api/geocode/xml';
			//$base_url .= '?key='.$this->provider->getAPIkey();
			//$base_url .= '?key='.$keys->googleAPIkey;
			
			//$sensor    = (isset($keys->sensor)) ? $keys->sensor : $params['sensor']['value'];
			//$base_url .= ($sensor) ? '?sensor='.$sensor : '';

			$format    = (isset($keys->format)) ? $keys->format : $params['format']['value'];
			//$base_url .= ($format) ? '&'.$format : '';
			//$base_url .= ($format) ? '&output='.$format : '';
			
			$country   = (isset($keys->country)) ? $keys->country : $params['country']['value'];
			$base_url .= ($country) ? '&region='.$country : '';
			
			$language  = (isset($keys->language)) ? $keys->language : $params['language']['value'];
			$base_url .= ($language) ? '&language='.$language : '';
		}
		return $base_url;
		
	}
	
	public static function getUrl(&$data) {
		$url  = self::getBaseUrl();
		//$url .= '&address='.urlencode($data->value);
		//$base_url .= '&key='.$this->provider->getAPIkey();
		$keys = self::getKeys();
		$APIkey = (isset($keys->GoogleAPIkey)) ? $keys->GoogleAPIkey : '';
		$url .= '?address=' . $data->value . '&key='. trim($APIkey);
		return $url;		
	}
	
	protected static function getStatus(&$xml) {		
		$status = $xml->status;
		switch ($status) {
			case "OK" :  // indicates that no errors occurred; the address was successfully parsed and at least one geocode was returned.
						 $return = 'found';
						 break;
			case "ZERO_RESULTS" : 
						 // indicates that no errors occurred; the address was successfully parsed and at least one geocode was returned.
						 $return = 'notfound';
						 break;
			case "OVER_QUERY_LIMIT" : 
						 // indicates that you are over your quota.
						 $return = 'wait';
						 break;
			case "REQUEST_DENIED" : 
						 // indicates that your request was denied, generally because of lack of a sensor parameter.
			case "INVALID_REQUEST" : 
						 // generally indicates that the query (address or latlng) is missing.
			default    : $return = $status;
						 break;
		}

		return $return;
	}

	protected function getCoordinates(&$xml) {		
		// Format coordinatest: Longitude, Latitude, Altitude
		$coordinates = array();
		$coordinates['lon'] = (float) $xml->result->geometry->location->lng;
		$coordinates['lat'] = (float) $xml->result->geometry->location->lat;
		return $coordinates;
	}
	
	protected function getNumberOfHits(&$xml) {	
		// Google returns 1 Status and n results	
		if(phpversion() >= '5.3.0'){ 
			// PHP > 5.3
    		return (int) ($xml->count() - 1);
    	} else { 
    		// PHP < 5.3
		    return (int) (count($xml->children()) - 1);
	    } 		
		
		//return (int) ($xml->count() - 1);
	}
	
	protected function getResultAddress(&$xml) {	
		return (string) $xml->result->formatted_address;
	}
	
	public function setResultSet(&$xml){
		$resultSet = array();
		
		foreach ($xml as $result) {
			if (isset($result->geometry->location->lng)) {
				$object		 = new \stdClass;
				$object->lon = (float)  $result->geometry->location->lng;
				$object->lat = (float)  $result->geometry->location->lat;
				$object->adr = (string) $result->formatted_address;	
				$resultSet[] = $object;
				unset($object);
			}
		}
		
		return $resultSet;
	}
	
	
}
