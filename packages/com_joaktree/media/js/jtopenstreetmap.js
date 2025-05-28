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
 * use https://leafletjs.com/ and https://github.com/coryasilva/Leaflet.ExtraMarkers
 */
var jtmap=[],jtmarker=[],isset=[],markers = [],jtoptions,
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
        afield.setAttribute('map_id',mapix);
        afield.setAttribute('id','jtmap_'+mapix);
        amap = afield.querySelector(".jt_osm_map");
        // init fields
        cont = document.querySelector('.contentpane.component .jt_osm_map');
        if (cont) {
           cont.style.width  = '100vw';
           cont.style.height = '100vh';
           document.querySelector('.contentpane.component').style.padding = 0;
        }
        let width   = jtoptions.width;
        let height  = jtoptions.height;
        jtlong[mapix] = jtoptions.longitude;
        jtlat[mapix] = jtoptions.latitude;
        isset[mapix] = true;
        jtzoom[mapix] = jtoptions.mapzoom;
        jtpopup[mapix] = jtoptions.showpopup;
        jtiti[mapix] = jtoptions.showiti;
        
        // create map
        jtmap[mapix] = (L.map(amap).setView([jtlat[mapix], jtlong[mapix]], jtzoom[mapix]));

        jtmap[mapix].scrollWheelZoom.disable();
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(jtmap[mapix]);    

        if (jtoptions.radius > 0) {
            let circle = L.circle([jtlat[mapix], jtlong[mapix]], {
                    color: '#2D36AD',
                    opacity: 0.5,
                    fillColor: '#2D36AD',
                    fillOpacity: 0.2,
                    radius: jtoptions.radius
                }).addTo(jtmap[mapix]);
        }
        
        markers = jtoptions.markers;
        markers.forEach(marker => {
            let myMarker = L.ExtraMarkers.icon({
                    icon: 'fa-number',
                    markerColor: marker.color,
                    // shape: 'square',
                    // prefix: 'fa',
                    number: marker.label
            });                
            let jtmarker = L.marker([marker.latitude, marker.longitude],{icon: myMarker}).addTo(jtmap[mapix]);
            popup = null;
            if (marker.text) {
                popup = createPopup(mapix,jtmarker, jtlat[mapix], jtlong[mapix],marker.text);
            }
            if (popup) {
                popup.on({
                    popupclose: (e) => {
                       //  e.sourceTarget._map.fitBounds(e.sourceTarget._map.getCenter());
                        e.sourceTarget._map.panTo(new L.LatLng(e.sourceTarget._map._lastCenter.lat, e.sourceTarget._map._lastCenter.lng));
                    }
                })                  
            }
        })
        mapix +=1;
    });
});

function createPopup(mapid,jtmarker, alat,along,atext) {
    max = jtmap[mapid].getSize().x - 20;
    popuptext = atext;   /// jtaddress[mapid].value;
    if (jtiti[mapid] == 'true') { // affiche un lien Venir ici
        popuptext += '<br><a href="https://www.openstreetmap.org/directions?route=%3B'+alat+'%2C'+along+'#map=14/'+alat+'/'+along+'" target="_blank" rel="noopener">Venir ici</a>'; 
    }
    return jtmarker.bindPopup(popuptext,{maxWidth: max,keepInView:true});
}
