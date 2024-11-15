<?php
/**
 * @package     MapsByJoaktree
 * @subpackage  Service
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace Joaktree\Component\Joaktree\Administrator\Mapservice\Interactivemap;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\Uri\Uri;		//replace JUri
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJInteractivemap;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;
/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class Google extends MBJInteractivemap {

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
			$params['language']['type']  = 'string';
			$params['language']['value'] = null;
		}
		
		return $params;
	}
	
	public function fetch($data, $options = array()) {
	//public function fetch() {
		$script = array();
		$indCenter = false;

		// center
		if (  (isset($options['longitude'])) && (!empty($options['longitude'])) 
		   && (isset($options['latitude']))  && (!empty($options['latitude']))
		   ) {
			$center = 'center: new google.maps.LatLng('.$options['latitude'].','.$options['longitude'].')';
			$indCenter = true;
		} else if ((isset($options['center'])) && (!empty($options['center']))) {
			$centerdata = new \stdClass;
			$centerdata->value = $options['center'];

// 			JLoader::register('MBJServiceGeocode',    JPATH_ADMINISTRATOR.'/components/com_joaktree/services/geocode.php');
			$geocode = MBJGeocode::getInstance();
			$geocode->_('findLocation', $centerdata);
			
			$center = 'center: new google.maps.LatLng('.$centerdata->latitude.','.$centerdata->longitude.')';
			$indCenter = true;
		} else if (count($data)) {
			// take the first marker as center
			$center = 'center: new google.maps.LatLng('.$data[0]->latitude.','.$data[0]->longitude.')';
			$indCenter = true;
		}
		
		if (!$indCenter) {
			$this->log[] = Text::_('MBJ_MAP_ERROR_NO_CENTERZOOM');
			return false;
		}
		
		// zoom
		if (  (isset($options['zoomlevel'])) && (!empty($options['zoomlevel'])) ) {
			$zoom = 'zoom: '.$options['zoomlevel'];
		} else {
			$zoom = 'zoom: 9';
		}		
		
		// maptype
		$maptype = 'mapTypeId: google.maps.MapTypeId.';
		if ((isset($options['maptype'])) && (!empty($options['maptype']))) {
			switch ($options['maptype']) {
				case "sat": 	$maptype .= 'SATELLITE';
								break;
				case "hyb"	: 	$maptype .= 'HYBRID';
								break;
				case "ter"	: 	$maptype .= 'TERRAIN';
								break;
				case "road"	:	// continue									
				default 	: 	// maptype is default roadmap
								$maptype .= 'ROADMAP';
				          		break;
			}	
		} else {
			$maptype .= 'ROADMAP';
		}
		
		// generate script
		$script[] = 'function init_dyn_map() { ';
		
		$script[] = '   var Elb = document.getElementById("map_canvas").parentNode; ';
		$script[] = '   Elb.style.margin = "0"; ';
		$script[] = '   Elb.style.height = "100%"; ';
		
		$script[] = '   var myOptions = { ';
		$script[] = '      '.$zoom.', ';
		$script[] = '      '.$center.', ';
		$script[] = '      '.$maptype.' ';
		$script[] = '   } ';
		$script[] = '   var myMap = new google.maps.Map(document.getElementById("map_canvas"), myOptions); ';
		
		if (  ($options['selection'] == 'location')
		   && isset($options['distance']) 
		   && !empty($options['distance'])
		   && ($options['distance'] > 0)
		   ) {
		   	// we are adding a circle
			$script[] = '   var myCircle = new google.maps.Circle({ ';
			$script[] = '     map: myMap, ';
			$script[] = '     center: new google.maps.LatLng('.$options['latitude'].','.$options['longitude'].'), '; 
			$script[] = '     radius: '.($options['distance'] * 1000).', ';
			$script[] = '     strokeColor: \'#2D36AD\', ';
			$script[] = '     strokeOpacity: 0.7, ';
			$script[] = '     strokeWeight: 2, ';
			$script[] = '     fillColor: \'#2D36AD\', ';
			$script[] = '     fillOpacity: 0.2 ';
			$script[] = '  }); ';		
		}
		
		if ((isset($options['icon_file'])) && (!empty($options['icon_file'])) ) {
			$iconFile = Uri::base().$options['icon_file'];
		} else {
			$iconFile = Uri::base().'media/com_joaktree/images/jt_map_sprite.png';
		}
				
		// setup possible images
		$iconset = (isset($options['color']) && !empty($options['color'])) ? (int) $options['color'] : 0 ; 
		
		$theme = $iconset * 34;
		for ($i=0; $i<21; $i++) {
			$pos = $i * 26;
			$script[] = '   var img'.($i+1).' = new google.maps.MarkerImage("'.$iconFile.'", '
					   .'      new google.maps.Size(24, 32), '         		// This marker is 24 pixels wide by 32 pixels tall.
					   .'      new google.maps.Point('.$pos.', '.$theme.')); '; // The origin in the sprite for this image is 0,0.
		}
		
		if (count($data)) {
			foreach ($data as $item) {
				$script[] = '   var cnt'.$item->id.' = \'<div id="cnt'.$item->id.'">'.$item->information.'</div>\'; ';
				$script[] = '   var inf'.$item->id.' = new google.maps.InfoWindow({ ';
				$script[] = '      content: cnt'.$item->id.' ';
				$script[] = '   }); ';
								
				$script[] = '   var mrk'.$item->id.' = new google.maps.Marker({ ';
				$script[] = '      position: new google.maps.LatLng('.$item->latitude.','.$item->longitude.'), ';
				$script[] = '      map: myMap, ';
				$script[] = '      icon: img'.(((int)$item->label < 21) ? (int)$item->label : 21).', '; 
				$script[] = '      title: "'.$item->value.'" ';
				$script[] = '   }); ';
				
				$script[] = '  google.maps.event.addListener(mrk'.$item->id.', \'click\', function() { ';
				$script[] = '      inf'.$item->id.'.open(myMap, mrk'.$item->id.'); ';
				$script[] = '  }); ';
			} 		
		}
				
		$script[] = '} ';
		$script[] = '';
		
		$script[] = 'function loadScript() { ';
		$script[] = '   var script  = document.createElement("script"); ';
		$script[] = '   script.type = "text/javascript"; ';
		$script[] = '   script.src  = "'.self::getScriptUrl().'&callback=init_dyn_map"; ';
		$script[] = '   document.body.appendChild(script); ';
		$script[] = '} ';
		$script[] = '';
		
		$script[] = 'window.onload = loadScript; ';
		
		return implode("\n", $script);
	}
	
	public function getToolkit() {
		return false;
	}
	
	private function getScriptUrl() {
		static $baseUrl;
		
		if (!isset($baseUrl)) {
			$keys   = self::getKeys();
			$params = self::parameters(); 	
			$base_url = '';
			
			$indHttps  = (isset($keys->indHttps)) ? $keys->indHttps : $params['indHttps']['value'];
			$base_url .= (($indHttps) ? 'https' : 'http').'://';
			
			$base_url  .= 'maps.googleapis.com/maps/api/js';
			//$base_url  = 'http://maps.googleapis.com/maps/api/js';
			$base_url .= '?key='.$this->provider->getAPIkey();
			$base_url .= '&sensor=false'; 
			$base_url .= (isset($keys->language)) ? '&language='.$keys->language : '';
			
		}
		return $base_url;
	}
		
	
}
