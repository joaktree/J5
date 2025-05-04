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

function getXMLHttp()
{
	var xmlHttp;
	
	try {
		//Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	}
	catch(e) {
		//Internet Explorer
		try  {
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch(e) {
			try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch(e) {
				// alert("Your browser does not support AJAX!")
				oEl = document.getElementById('noajaxid');
				if(isClassInElement(oEl, 'jt-hide')) {
					swapClassInElement(oEl,'jt-hide','jt-show');
				}
				return false;
			}
		}
	}
	return xmlHttp;
}


function MakeRequest(url, respid)
{
	var xmlHttp = getXMLHttp();
	
	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleResponse(xmlHttp.responseText, respid);
		}
	};
	
	xmlHttp.open("GET", url, true);
	xmlHttp.send(null);
}


function HandleResponse(response, respid) {
	document.getElementById(respid).innerHTML = response;
	
	// nvd
	// resetFooter();
	// end: nvd

}

////////////////////////
// show pop up for mouse over
//
function ShowAjaxPopup(hoverid, popupid, url) {
	if (!jte) var jte = window.event;

	// Close other popups
    popups = document.querySelectorAll('.jt-popup');
    for (var i = 0; i < popups.length; i++) {
        HidePopup(popups[i].id);
    }

	var browser=navigator.appName;
	
	hEl = document.getElementById(hoverid);
	oEl = document.getElementById(popupid);
	
	// Set popup to visible
	if(isClassInElement(oEl, 'jt-ajax')) {
		swapClassInElement(oEl,'jt-ajax','jt-show');
		MakeRequest(url, popupid);
	}
	if(isClassInElement(oEl, 'jt-hide')) {
		swapClassInElement(oEl,'jt-hide','jt-show');
	}

	basetop  = 0;
	baseleft = 0;
	jttop    = hEl.offsetTop;
	jtleft   = hEl.offsetLeft;
	
	// Find out the absolute values for the position
	nextEl   = hEl.offsetParent;
	while (nextEl) {
		jttop  += nextEl.offsetTop;
		jtleft += nextEl.offsetLeft;
		nextEl  = nextEl.offsetParent;
	}

	// Find out the basic values for the element
	nextEl   = oEl.offsetParent;
	while (nextEl) {
		basetop  += nextEl.offsetTop;
		baseleft += nextEl.offsetLeft;
		nextEl    = nextEl.offsetParent;
	}
	
	oEl.style.top  = jttop  - basetop  + 30 + 'px';
	oEl.style.left = jtleft - baseleft - 30 + 'px';
	
}

////////////////////////
//toggle visibility of sources
//
function toggleAjaxSources(bCitElid, lCitElid, url) {
	bCitEl = document.getElementById(bCitElid); 
	lCitEl = document.getElementById(lCitElid); 
	
	if(isClassInElement(bCitEl, 'jt-button-closed')) 
		swapClassInElement(bCitEl,'jt-button-down-open','jt-button-closed');
	if(isClassInElement(lCitEl, 'jt-ajax')) {
		swapClassInElement(lCitEl,'jt-show','jt-ajax');
		MakeRequest(url, lCitElid);
	}
	if(isClassInElement(lCitEl, 'jt-hide')) 
		swapClassInElement(lCitEl,'jt-show','jt-hide');

	// nvd
	// resetFooter();
	// end: nvd
	
	return false;
}

////////////////////////
// toggle visibility of notes and sources
//
function toggleAjaxNotesSources(action, bNotElid, lNotElid, bCitElid, lCitElid, url) {
	bNotEl = document.getElementById(bNotElid); 
	lNotEl = document.getElementById(lNotElid);  
	bCitEl = document.getElementById(bCitElid); 
	lCitEl = document.getElementById(lCitElid); 
	
	if (action == 1) {
		if(isClassInElement(bNotEl, 'jt-button-closed')) 
			swapClassInElement(bNotEl,'jt-button-down-open','jt-button-closed');
		if(isClassInElement(lNotEl, 'jt-ajax')) { 
			swapClassInElement(lNotEl,'jt-show','jt-ajax');
			MakeRequest(url, lNotElid);
		}
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
		if(isClassInElement(lCitEl, 'jt-ajax')) {
			swapClassInElement(lCitEl,'jt-show','jt-ajax');
			MakeRequest(url, lCitElid);
		}
		if(isClassInElement(lCitEl, 'jt-hide')) 
			swapClassInElement(lCitEl,'jt-show','jt-hide');
	}
	
	// nvd
	// resetFooter();
	// end: nvd
	return false;
}

////////////////////////
// toggle visibility of notes and sources
//
function drilldownAjax(respid, url) {
	oEl = document.getElementById(respid); 
	
	if(isClassInElement(oEl, 'jt-ajax')) {
		oEl.style.left = oEl.style.left + 10 + 'px';
		swapClassInElement(oEl,'jt-show','jt-ajax');
		MakeRequest(url, respid);
	}
	if(isClassInElement(oEl, 'jt-hide')) 
		swapClassInElement(oEl,'jt-show','jt-hide');

	// nvd
	// resetFooter();
	// end: nvd
}


////////////////////////
// toggle visibility of parents
//
function drilldownAjaxParent(butid, respid, url) {
	oEl = document.getElementById(respid); 
	bEl = document.getElementById(butid); 
	
	if(isClassInElement(oEl, 'jt-show') | isClassInElement(oEl, 'jt-hide')) 
		swapClassInElement(oEl,'jt-show','jt-hide');
	if(isClassInElement(oEl, 'jt-ajax')) {
		oEl.style.left = oEl.style.left + 10 + 'px';
		swapClassInElement(oEl,'jt-show','jt-ajax');
		MakeRequest(url, respid);
	}
	if(isClassInElement(bEl, 'jt-button-up-open') | isClassInElement(bEl, 'jt-button-closed')) 
		swapClassInElement(bEl,'jt-button-up-open','jt-button-closed');

	// nvd
	// resetFooter();
	// end: nvd
}


////////////////////////
// toggle visibility of details
//
function drilldownAjaxDetail(butid, respid, url) {
	oEl = document.getElementById(respid); 
	bEl = document.getElementById(butid); 
	
	if(isClassInElement(oEl, 'jt-show') | isClassInElement(oEl, 'jt-hide')) 
		swapClassInElement(oEl,'jt-show','jt-hide');
	if(isClassInElement(oEl, 'jt-ajax')) {
		oEl.style.left = oEl.style.left + 10 + 'px';
		swapClassInElement(oEl,'jt-show','jt-ajax');
		MakeRequest(url, respid);
	}
	if(isClassInElement(bEl, 'jt-button-down-open') | isClassInElement(bEl, 'jt-button-closed')) 
		swapClassInElement(bEl,'jt-button-down-open','jt-button-closed');

	// nvd
	// resetFooter();
	// end: nvd
}

////////////////////////
//set tabs correctly
//
function set_jtTabs() {
	var ElBar = document.getElementById("jt-tabbar"); 
	var El1   = document.getElementById("jt1tabid");
	var El2   = document.getElementById("jt2tabid");
	var El3   = document.getElementById("jt3tabid");
	var El4   = document.getElementById("jt4tabid");		
	var El5   = document.getElementById("jt5tabid");		
	
	if (El1 != null) {
		var moveRelative = ElBar.offsetHeight - El1.offsetHeight - 6;
		if (El1 != null) El1.style.top =  moveRelative + 'px';
		if (El2 != null) El2.style.top =  moveRelative + 'px';
		if (El3 != null) El3.style.top =  moveRelative + 'px';
		if (El4 != null) El4.style.top =  moveRelative + 'px';
		if (El5 != null) El5.style.top =  moveRelative + 'px';
	}
}

////////////////////////
//toggle visibility of notes and sources (tabs)
//
function toggleAjaxTabs(action, url) {
	setTimeout('toggleAjaxTabs2(\''+action+'\', \''+url+'\')', 150);
}

function toggleAjaxTabs2(action, url) {
	tab1El  = document.getElementById('jt1tabid'); 
	page1El = document.getElementById('jt1tabpageid');  
	tab2El  = document.getElementById('jt2tabid'); 
	page2El = document.getElementById('jt2tabpageid');  
	tab3El  = document.getElementById('jt3tabid'); 
	page3El = document.getElementById('jt3tabpageid');  
	tab4El  = document.getElementById('jt4tabid'); 
	page4El = document.getElementById('jt4tabpageid');  
	tab5El  = document.getElementById('jt5tabid'); 
	
	if (action == 1) {
		// make tab1 active
		if(isClassInElement(tab1El, 'jt-tab-inactive'))
			swapClassInElement(tab1El,'jt-tab-active','jt-tab-inactive');
		if(isClassInElement(tab1El, 'jt-tablabel-inactive')) 
			swapClassInElement(tab1El,'jt-tablabel-active','jt-tablabel-inactive');
		
		// make tab2 inactive
		if(isClassInElement(tab2El, 'jt-tab-active'))
			swapClassInElement(tab2El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab2El, 'jt-tablabel-active')) 
			swapClassInElement(tab2El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab3 inactive
		if(isClassInElement(tab3El, 'jt-tab-active'))
			swapClassInElement(tab3El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab3El, 'jt-tablabel-active')) 
			swapClassInElement(tab3El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab4 inactive
		if(isClassInElement(tab4El, 'jt-tab-active'))
			swapClassInElement(tab4El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab4El, 'jt-tablabel-active')) 
			swapClassInElement(tab4El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab5 inactive
		if(isClassInElement(tab5El, 'jt-tab-editactive'))
			swapClassInElement(tab5El,'jt-tab-edit','jt-tab-editactive');
		if(isClassInElement(tab5El, 'jt-tablabel-active')) 
			swapClassInElement(tab5El,'jt-tablabel-inactive','jt-tablabel-active');

		// make page1 active
		if(isClassInElement(page1El, 'jt-ajax')) { 
			swapClassInElement(page1El,'jt-show-block','jt-ajax');
			MakeRequest(url, 'jt1tabpageid');
		}
		if(isClassInElement(page1El, 'jt-hide')) { 
			swapClassInElement(page1El,'jt-show-block','jt-hide');
		}
		
		// make page2 inactive
		if(isClassInElement(page2El, 'jt-show-block')) { 
			swapClassInElement(page2El,'jt-hide','jt-show-block');
		}
		
		// make page3 inactive
		if(isClassInElement(page3El, 'jt-show-block')) { 
			swapClassInElement(page3El,'jt-hide','jt-show-block');
		}

		// make page4 inactive
		if(isClassInElement(page4El, 'jt-show-block')) { 
			swapClassInElement(page4El,'jt-hide','jt-show-block');
		}

		tog = jtedit('div', 'jt-edit-1', 'jt-edit-2');
	} else if (action == 2) {
		// make tab1 inactive
		if(isClassInElement(tab1El, 'jt-tab-active'))
			swapClassInElement(tab1El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab1El, 'jt-tablabel-active')) 
			swapClassInElement(tab1El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab2 active
		if(isClassInElement(tab2El, 'jt-tab-inactive'))
			swapClassInElement(tab2El,'jt-tab-active','jt-tab-inactive');
		if(isClassInElement(tab2El, 'jt-tablabel-inactive')) 
			swapClassInElement(tab2El,'jt-tablabel-active','jt-tablabel-inactive');
		
		// make tab3 inactive
		if(isClassInElement(tab3El, 'jt-tab-active'))
			swapClassInElement(tab3El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab3El, 'jt-tablabel-active')) 
			swapClassInElement(tab3El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab4 inactive
		if(isClassInElement(tab4El, 'jt-tab-active'))
			swapClassInElement(tab4El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab4El, 'jt-tablabel-active')) 
			swapClassInElement(tab4El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab5 inactive
		if(isClassInElement(tab5El, 'jt-tab-editactive'))
			swapClassInElement(tab5El,'jt-tab-edit','jt-tab-editactive');
		if(isClassInElement(tab5El, 'jt-tablabel-active')) 
			swapClassInElement(tab5El,'jt-tablabel-inactive','jt-tablabel-active');

		// make page1 inactive
		if(isClassInElement(page1El, 'jt-show-block')) { 
			swapClassInElement(page1El,'jt-hide','jt-show-block');
		}
		
		// make page2 active
		if(isClassInElement(page2El, 'jt-ajax')) { 
			swapClassInElement(page2El,'jt-show-block','jt-ajax');
			MakeRequest(url, 'jt2tabpageid');
		}
		if(isClassInElement(page2El, 'jt-hide')) { 
			swapClassInElement(page2El,'jt-show-block','jt-hide');
		}
		
		// make page3 inactive
		if(isClassInElement(page3El, 'jt-show-block')) { 
			swapClassInElement(page3El,'jt-hide','jt-show-block');
		}

		// make page4 inactive
		if(isClassInElement(page4El, 'jt-show-block')) { 
			swapClassInElement(page4El,'jt-hide','jt-show-block');
		}

		tog = jtedit('div', 'jt-edit-1', 'jt-edit-2');
	} else if (action == 3) {
		// make tab1 inactive
		if(isClassInElement(tab1El, 'jt-tab-active'))
			swapClassInElement(tab1El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab1El, 'jt-tablabel-active')) 
			swapClassInElement(tab1El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab2 inactive
		if(isClassInElement(tab2El, 'jt-tab-active'))
			swapClassInElement(tab2El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab2El, 'jt-tablabel-active')) 
			swapClassInElement(tab2El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab3 active
		if(isClassInElement(tab3El, 'jt-tab-inactive'))
			swapClassInElement(tab3El,'jt-tab-active','jt-tab-inactive');
		if(isClassInElement(tab3El, 'jt-tablabel-inactive')) 
			swapClassInElement(tab3El,'jt-tablabel-active','jt-tablabel-inactive');
		
		// make tab4 inactive
		if(isClassInElement(tab4El, 'jt-tab-active'))
			swapClassInElement(tab4El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab4El, 'jt-tablabel-active')) 
			swapClassInElement(tab4El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab5 inactive
		if(isClassInElement(tab5El, 'jt-tab-editactive'))
			swapClassInElement(tab5El,'jt-tab-edit','jt-tab-editactive');
		if(isClassInElement(tab5El, 'jt-tablabel-active')) 
			swapClassInElement(tab5El,'jt-tablabel-inactive','jt-tablabel-active');

		// make page1 inactive
		if(isClassInElement(page1El, 'jt-show-block')) { 
			swapClassInElement(page1El,'jt-hide','jt-show-block');
		}
		
		// make page2 inactive
		if(isClassInElement(page2El, 'jt-show-block')) { 
			swapClassInElement(page2El,'jt-hide','jt-show-block');
		}

		// make page3 active
		if(isClassInElement(page3El, 'jt-ajax')) { 
			swapClassInElement(page3El,'jt-show-block','jt-ajax');
			MakeRequest(url, 'jt3tabpageid');
		}
		if(isClassInElement(page3El, 'jt-hide')) { 
			swapClassInElement(page3El,'jt-show-block','jt-hide');
		}
		
		// make page4 inactive
		if(isClassInElement(page4El, 'jt-show-block')) { 
			swapClassInElement(page4El,'jt-hide','jt-show-block');
		}

		tog = jtedit('div', 'jt-edit-1', 'jt-edit-2');
	} else if (action == 4) {
		// make tab1 inactive
		if(isClassInElement(tab1El, 'jt-tab-active'))
			swapClassInElement(tab1El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab1El, 'jt-tablabel-active')) 
			swapClassInElement(tab1El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab2 inactive
		if(isClassInElement(tab2El, 'jt-tab-active'))
			swapClassInElement(tab2El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab2El, 'jt-tablabel-active')) 
			swapClassInElement(tab2El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab3 inactive
		if(isClassInElement(tab3El, 'jt-tab-active'))
			swapClassInElement(tab3El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab3El, 'jt-tablabel-active')) 
			swapClassInElement(tab3El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab4 active
		if(isClassInElement(tab4El, 'jt-tab-inactive'))
			swapClassInElement(tab4El,'jt-tab-active','jt-tab-inactive');
		if(isClassInElement(tab4El, 'jt-tablabel-inactive')) 
			swapClassInElement(tab4El,'jt-tablabel-active','jt-tablabel-inactive');
		
		// make tab5 inactive
		if(isClassInElement(tab5El, 'jt-tab-editactive'))
			swapClassInElement(tab5El,'jt-tab-edit','jt-tab-editactive');
		if(isClassInElement(tab5El, 'jt-tablabel-active')) 
			swapClassInElement(tab5El,'jt-tablabel-inactive','jt-tablabel-active');

		// make page1 inactive
		if(isClassInElement(page1El, 'jt-show-block')) { 
			swapClassInElement(page1El,'jt-hide','jt-show-block');
		}
		
		// make page2 inactive
		if(isClassInElement(page2El, 'jt-show-block')) { 
			swapClassInElement(page2El,'jt-hide','jt-show-block');
		}

		// make page3 inactive
		if(isClassInElement(page3El, 'jt-show-block')) { 
			swapClassInElement(page3El,'jt-hide','jt-show-block');
		}

		// make page4 active
		if(isClassInElement(page4El, 'jt-ajax')) { 
			swapClassInElement(page4El,'jt-show-block','jt-ajax');
			MakeRequest(url, 'jt4tabpageid');
		}
		if(isClassInElement(page4El, 'jt-hide')) { 
			swapClassInElement(page4El,'jt-show-block','jt-hide');
		}
		
		tog = jtedit('div', 'jt-edit-1', 'jt-edit-2');
	} else if (action == 5) {
		// make tab1 inactive
		if(isClassInElement(tab1El, 'jt-tab-active'))
			swapClassInElement(tab1El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab1El, 'jt-tablabel-active')) 
			swapClassInElement(tab1El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab2 inactive
		if(isClassInElement(tab2El, 'jt-tab-active'))
			swapClassInElement(tab2El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab2El, 'jt-tablabel-active')) 
			swapClassInElement(tab2El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab3 inactive
		if(isClassInElement(tab3El, 'jt-tab-active'))
			swapClassInElement(tab3El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab3El, 'jt-tablabel-active')) 
			swapClassInElement(tab3El,'jt-tablabel-inactive','jt-tablabel-active');

		// make tab4 inactive
		if(isClassInElement(tab4El, 'jt-tab-active'))
			swapClassInElement(tab4El,'jt-tab-inactive','jt-tab-active');
		if(isClassInElement(tab4El, 'jt-tablabel-active')) 
			swapClassInElement(tab4El,'jt-tablabel-inactive','jt-tablabel-active');
		
		// make tab5 inactive
		if(isClassInElement(tab5El, 'jt-tab-edit'))
			swapClassInElement(tab5El,'jt-tab-editactive','jt-tab-edit');
		if(isClassInElement(tab5El, 'jt-tablabel-inactive')) 
			swapClassInElement(tab5El,'jt-tablabel-active','jt-tablabel-inactive');

		// make page1 active
		if(isClassInElement(page1El, 'jt-hide')) { 
			swapClassInElement(page1El,'jt-show-block','jt-hide');
		}
		
		// make page2 inactive
		if(isClassInElement(page2El, 'jt-show-block')) { 
			swapClassInElement(page2El,'jt-hide','jt-show-block');
		}

		// make page3 inactive
		if(isClassInElement(page3El, 'jt-show-block')) { 
			swapClassInElement(page3El,'jt-hide','jt-show-block');
		}

		// make page4 inactive
		if(isClassInElement(page4El, 'jt-show-block')) { 
			swapClassInElement(page4El,'jt-hide','jt-show-block');
		}

		tog = jtedit('div', 'jt-edit-2', 'jt-edit-1');
	}

	// nvd
	// resetFooter();
	// end: nvd
	
	return false;
}

////////////////////////
//retrieve article
//
function retrieveAjaxArticle(url, respid, busysignal) {
	document.getElementById(respid).innerHTML = '<div class="jt-ajax-loader">'+busysignal+'</div>';
	MakeRequest(url, respid);
	
	// nvd
	// resetFooter();
	// end: nvd
	return false;
}

////////////////////////
//retrieve many-years-ago
//
function getManyYearsAgo(dayid, monthid, respid, url) {
	if ((dayid == 0) && (monthid == 0)) {
		url = url + "&day=0&month=0";
		
	} else {
		dEl = document.getElementById(dayid);
		mEl = document.getElementById(monthid);
		
		try {
			//Firefox, Opera 8.0+, Safari, chrome
			jtday = dEl.getValue();
		}
		catch(e) {
			//Internet Explorer
			jtday = dEl.value;
		}
	
		try {
			//Firefox, Opera 8.0+, Safari, chrome
			jtmonth = mEl.getValue();
		}
		catch(e) {
			//Internet Explorer
			jtmonth= mEl.value;
		}
			
		url = url + "&day=" + jtday + "&month=" + jtmonth;
	}
		
	MakeRequest(url, respid);
	
	return false;			
}

