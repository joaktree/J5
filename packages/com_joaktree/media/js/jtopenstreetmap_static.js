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
 * use https://leafletjs.com/ 
 *
 * leaflet map  to image : from https://dev.to/gabiaxel/exporting-leaflet-map-to-image-in-the-browser-16am
 * using https://github.com/tsayen/dom-to-image
 *
 */
var jtmap=[],jtmarker=[],isset=[],amap=[],jtoptions,mapix,
	jtlong=[],jtlat=[],jtaddress=[],jtzoom=[],jtpopup=[],jtiti=[];

document.addEventListener('DOMContentLoaded', function() {
	if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
		console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
		return false;
	}
	jtoptions = Joomla.getOptions('joaktree');
	if (typeof jtoptions === 'undefined' ) {return false}

    mapix = 1;
    document.querySelectorAll('.jtosm').forEach( function (afield) {
         createMapImage(afield);
        mapix +=1;
     });
});

createMapImage = async(afield) => {
        afield.setAttribute('map_id',mapix);
        afield.setAttribute('id','jtmap_'+mapix);
        amap[mapix] = afield.querySelector(".jt_osm_map");
        
        // init fields
        let width = jtoptions.width;
        let height  = jtoptions.height;
        amap[mapix].style.height = height+'px';
        amap[mapix].style.width = width+'px';
        jtlong[mapix] = jtoptions.longitude;
        jtlat[mapix] = jtoptions.latitude;
        isset[mapix] = true;
        jtzoom[mapix] = jtoptions.mapzoom;
        jtpopup[mapix] = jtoptions.showpopup;
        jtiti[mapix] = jtoptions.showiti;
        // create map
        jtmap[mapix] = (L.map(amap[mapix],{attributionControl: false,
                                    zoomControl: false,
                                    fadeAnimation: false,
                                    zoomAnimation: false
                        }).setView([jtlat[mapix], jtlong[mapix]], jtzoom[mapix]));
        
        jtmap[mapix].scrollWheelZoom.disable();

        let tileLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(jtmap[mapix]);    

        markers = jtoptions.markers;
        markers.forEach(marker => {
            let myMarker = L.IconColor.icon({
                    icon: '\uf05a',
                    markerColor: marker.color,
                    // shape: 'square',
                    // prefix: 'fa',
                    number: marker.label
            });                
            let jtmarker = L.marker([marker.latitude, marker.longitude],{icon: myMarker}).addTo(jtmap[mapix]);
            // unused in static mode 
            //if (marker.text) {
            //    createPopup(mapix,jtmarker, jtlat[mapix], jtlong[mapix],marker.text);
            //}

        })
       
        await new Promise(resolve => tileLayer.on("load", () => resolve()));
        const dataURL = await domtoimage.toPng(afield, { width, height });
        
        try{
            aix = afield.getAttribute('map_id');
            afield.removeChild(amap[aix]);
            const imgElement = document.createElement("img");
            imgElement.src = dataURL;
            afield.appendChild(imgElement);
        } catch($e) {}
}
