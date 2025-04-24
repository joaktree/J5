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
function jtsaveaccess(id){
	var form = document.adminForm;
    var cb = form[id];
    form.task.value = 'save';
	if (cb) {
		for (var i = 0; true; i++) {
			var cbx = form['cb'+i];
			if (!cbx)
				break;
			cbx.checked = false;
		} // for
		cb.checked = true;
		form.boxchecked.value = 1;
		form.submit();
	}

}

function changeAccessLevel(id) {
    var form = document.adminForm;
    var cb = form[id];
    if (cb) {
        cb.checked = true;
        form.boxchecked.value = 1;
    }
    return false;
}

//jt_tree + jt_map
function jSelectPerson(id, title, appid, apptitle, treeid) {
	var El1   = document.getElementById('jform_personId');
	var El2   = document.getElementById('jform_root_person_id');
	var El3   = document.getElementById('jform_personName');
	var El4   = document.getElementById('jform_app_id');
	var El5   = document.getElementById('jform_appTitle');
	var El6   = document.getElementById('jform_params_treeId');
	var El7   = document.getElementById('jform_tree');
	
	if (El1 != null) { El1.value = appid + '!' + id; }
	if (El2 != null) { El2.value = id; }
	if (El3 != null) { El3.value = title; }
	if (El4 != null) { El4.value = appid; }
	if (El5 != null) { El5.value = apptitle; }
	if (El6 != null) { El6.value = treeid; }
	if (El7 != null) { El7.value = treeid; }
    if (treeid && El5) {
        options = El5.options;
        for (var opt, j = 0; opt = options[j]; j++) {
            if (opt.value == treeid) {
            //Select the option and break out of the for loop.
                options.selectedIndex = j;
                break;
            }
        }
    }
	document.querySelector('#selectperson').close();
    document.querySelector('#selecttree').close();
    
    // this.close();
}

//jt_tree
function jClearPerson() {
	var El1   = document.getElementById('jform_personId');
	var El2   = document.getElementById('jform_root_person_id');
	var El3   = document.getElementById('jform_personName');
	
	if (El1 != null) {
		El1.value = null;
	}
	
	if (El2 != null) {
		El2.value = null;
	}

	if (El3 != null) {
		El3.value = null;
	}
}

// jt_map
function toggleMapSetting() {
	var El1   = document.getElementById('jform_selection');
	var El2   = document.getElementById('tree');
	var El3   = document.getElementById('person1');
	var El4   = document.getElementById('person2');
	var El5   = document.getElementById('familyName');
	var El6   = document.getElementById('descendants');
	var El7   = document.getElementById('relations');
	var El8   = document.getElementById('distance');
	
	if ((El1.value == 'tree') || (El1.value == 'location')) {
		if (El2.classList.contains('jt-hide')) {
			El2.classList.remove('jt-hide');
			El2.classList.add('jt-show');
		}
		
		if (El3.classList.contains('jt-show')) {
			El3.classList.remove('jt-show');
			El3.classList.add('jt-hide');
		}
		
		//if (El4.classList.contains('jt-show')) {
		//	El4.classList.remove('jt-show');
		//	El4.classList.add('jt-hide');
		//}
		
		if (El5.classList.contains('jt-hide')) {
			El5.classList.remove('jt-hide');
			El5.classList.add('jt-show');
		}

		if (El6.classList.contains('jt-hide')) {
			El6.classList.remove('jt-hide');
			El6.classList.add('jt-show');
		}
		
		if (El7.classList.contains('jt-show')) {
			El7.classList.remove('jt-show');
			El7.classList.add('jt-hide');
		}
	}

	if (El1.value == 'tree') {
		if (El8.classList.contains('jt-show')) {
			El8.classList.remove('jt-show');
			El8.classList.add('jt-hide');
		}
	}
	if (El1.value == 'location') {
		if (El8.classList.contains('jt-hide')) {
			El8.classList.remove('jt-hide');
			El8.classList.add('jt-show');
		}
	}
	
	if (El1.value == 'person') {
		if (El2.classList.contains('jt-show')) {
			El2.classList.remove('jt-show');
			El2.classList.add('jt-hide');
		}
		
		if (El3.classList.contains('jt-hide')) {
			El3.classList.remove('jt-hide');
			El3.classList.add('jt-show');
		}
		
		//if (El4.classList.contains('jt-hide')) {
		//	El4.classList.remove('jt-hide');
		//	El4.classList.add('jt-show');
		//}
		
		if (El5.classList.contains('jt-show')) {
			El5.classList.remove('jt-show');
			El5.classList.add('jt-hide');
		}
		
		if (El6.classList.contains('jt-hide')) {
			El6.classList.remove('jt-hide');
			El6.classList.add('jt-show');
		}

		if (El7.classList.contains('jt-hide')) {
			El7.classList.remove('jt-hide');
			El7.classList.add('jt-show');
		}
		
		if (El8.classList.contains('jt-show')) {
			El8.classList.remove('jt-show');
			El8.classList.add('jt-hide');
		}
	}
}

function old_importGedcom() {
	
	var myRequest = new Request({
	    url: 'index.php?option=com_joaktree&view=importgedcom&format=raw&tmpl=component',
	    method: 'get',
		onFailure: function(xhr) {
			alert('Error occured for url: ' + url);
		},
		onComplete: function(response) {
	    	HandleResponseGedcom('import', response);	    		
		}
	}).send();
}
function old_exportGedcom() {
				//console.log('OK');
	var myRequest = new Request({
	    url: 'index.php?option=com_joaktree&view=exportgedcom&format=raw&tmpl=component',
	    method: 'get',
		onFailure: function(xhr) {
			alert('Error occured for url: ' + url);
		},
		onComplete: function(response) {
			HandleResponseGedcom('export', response);	    		
		}
	}).send();
}
function importGedcom() {
    var url = 'index.php?option=com_joaktree&view=importgedcom&format=raw&tmpl=component';

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(responseText => {
            HandleResponseGedcom('import', responseText);
        })
        .catch(error => {
            console.log('Error occurred for url: ' + url);
        });
}


function exportGedcom() {
    
    var url = 'index.php?option=com_joaktree&view=exportgedcom&format=raw&tmpl=component';
			//console.log('url : ' + url);
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(responseText => {
			HandleResponseGedcom('export', responseText);
        })
        .catch(error => {
            //alert('Error occurred for url: ' + url);
			console.log('Error occured for url: ' + url);
        });
}

function delExportFile(id) {
	var url = 'index.php?option=com_joaktree&view=exportgedcom&format=raw&tmpl=component&task=del&id='+id;
    fetch(url)
		.then(response => {
           	if (!response.ok) {
               	throw new Error('Network response was not ok');
           	}
			console.log('Response: ' + response.ok);
                return response.text();
        	})
        .	then(responseText => {
           		console.log('Response: ' + responseText);
				btn = document.getElementById("btndelexport");
				btn.style.display = "none";
				btn = document.getElementById("btngetexport");
				btn.style.display = "none";
                curmsg = document.getElementById('procmsg').innerHTML;
                document.getElementById('procmsg').innerHTML = curmsg + '<br />' + responseText;
	        })
       		.catch(error => {
           		console.log('Error occurred for url');
      		});
}


function HandleResponseGedcom(type, response) {
	var curmsg = document.getElementById('procmsg').innerHTML;
	//console.log(response);
	try { var r = JSON.parse(response); }
	catch(err) { 		
		document.getElementById('procmsg').innerHTML = curmsg + '<br />' + response;
		//alert('An error occured while processing GedCom.');
		console.log('An error occured while processing GedCom. ' + curmsg);
	}	
	if ((r) && (r.status)) {
		//console.log('statut : ' + r.status);
		if (r.msg != null) {
			document.getElementById('procmsg').innerHTML = curmsg + '<br />' + r.msg;
		}
		
		if (r.status == 'stop') {
			console.log('stop');
			document.getElementById('head_process').style.display  = 'none';
            document.getElementById('head_finished').style.display = 'block';
			// document.getElementById('end_' + r.id).value = r.end;
			// document.getElementById('procmsg').innerHTML = r.msg;
            if (document.getElementById('iframeModalWindow')) {
                document.getElementById('iframeModalWindow').src = "index.php?option=com_joaktree&view=viewlogs&tmpl=component&appId="+r.id;
            }
            if (document.getElementById('btnviewlog')) { // import GED : view log button
                document.getElementById('btnviewlog').style.display = 'block';
            }
            if (document.getElementById('btnviewlog')) { // import GED : view log button
                document.getElementById('btnviewlog').style.display = 'block';
            } 
            if (document.getElementById('btngetexport')) { // export GED : copy file from server
                document.getElementById('btngetexport').style.display = 'block';
            } 
            if (document.getElementById('btndelexport')) { // export GED : delete file from server
                document.getElementById('btndelexport').style.display = 'block';
            } 
			return true;
		}

		if (r.status == 'error') {
			document.getElementById('head_process').style.display  = 'none';
			document.getElementById('head_error').style.display    = 'block';
            if (r.msg.indexOf('jt_export_present')) { // export error : file exists
                btn = document.getElementById("btndelexport");
				btn.style.display = "block";
            }
		}

		if (r.status != 'stop') {
				//console.log(r.status);
			if (r.start) {
				document.getElementById('start_' + r.id).value = r.start;
			}
			if (r.current) {
				document.getElementById('current_' + r.id).value = r.current;
			}
			
			if (r.persons > 0) {
				document.getElementById('l_persons_' + r.id).style.display = 'flex';
				document.getElementById('persons_' + r.id).value = r.persons;
			}
			
			if (r.families > 0) {
				document.getElementById('l_families_' + r.id).style.display = 'flex';
				document.getElementById('families_' + r.id).value = r.families;
			}

			if (r.sources > 0) {
				document.getElementById('l_sources_' + r.id).style.display = 'flex';
				document.getElementById('sources_' + r.id).value = r.sources;
			}

			if (r.repos > 0) {
				document.getElementById('l_repos_' + r.id).style.display = 'flex';
				document.getElementById('repos_' + r.id).value = r.repos;
			}

			if (r.notes > 0) {
				document.getElementById('l_notes_' + r.id).style.display = 'flex';
				document.getElementById('notes_' + r.id).value = r.notes;
			}

			if (r.unknown > 0) {
				document.getElementById('l_unknown_' + r.id).style.display = 'flex';
				document.getElementById('unknown_' + r.id).value = r.unknown;
			}
			
			if (r.end) {
				console.log('end');
				document.getElementById('end_' + r.id).value = r.end;
			}
		}
		
		if ((r.status != 'stop') && (r.status != 'error')) {
			if (type == 'import') {
				importGedcom();
			}
			if (type == 'export') {
				//console.log('export');
				exportGedcom();
			}
		}		
	}
}

function old_assignft(url1) {
	
	if (!url1) { url1 = 'index.php?option=com_joaktree&view=trees&format=raw&tmpl=component&init=0'; }
	
	var myRequest = new Request({
	    url: url1,
	    method: 'get',
		onFailure: function(xhr) {
			alert('Error occured for url: ' + url);
		},
		onComplete: function(response) {
	    		HandleResponseAssignft(response);	    		
		}
	}).send();
}
function assignft(url1) {
	
	if (!url1) { url1 = 'index.php?option=com_joaktree&view=trees&format=raw&tmpl=component&init=0'; }
    fetch(url1)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(responseText => {
            HandleResponseAssignft(responseText);
        })
        .catch(error => {
            console.log('Error occurred for url: ' + url1);
        });
}
function HandleResponseAssignft(response) {
	var curmsg = document.getElementById('procmsg').innerHTML;
	
	try { var r = JSON.parse(response); }
	catch(err) { 		
		document.getElementById('procmsg').innerHTML = curmsg + '<br />' + response;
		alert('An error occured while assigning family trees to persons.');
	}	
	if ((r) && (r.status)) {
		if (r.msg != null) {
			document.getElementById('procmsg').innerHTML = curmsg + '<br />' + r.msg;
		}
		if (r.start)   { document.getElementById('start').value = r.start; }
		if (r.current) { document.getElementById('current').value = r.current; }
		if (r.end)     { document.getElementById('end').value = r.end; }
		if ((r.status != 'end') && (r.status != 'error')) { assignft(); }	
		if (r.status == 'end') { 
            document.getElementById('butprocmsg').style.display='block'; 
        }
	}
}
function checkenter(event) {
    if (event.key == "Enter") {
      	var form = document.adminForm;
        form.submit()
    }
}

