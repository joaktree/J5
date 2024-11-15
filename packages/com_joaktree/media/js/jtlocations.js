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
var jtl_mode_sef,indCookie,lists,format,btnSourcel;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtloptions = Joomla.getOptions('joaktreelocations');
	if (typeof jtloptions === 'undefined' ) {return false}
    
    jtl_mode_sef = jtloptions.mode_sef;
    indCookie = jtloptions.indCookie;
    lists = jtloptions.lists;
    format = jtloptions.format;

    const btn_alpha = document.querySelectorAll('.jt-content-accordion'); 
    btnSourcel = '0-jt-cnt';
    let jtTab = '0-jt-cnt';
    const index = 0;
    btn_alpha.forEach((item)=>{ 
        item.classList.remove('jt-content-accordion-active'); 
        item.classList.add('jt-content-accordion'); 
        item.addEventListener('click', function(){ 
            btnSourcel = item.getAttribute('btn_a_index'); 
            item.classList.remove('jt-content-accordion');
            item.classList.add('jt-content-accordion-active'); 
            loadData(btnSourcel);
        });  
    });
    
}); 
function loadData(Sourcel) { 
    var selSourcel,selDell;
    if ((typeof Sourcel === 'undefined')|| (typeof Sourcel !== 'string')){
        ;return 0;
    }
    try {
    	selSourcel = Sourcel,
        selDell = false;
    } catch (e) { 
    	; selSourcel = btnSourcel ;
    	selDell = true;
    }; 
    const jtDest = document.querySelectorAll('#jt-accordion div.jt-showr'); 
    jtDest.forEach((elem)=>{ 
        elem.classList.remove('jt-showr');
        elem.classList.add('jt-ajax');
    });
    const jtElement = document.getElementById( selSourcel );
    if ((jtElement) && (jtElement.classList.contains('jt-ajax'))) { 
        jtElement.classList.remove('jt-ajax'); 
        jtElement.classList.add('jt-showr');
        const index = jtElement.id;
        let myRequest = new XMLHttpRequest();
        myRequest.open('GET', 'index.php?option=com_joaktree&format=raw&tmpl=component&view=locations&layout=_places&treeId='+lists['tree_id']+'&filter=' + index.replace('-jt-cnt', ''), true);
        url: 'index.php?option=com_joaktree&format=raw&tmpl=component&view=locations&layout=_places&treeId='+lists['tree_id']+'&filter='+index.replace('-jt-cnt', ''); 
        myRequest.onerror = function() {
            alert('Error occured for url: ' + url); 
        };
        myRequest.onload = function() {
            if (myRequest.status >= 200 && myRequest.status < 400) {
                ;HandleResponseLoc(index, myRequest.responseText);
            } else {
                ;alert('Error occured for url: ' + url);
            }
        }
        myRequest.send();
    }
}
function handleEvent(event) {
    event = new CustomEvent('DOMEvent', {
        bubbles: true,
        cancelable: true,
        detail: {
        	  originalEvent: event,
        	  window: window
        }
    })
    if (i.call(j, event) === false) {
        event.preventDefault();
        event.stopPropagation();
    }
}
function HandleResponseLoc(id, response) {
    const El = document.getElementById(id);
    El.innerHTML = response;
}
function jt_show_list(loc, url) { 
    var MTitl = document.getElementById('jt-map-title'); 
    var MId   = document.getElementById('jt-map-id'); 
    if (indCookie) document.cookie= 'jt_loc_url', loc + '|' + url, {duration: 0}; 
    var html = '<iframe id=\"jt-map-frame\" src=\"' + url + '\" height=\"250px\" style=\"border: 1px solid #dddddd;\" ></iframe>'; 
    MTitl.setAttribute('html', loc); 
    MId.setAttribute('html', html);
} 
function jt_show_map(loc, url) { 
    var MTitl = document.getElementById('jt-map-title'); 
    var MDist = document.getElementById('jt-map-distance'); 
    var MId   = document.getElementById('jt-map-id'); 
    jtTab = document.querySelector('#jt-accordion div.jt-showr').id;
    //if (jtl_mode_sef) {
    //    url = url + '-' + MDist.value; 
    //} else { 
        url = url + '&distance=' + MDist.value; 
    //}
    if (indCookie) document.cookie = ('jt_loc_url' + '=' + loc + '|' + url + ';' );
    var html = '<iframe id="jt-map-frame" src="' + url + '" height="250px" style="border: 1px solid #dddddd;" ></iframe>';
    MTitl.setAttribute('html' , loc); 
    MId.setAttribute('html' , html);
    const jtElementn = document.getElementById( btnSourcel );
    if ((jtElementn) && (jtElementn.classList.contains('jt-ajax'))) { 
        jtElementn.classList.remove('jt-ajax'); 
        jtElementn.classList.add('jt-showr')
    };
    try { 
        if (document.getElementById('jt-map-frame').style.display == 'none') {
            document.getElementById('jt-map-frame').style.display = 'block';
        }
        document.getElementById('jt-map-frame').contentDocument.location.replace(url);
        document.getElementById('jt-map-title').innerHTML = loc;
    } catch (e) { 
        location.reload();
    }
    document.getElementById('jt-map-frame').setAttribute('src',url);
}
function jt_upd_radius() { 

    var Mfr   = document.getElementById('jt-map-frame'); 
    if (Mfr != null) { 
        var loc   = document.querySelector('#jt-map-title').innerHTML; 
        var MDist = document.getElementById('jt-map-distance');
        let urlm   = Mfr.getAttribute('src'); 
        //if (jtl_mode_sef) {
        //    const ind   = urlm.lastIndexOf('-'); 
        //    urlm = urlm.slice(0, ind) + '-' + MDist.value; 
        //} else {
            const ind   = urlm.indexOf('&distance'); 
            urlm = urlm.slice(0, ind) + '&distance=' + MDist.value; 
        //}
        if (indCookie) document.cookie = ('jt_loc_url' + '=' + loc + '|' + urlm + ';' );
        Mfr.setAttribute('src', urlm); 
        if (lists['indFilter']) {
            var indLoc = lists['indFilter'];
        } else {
            var indLoc = '0-jt-cnt';
        }
        jtTab = document.querySelector('#jt-accordion div.jt-showr');
        document.getElementById('jt-map-frame').contentDocument.location.replace(urlm);
        // document.getElementById('jt-map-frame').setAttribute('src',urlm);
    } 
}
window.onload = loadData();