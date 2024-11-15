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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\Uri\Uri;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJInteractivemap;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJGeocode;

/**
 * Service connector class.
 *
 * @package     MapsByJoaktree
 * @subpackage  Service
 * @since       1.0
 */
class Openstreetmap extends MBJInteractivemap
{
    protected static $version = '1.9.4';

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

    public static function parameters()
    {
        static $params;

        if (!isset($params)) {
            $params = array();

            $params['indHttps']['type']  = 'boolean';
            $params['indHttps']['value'] = 'false';
            $params['imagetype']['type']  = 'string';
            $params['imagetype']['value'] = 'png';
        }

        return $params;
    }

    protected function getBaseUrl()
    {
        static $baseUrl;

        if (!isset($baseUrl)) {
            $keys   = self::getKeys();
            $params = self::parameters();
            $base_url = '';

            $indHttps  = (isset($keys->indHttps)) ? $keys->indHttps : $params['indHttps']['value'];
            $base_url .= (($indHttps) ? 'https' : 'http').'://';
            //$base_url .= 'http://';
            $base_url .= 'open.mapquestapi.com/staticmap/'.self::$version.'/getmap';

            $base_url .= '?imagetype='.$params['imagetype']['value'];

        }
        return $base_url;
    }

    public function fetch($data, $options = array())
    {
        //public function fetch() {
        $base	= 'media/com_joaktree/';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('leaflet', 'https://unpkg.com/leaflet@'.self::$version.'/dist/leaflet.css');
        $wa->registerAndUseScript('leaflet', 'https://unpkg.com/leaflet@'.self::$version.'/dist/leaflet.js');
        $wa->registerAndUseScript('markers', $base.'js/leaflet.extra-markers.min.js');
        $wa->registerAndUseStyle('osmcss', $base.'css/leaflet.extra-markers.min.css');
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->getDocument()->addScript(''.URI::root().'media/com_joaktree/js/jtopenstreetmap.js');
        } else {
            $wa->registerAndUseScript('jtopenstreetmap', $base.'js/jtopenstreetmap.js');
        }

        $jtosm  = '<div class="jtosm">';
        $minlength = '200';
        $width  = ((isset($options['width']))  && (!empty($options['width']))) ? $options['width'] : '650';
        $height = ((isset($options['height'])) && (!empty($options['height']))) ? $options['height'] : '450';
        $mapzoom = 9;
        if ((isset($options['zoomlevel'])) && (!empty($options['zoomlevel']))) {
            $mapzoom = $options['zoomlevel'];
        }

        $showiti = $showpopup = false;
        $jtosm .= '<div class="jt_osm_map" style="width:'.$width.'px;height:'.$height.'px"></div>';
        $jtosm .= '</div>';
        $longitude = $latitude = "";
        $markers = [];
        if ((isset($options['longitude'])) && (!empty($options['longitude']))
           && (isset($options['latitude']))  && (!empty($options['latitude']))) {
            $longitude  = $options['longitude'];
            $latitude   =  $options['latitude'];
            $indCenter = true;
        } elseif ((isset($options['center'])) && (!empty($options['center']))) {
            $centerdata = new \stdClass();
            $centerdata->value = $options['center'];

            $geocode = MBJGeocode::getInstance();
            $geocode->_('findLocation', $centerdata);
            $longitude  = $centerdata->longitude;
            $latitude   = $centerdata->latitude;
            $indCenter = true;
        } elseif (count($data)) {
            // take the first marker as center
            $longitude  = $data[0]->longitude;
            $latitude   = $data[0]->latitude;
            $indCenter = true;
        }

        if (count($data)) {
            $indContinue = true;
            $tmpdata = $data;
            $item = array_shift($tmpdata);
            //             $mapview .= '&pois=';
            $color = (isset($options['color'])) && !empty($options['color']) ? $options['color'] : 'orange';

            $markers = [];
            while ($indContinue) {
                if (is_object($item)) {
                    // save the current string in case we go over the max length
                    $marker =  [];
                    // color + label
                    $marker['color'] = $color;
                    $marker['longitude'] = $item->longitude;
                    $marker ['latitude'] = $item->latitude;
                    // label
                    if (isset($item->label) && !empty($item->label)) {
                        $marker['label']    = ((is_numeric($item->label)) ? (int)$item->label : '');
                        $marker['text']     = ((($item->information)) ? $item->information : '');
                    }
                    $markers[] = $marker;
                    $item = array_shift($tmpdata);
                    if (!$item) {
                        // we are done
                        $indContinue = false;
                    }
                } else {
                    $indContinue = false;
                }
            }
        }
        $radius = 0;
		if (  ($options['selection'] == 'location')
		   && isset($options['distance']) 
		   && !empty($options['distance'])
		   && ($options['distance'] > 0)
		   ) {
               $radius = $options['distance'] * 1000;
           }        
        Factory::getApplication()->getDocument()->addScriptOptions(
            'joaktree',
            array('width' => $width,'height' => $height,'minlength' => $minlength,
            'longitude' => $longitude, 'latitude' => $latitude,
            'mapzoom' => $mapzoom,'showpopup' => $showpopup, 'showiti' => $showiti,
            'markers' => $markers,'radius' =>$radius
            )
        );

        return $jtosm;
    }

}
