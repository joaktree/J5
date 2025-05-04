///////////////////////////////////////////////////////////////// 
// the DOLLAR-jt function for creating an object out of ID or Object
function $jt() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
	}
	return elements;
}

////////////////////////
//	This function checks if a class is in a specified element
//
function isClassInElement(oElement, sClassName) {
	if(oElement == null) return false;
    if(sClassName=="*") return true;
	return new RegExp('\\b'+sClassName+'\\b').test(oElement.className)
}

////////////////////////
// adds a class on an element
//
function addClassToElement(oElement, sClassName) {
	if (!isClassInElement(oElement, sClassName)) {
		oElement.className += oElement.className ? (' ' + sClassName) : sClassName;
	}
}

////////////////////////
// removes a class from an element
//
function removeClassFromElement(oElement, sClassName) {
	if( isClassInElement(oElement, sClassName)){
		var oPattern = oElement.className.match(' '+sClassName) ? (' ' + sClassName) : sClassName;
		oElement.className = oElement.className.replace(oPattern,'');
	}
}

////////////////////////
// swap two classes on an element
//
function swapClassInElement(oElement,sClassName,sClassName2){
	if(oElement != null){
		if( isClassInElement(oElement, sClassName)){
			removeClassFromElement(oElement, sClassName);
			addClassToElement(oElement, sClassName2);
		}
		else{
			removeClassFromElement(oElement, sClassName2);
			addClassToElement(oElement, sClassName);
		}
	}
}

////////////////////////
//toggle visibility for module today many years ago
//
function jttmya_toggle(id) {
   var oEl = document.getElementById(id);
   if(isClassInElement(oEl, 'jt-hide') | isClassInElement(oEl, 'jt-show'))
      swapClassInElement(oEl,'jt-hide','jt-show');
   return false;
}

////////////////////////
// toggle visibility of an array of elements depending on a classname or style
//
function oldjtedit(value, cl1, cl2) {
	var oEl, i, elements;
	elements = document.getElementById('jt-content').getElements(value); 
	for (i=0; i < elements.length; i++ ) {
		if($jt(elements[i])){
			oEl = $jt(elements[i]);
			if(isClassInElement(oEl, cl1)) 
				swapClassInElement(oEl,cl1,cl2);
		}
	}
	return false;
}
function jtedit(value, cl1, cl2) {
    var oEl, i, elements;
    elements = document.getElementById('jt-content').getElementsByTagName(value); 
    for (i = 0; i < elements.length; i++) {
        if ($jt(elements[i])) {
            oEl = $jt(elements[i]);
            if (isClassInElement(oEl, cl1)) 
                swapClassInElement(oEl, cl1, cl2);
        }
    }
    return false;
}
////////////////////////
//toggle visibility of an array of elements depending on a classname or style
//
function jttogform(value, cl1, cl2) {
	var oEl, i, elements;
	elements = document.getElementById('jt-form').getElementsByTagName(value); 
	for (i=0; i < elements.length; i++ ) {
		if($jt(elements[i])){
			oEl = $jt(elements[i]);
			if(isClassInElement(oEl, cl1)) 
				swapClassInElement(oEl,cl1,cl2);
		}
	}
	return false;
}

function jtrefnot(objid) {
	if (objid == 1) {
		tog = jttogform('tr', 'jt-table-entry5', 'jt-table-entry6');
	} else {
		tog = jttogform('tr', 'jt-table-entry6', 'jt-table-entry5');
		elm = document.getElementById(objid);
		if (elm != null) elm.className = 'jt-table-entry6';
	}
	return false;
}


////////////////////////
// toggle visibility of notes and sources
//
function toggleNotesSources(action, bNotElid, lNotElid, bCitElid, lCitElid) {
	bNotEl = document.getElementById(bNotElid); 
	lNotEl = document.getElementById(lNotElid);  
	bCitEl = document.getElementById(bCitElid); 
	lCitEl = document.getElementById(lCitElid); 
	
	if (action == 0) {
		if(isClassInElement(bNotEl, 'jt-button-down-open')) 
			swapClassInElement(bNotEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lNotEl, 'jt-show')) 
			swapClassInElement(lNotEl,'jt-show','jt-hide');
		if(isClassInElement(bCitEl, 'jt-button-down-open')) 
			swapClassInElement(bCitEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lCitEl, 'jt-show')) 
			swapClassInElement(lCitEl,'jt-show','jt-hide');
	} else if (action == 1) {
		if(isClassInElement(bNotEl, 'jt-button-closed')) 
			swapClassInElement(bNotEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lNotEl, 'jt-hide')) 
			swapClassInElement(lNotEl,'jt-show','jt-hide');
		if(isClassInElement(bCitEl, 'jt-button-down-open')) 
			swapClassInElement(bCitEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lCitEl, 'jt-show')) 
			swapClassInElement(lCitEl,'jt-show','jt-hide');
	} else if (action == 2) {
		if(isClassInElement(bNotEl, 'jt-button-down-open')) 
			swapClassInElement(bNotEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lNotEl, 'jt-show')) 
			swapClassInElement(lNotEl,'jt-show','jt-hide');
		if(isClassInElement(bCitEl, 'jt-button-closed')) 
			swapClassInElement(bCitEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lCitEl, 'jt-hide')) 
			swapClassInElement(lCitEl,'jt-show','jt-hide');
	}
	
	return false;
}


////////////////////////
// show pop up for mouse over
//
function ShowPopup(hoverid, popupid, lpx, dpx) {
	var browser=navigator.appName;
	
	var offsetlpx = -180;
	var offsetdpx = 160;
	if (lpx == 0) { lpx = 10 + offsetlpx; } else  { lpx = lpx + offsetlpx; }
	if (dpx == 0) { dpx = 30 + offsetdpx; } else  { dpx = dpx + offsetdpx; }
	
	var hEl = document.getElementById(hoverid);
	var oEl = document.getElementById(popupid);
	
	// Set position of hover-over popup
	if (browser == 'Microsoft Internet Explorer' ) {
		if (!jte) var jte = window.event;
		if (jte.clientX || jte.clientY) {
			oEl.style.top = jte.clientY  + document.body.scrollTop  + document.documentElement.scrollTop  + 18 + 'px';
			oEl.style.left = jte.clientX + document.body.scrollLeft + document.documentElement.scrollLeft - 10 + 'px';
		} else {
			oEl.style.top =  hEl.offsetTop  + hEl.parentNode.offsetTop  + 312 + 'px';
			oEl.style.left = hEl.offsetLeft + hEl.parentNode.offsetLeft + 235 + 'px';
		}
	} else {
		oEl.style.top =  hEl.offsetTop  + dpx + 'px';
		oEl.style.left = hEl.offsetLeft - lpx + 'px';
	}
	// Close other popups
    popups = document.querySelectorAll('.jt-popup');
    for (var i = 0; i < popups.length; i++) {
        HidePopup(popups[i].id);
    }
	// Set popup to visible
	if(isClassInElement(oEl, 'jt-hide')) 
		swapClassInElement(oEl,'jt-hide','jt-show');
}

////////////////////////
// hide pop up for mouse over
//
function HidePopup(popupid) {
	oEl = document.getElementById(popupid);
	
	if(isClassInElement(oEl, 'jt-show')) 
		swapClassInElement(oEl,'jt-hide','jt-show');
}

