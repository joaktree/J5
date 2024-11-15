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
namespace Joaktree\Component\Joaktree\Administrator\Mapservice\Staticmap;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;		// replace JText

use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJStaticmap;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;
/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class Google extends MBJStaticmap {

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
			$params['sensor']['type']    = 'boolean';
			$params['sensor']['value']   = 'false';
			$params['country']['type']   = 'string';
			$params['country']['value']  = null;
			$params['language']['type']  = 'string';
			$params['language']['value'] = null;
		}
		
		return $params;
	}
	
	protected function getBaseUrl() {
		static $baseUrl;
		
		if (!isset($baseUrl)) {
			$keys   = self::getKeys();
			$params = self::parameters(); 	
			$base_url = '';
			
			$indHttps  = (isset($keys->indHttps)) ? $keys->indHttps : $params['indHttps']['value'];
			$base_url .= (($indHttps) ? 'https' : 'http').'://';
			$base_url .= 'maps.googleapis.com/maps/api/staticmap';
			$base_url .= '?key='.$this->provider->getAPIkey();
			
			$sensor    = (isset($keys->sensor)) ? $keys->sensor : $params['sensor']['value'];
			$base_url .= ($sensor) ? '&sensor='.$sensor : '';

			$country   = (isset($keys->country)) ? $keys->country : $params['country']['value'];
			$base_url .= ($country) ? '&region='.$country : '';
			
			$language  = (isset($keys->language)) ? $keys->language : $params['language']['value'];
			$base_url .= ($language) ? '&language='.$language : '';
		}
		return $base_url;		
	}
	
	public function fetch($data, $options = array()) {
	//public function fetch(&$data, $options = array()) {
	//public function fetch() {
		$indCenter = false;
		$indZoom   = false;
		$mapview   = $this->getBaseUrl();
		
		// size
		$width  = ((isset($options['width']))  && (!empty($options['width']))  ) ? $options['width']  : '650';
		$height = ((isset($options['height'])) && (!empty($options['height'])) ) ? $options['height'] : '450';
		$mapview .= '&size='.$width.'x'.$height;
		
		if (  (isset($options['longitude'])) && (!empty($options['longitude'])) 
		   && (isset($options['latitude']))  && (!empty($options['latitude']))
		   ) {
			$mapview .= '&center='.$options['latitude'].','.$options['longitude'];
			$indCenter = true;
		} else if ((isset($options['center'])) && (!empty($options['center']))) {
			$centerdata = new \stdClass;
			$centerdata->value = $options['center'];

			$geocode = MBJGeocode::getInstance();
			$geocode->_('findLocation', $centerdata);
			
			$mapview .= '&center='.$centerdata->latitude.','.$centerdata->longitude;
			$indCenter = true;
		}
		
		if ((isset($options['zoomlevel'])) && (!empty($options['zoomlevel']))) {
			$mapview .= '&zoom='.$options['zoomlevel'];
			$indZoom = true;
		} else if (count($data) == 1) {
			// just one locations
			$mapview .= '&zoom=10';
			$indZoom = true;
		}

		// maptype
		if ((isset($options['maptype'])) && (!empty($options['maptype']))) {
			switch ($options['maptype']) {
				case "sat": 	$mapview .= '&maptype=satellite';
								break;
				case "hyb"	: 	$mapview .= '&maptype=hybrid';
								break;
				case "ter"	: 	$mapview .= '&maptype=terrain';
								break;
				case "road"	:	// continue									
				default 	: 	// continue
								// maptype is default roadmap
				          		break;
			}	
		} 
		
		// markers
		if (count($data)) {
			$indContinue = true;
			$tmpdata = $data;					
			$item = array_shift($tmpdata);
			$color = (isset($options['color'])) && !empty($options['color']) ? 'color:0x'.$options['color'].'|' : '';		
			
			while ($indContinue) {
				if (is_object($item)) {
					// save the current string in case we go over the max length
					$tmpUrl = $mapview;
					
					$mapview .= '&markers='.$color;
					
					// label
					if (isset($item->label) && !empty($item->label)) {
						$mapview .= ((is_numeric($item->label))  ? 'label:'.(int)$item->label : '');
						$mapview .= ((!is_numeric($item->label)) ? 'label:'.strtoupper(substr($item->label, 0, 1)) : '');
						$mapview .= '|';
					}
					
					$mapview .= $item->latitude.','.$item->longitude;
					
					$indCenter = true;
					$indZoom = true;
					
					$item = array_shift($tmpdata);
					if (!$item) {
						// we are done
						$indContinue = false;
					}
					if (strlen($mapview) > 2000) { // This should be - according to Google - 2048
						// we reached max url length
						$indContinue = false;
						$mapview = $tmpUrl;
					}
					
				} else {
					$indContinue = false;
				}		
			}			
		}
		
		if ((!$indCenter) || (!$indZoom)) {
			$this->log[] = Text::_('MBJ_MAP_ERROR_NO_CENTERZOOM');
			return false;
		}
						
		return $mapview;
	}
	
}
