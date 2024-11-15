/**
 * @package     MapsByJoaktree
 * @subpackage  jtmaps
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 */
function showui() { 
	var El1 = document.getElementById('jtmap-uidisplay');
	El1.setStyle('display', 'block');
	var El2 = document.getElementById('jtmap-but-showui');
	El2.setStyle('display', 'none');
}

function hideui() {
	var El1 = document.getElementById('jtmap-uidisplay');
	El1.setStyle('display', 'none');
	var El2 = document.getElementById('jtmap-but-showui');
	El2.setStyle('display', 'block');
}
function jtSelectPerson(app_id, id, name) {
	SqueezeBox.close();
	var El1 = document.getElementById('jtmap-person');
	El1.value = name;
	var El2 = document.getElementById('jtmap-person_id');
	El2.value = app_id + '!' + id;	
}
function updateUrl(url) {
	var El1 = document.getElementById('jtmap-person_id');
	if (El1 != null) { url =  url + '|' + El1.value; } else { url =  url + '|'; }
	var El2 = document.getElementById('jtmap-relations');
	if (El2 != null) { url =  url + '|' + El2.value; } else { url =  url + '|'; }
	var El3 = document.getElementById('jtmap-familyname');
	if (El3 != null) { url =  url + '|' + El3.value; } else { url =  url + '|'; }
	var El4 = document.getElementById('jtmap-perstart');
	if (El4 != null) { url =  url + '|' + El4.value; } else { url =  url + '|'; }
	var El5 = document.getElementById('jtmap-perend');
	if (El5 != null) { url =  url + '|' + El5.value; } else { url =  url + '|'; }
	var El6 = document.getElementById('jtmap-events');
	if (El6 != null) {  
		url =  url + '|[';
		for (i=0; i<El6.getSelected().length; i++) {
			url =  url + '"' + El6.getSelected()[i].value + '"';
			if (i != (El6.getSelected().length-1)) { url = url + ','; }
		}
		url =  url + ']' ; 
	} else { 
		url =  url + '|'; 
	}
	var El7 = document.getElementById('jtmap-distance');
	if (El7 != null) { url =  url + '|' + El7.value; } else { url =  url + '|'; }
	return url;
}
function jtRefreshDynMap(url, mapid) {
	url = updateUrl(url);
	var EMap = document.getElementById(mapid);
	EMap.setProperty('src', url);
}
function jtRefreshStatMap(url1, url2, mapid) {
	var EMap = document.getElementById(mapid);
	EMap.setProperty('src', url2);
	
	url1 = updateUrl(url1);
	var myRequest = new Request({
	    url: url1,
	    method: 'get',
		onFailure: function(xhr) {
			alert('Error occured for url: ' + url);
		},
		onComplete: function(response) {
	    		HandleResponseStatmap(mapid, response);	    		
		}
	}).send();
}
function HandleResponseStatmap(mapid, response) {
	var EMap = document.getElementById(mapid);
	EMap.setProperty('src', response);
}


