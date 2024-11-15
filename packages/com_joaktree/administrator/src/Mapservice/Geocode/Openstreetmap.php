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
class Openstreetmap extends MBJGeocode {

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
			$params['indHttps']['value'] = 'false';
			$params['format']['type']    = 'string';
			$params['format']['value']   = 'xml';
			$params['email']['type']     = 'string';
			$params['email']['value']    = null;
//			$params['limit']['type']     = 'number';
//			$params['limit']['value']    = '1';
			//$params['polygon']['type']   = 'number';
			//$params['polygon']['value']  = '0';
			//$params['addressdetails']['type']   = 'number';
			//$params['addressdetails']['value']  = '0';
			
		}
		
		return $params;
	}
	
	protected function getBaseUrl() {
		static $baseUrl;
		
		if (!isset($baseUrl)) {
			$keys   = $this->getKeys();
			$params = self::parameters(); 	
			$base_url = '';
			
			$base_url .= 'http://';
			$base_url .= 'nominatim.openstreetmap.org/search';

			$format    = (isset($keys->format)) ? $keys->format : $params['format']['value'];
			$base_url .= '?format='.$format;
			
			//$polygon   = (isset($keys->polygon)) ? $keys->polygon : $params['polygon']['value'];
			//$base_url .= ($polygon) ? '&polygon='.$polygon : '';
			$base_url .= '&polygon=0';
			
			//$addressdetails = (isset($keys->addressdetails)) ? $keys->addressdetails : $params['addressdetails']['value'];
			//$base_url .= ($addressdetails) ? '&addressdetails='.$addressdetails : '';
			$base_url .= '&addressdetails=0';
			
			$email     = (isset($keys->email)) ? $keys->email : $params['email']['value'];
			$base_url .= ($email) ? '&email='.$email : '';
			
//			$limit     = (isset($keys->limit)) ? $keys->limit : $params['limit']['value'];
//			$base_url .= ($limit) ? '&limit='.$limit : '';
		}
		return $base_url;
		
	}
	
	protected function getUrl(&$data) {
		$url  = $this->getBaseUrl();
		$url .= '&q='.urlencode($data->value);
		
		return $url;		
	}
	
	protected function getStatus(&$xml) {		
		$return = ($xml->place['lat']) ? 'found' : 'notfound';										
		return $return;
	}

	protected function getCoordinates(&$xml) {		
		// Format coordinatest: Longitude, Latitude, Altitude
		$coordinates = array();
		$coordinates['lon'] = (float) $xml->place['lon'];
		$coordinates['lat'] = (float) $xml->place['lat'];
		return $coordinates;
	}
	
	protected function getNumberOfHits(&$xml) {	
		// Just returns the number of results	
		
		if(phpversion() >= '5.3.0'){ 
			// PHP > 5.3
    		return (int) $xml->count();
    	} else { 
    		// PHP < 5.3
		    return count($xml->children());
	    } 
	    		
		// return (int) $xml->count();
	}
	
	protected function getResultAddress(&$xml) {	
		return (string) $xml->place['display_name'];
	}
	
	public function setResultSet(&$xml){
		$resultSet  = array();
		
		foreach ($xml as $result) {
			if ($result['lon']) {
				$object		 = new \stdClass;
				$object->lon = (float)  $result['lon'];
				$object->lat = (float)  $result['lat'];
				$object->adr = (string) $result['display_name'];	
				$resultSet[] = $object;
				unset($object);
			}
		}
		
		return $resultSet;
	}
	
}
