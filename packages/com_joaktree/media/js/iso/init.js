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

var jtisotope = [];

document.addEventListener('DOMContentLoaded', function() {
	
mains = document.querySelectorAll('.isotope-main');
for(var i=0; i<mains.length; i++) {
	let $oneiso = mains[i];
	
	isoid = $oneiso.getAttribute("data");
	if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
		console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
		return false;
	}
	options_iso = Joomla.getOptions('com_joaktree_'+isoid);
	if (typeof options_iso === 'undefined' ) {return false}
	jtisotope[isoid] = new JTIsotope(isoid,options_iso)
    adiv = document.querySelector('.article-loading');
    jtisotope[isoid].addClass(adiv,'hidden');    
    adiv = document.querySelector('.isotope-main');
    jtisotope[isoid].removeClass(adiv,'hidden');
	jtisotope[isoid].goisotope(isoid);
}
}) // end of ready --------------
function JTIsotope(isoid,options) {
	$myiso = this;
    this.isoid = isoid;
	this.qsRegex = null;
	this.close = null;
	this.loadCount = 0;
	this.std_parents = ['family','alpha'] // otherwise it's a custom field
	this.parent = 'family';
	this.cookie_name = "com_joaktree_"+this.isoid;
	this.me = "#isotope-main-"+this.isoid+" ";
	this.options = options;
	this.filters = {};
	this.items_limit = this.options.limit_items;
	this.sav_limit = this.options.limit_items;
	this.empty_message = (this.options.empty == "true");
	this.asc = (this.options.ascending == "true");
	this.sort_by = this.options.sortby;
	this.grid_toggle = document.querySelector('.isotope_grid')
	this.iso_div = document.querySelector('.isotope-main .isotope-div')

	if (this.options.default_family == "")
		this.filters['family'] = ['*']
	else 
		this.filters['family'] = [this.options.default_family];
	if (this.options.default_letter == "")
        this.filters['alpha'] = ['*'];
    else {
        this.filters['alpha'] = [this.options.default_letter];
    }
}
JTIsotope.prototype.updateselectfamily = function (iso, onechar)  {
    if (iso.options.displayfilterfamily == 'hide') return;
    if (iso.options.displayfilterfamily == 'list' || iso.options.displayfilterfamily == 'listmulti') {
        sel = document.querySelector(iso.me+'#isotope-select-family');
        if (!sel) return;
        sels = sel.querySelectorAll('.choices__item');
        sels.forEach(element => {
            $data = element.getAttribute('data-value');
            if ($data && $data.substr(0,1).toUpperCase() == onechar) 
                element.style.display = 'inherit';
            else
                element.style.display = 'none';
        })
    } else { // buttons
        sel = document.querySelector(iso.me+'.filter-btn-grp-family');
        if (!sel) return;
        sels = sel.querySelectorAll('.btn');
        sels.forEach(element => {
            $data = element.getAttribute('data-sv');
            if ($data && $data.substr(0,1).toUpperCase() == onechar) 
                element.style.display = 'inline-block';
            else
                element.style.display = 'none';
        })
    }
    // remove filters with wrong first letter
    res = [];
    iso.filters['family'].forEach(elem => {
        if (elem.substr(0,1).toUpperCase() == onechar) res.push( elem );
    })
    if (res.length == 0) {
        iso.filters['family'] =  ['*'];
    } else {
        iso.filters['family'] = res;
    }
    iso.update_cookie_filter();
    if (iso.iso) iso.iso.arrange();
}

JTIsotope.prototype.goisotope = function(isoid) {
	$myiso = jtisotope[isoid];
	$myiso.cookie = $myiso.getCookie($myiso.cookie_name);
	if ($myiso.cookie != "") {
		let $arr = $myiso.cookie.split('&');
		for (let index = 0; index < $arr.length; ++index) {
			$myiso.splitCookie($myiso.isoid,$arr[index]);
		}
	}
	if ($myiso.options.default_letter) { 
        $button =  document.querySelector( $myiso.me+'.iso_btn_alpha_'+ $myiso.filters['alpha']);
        if ($button)    $myiso.addClass($button,'is-checked');
        $button = document.querySelector( this.me+'.iso_btn_alpha_tout');
        if ($button)    $myiso.addClass($button,'hidden');
        $myiso.updateselectfamily($myiso,$myiso.filters['alpha']);
    }
	$items = document.querySelectorAll('#isotope-main-' + $myiso.isoid + ' .iso_itm');
	for (var i=0; i< $items.length;i++) {
		if (($myiso.options.layout == "masonry") || ($myiso.options.layout == "fitRows") || ($myiso.options.layout == "packery"))
			$items[i].style.width = (100 / parseInt($myiso.options.nbcol)-2)+"%" ;
		if ($myiso.options.layout == "vertical") 
			$items[i].style.width = "100%";
		$items[i].style.background = $myiso.options.background;
	}

    if (typeof $myiso.sort_by === 'string') {
		$myiso.sort_by = $myiso.sort_by.split(',');
	}
	var grid = document.querySelector($myiso.me + '.isotope_grid');
	$myiso.iso = new Isotope(grid,{ 
			itemSelector: $myiso.me+'.iso_itm',
			percentPosition: true,
			layoutMode: $myiso.options.layout,
			getSortData: {
				family: '[data-family]',
				date: '[data-date]',
				title: '[data-title]',
			},
			sortBy: $myiso.sort_by,
			sortAscending: $myiso.asc,
			isJQueryFiltering : false,
			filter: function(itemElem ){ 
				if (itemElem) {
					$id = itemElem.parentNode.getAttribute('data');
					return jtisotope[$id].grid_filter($id,itemElem)	
				}
				return false;
			}
	}); // end of Isotope definition
	imagesLoaded(grid, function() {
		$myiso = jtisotope[this.elements[0].getAttribute('data')];
		$myiso.updateFilterCounts();
		if ($myiso.sort_by == "random") {
			$myiso.iso.shuffle();
		}
	}); 

	if ($myiso.options.displayrange == "true") {
		if (!$myiso.min_range) {
			$myiso.min_range = parseInt($myiso.options.minrange);
			$myiso.max_range = parseInt($myiso.options.maxrange);
		}
		$myiso.rangeSlider = new rSlider({
			target: '#rSlider',
			values: {min:parseInt($myiso.options.minrange), max:parseInt($myiso.options.maxrange)},
			step: parseInt($myiso.options.rangestep),
			set: [$myiso.min_range,$myiso.max_range],
			range: true,
			tooltip: true,
			scale: true,
			labels: true,
			onChange: $myiso.rangeUpdated,
		});
	}		
	iso_div = document.querySelector($myiso.me + '.isotope-div');
	iso_div.addEventListener("refresh", function(){
 	  $myiso.iso.arrange();
	});
	var sortbybutton = document.querySelectorAll($myiso.me+'.sort-btn-grp button');

	for (var i=0; i< sortbybutton.length;i++) {
		['click', 'touchstart'].forEach(type => {
			sortbybutton[i].addEventListener(type,e => {
				let isoid = e.srcElement.parentNode.getAttribute('data'); // get isotope component id
				let isoobj = jtisotope[isoid]; // get isotope object
				e.stopPropagation();
				e.preventDefault();		
				isoobj.update_sort_buttons(e.srcElement);
				for (var j=0; j< sortbybutton.length;j++) {
					sortbybutton[j].classList.remove('is-checked');
				}
				e.srcElement.classList.add('is-checked');
			});
		})
	}
// use value of search field to filter
	$myiso.quicksearch = document.querySelector($myiso.me+'.quicksearch');
	if ($myiso.quicksearch) {
		$myiso.quicksearch.addEventListener('keyup',e => {
			this.qsRegex = new RegExp( this.quicksearch.value, 'gi' );
			this.CG_Cookie_Set(this.isoid,'search',this.quicksearch.value);
					  
			this.updateFilterCounts();
		});
	}
//  clear search button + reset filter buttons
    var cancelsquarred = document.querySelectorAll($myiso.me+'.ison-cancel-squared');
	for (var cl=0; cl< cancelsquarred.length;cl++) {
	['click', 'touchstart'].forEach(type => {
		cancelsquarred[cl].addEventListener( type, function(e) {
			let isoid = this.getAttribute('data');
			if (!isoid) isoid = this.parentNode.getAttribute('data'); // get isotope component id
			let isoobj = jtisotope[isoid]; // get isotope object
			e.stopPropagation();
			e.preventDefault();	
			if (isoobj.quicksearch) {
				isoobj.quicksearch.value = "";
			}
			isoobj.qsRegex = new RegExp( "", 'gi' );
			isoobj.CG_Cookie_Set(isoobj.isoid,'search',"");
			if (isoobj.rangeSlider) {
				range_sel = isoobj.range_init;
				ranges = range_sel.split(",");
				isoobj.rangeSlider.setValues(parseInt(ranges[0]),parseInt(ranges[1]));
				isoobj.CG_Cookie_Set(isoobj.isoid,'range',range_sel);
			}
			isoobj.filters['family'] = ['*']
			grouptype = ['family','alpha'];
			for (var g = 0; g < grouptype.length;g++) {
				agroup = document.querySelectorAll(isoobj.me+'.filter-btn-grp-'+grouptype[g]+' button'); 
				for (var i=0; i< agroup.length;i++) {
					agroup[i].classList.remove('is-checked');
					if (agroup[i].getAttribute('data-sv') == "*") isoobj.addClass(agroup[i],'is-checked');
					if (agroup[i].getAttribute('data-all') == "all") agroup[i].setAttribute('selected',true);
				}
			}
			agroup= document.querySelectorAll(isoobj.me+'select[id^="isotope-select-"]');
			for (var i=0; i< agroup.length;i++) {
				var myval = agroup[i].parentElement.parentElement.parentElement.getAttribute('data-fg');
				var elChoice = document.querySelector('joomla-field-fancy-select#isotope-select-'+myval);
				var choicesInstance = elChoice.choicesInstance;
				choicesInstance.removeActiveItems();
				choicesInstance.setChoiceByValue('')
				// isoobj.filters[myval] = ['*']
			};
            if (isoobj.options.default_letter == "")
                isoobj.filters['alpha'] = ['*'];
            else {
                isoobj.filters['alpha'] = [isoobj.options.default_letter];
                $button =  document.querySelector( isoobj.me+'.iso_btn_alpha_'+ isoobj.options.default_letter);
                if ($button)    isoobj.addClass($button,'is-checked');
                isoobj.updateselectfamily(isoobj,isoobj.options.default_letter);
            }
            
			isoobj.update_cookie_filter();
			isoobj.updateFilterCounts();
			if (isoobj.quicksearch) {
				isoobj.quicksearch.focus();
			}
		});
	})
	}
	if ($myiso.options.displayfilterfamily == "multi") {
		$myiso.events_multibutton('family')
	}
	if ($myiso.options.displayfilterfamily == "button"){
		$myiso.events_button('family');
	}
	if ($myiso.options.displayfilterfamily == "list")  { 
		$myiso.events_list('family');
	} 
	if ($myiso.options.displayfilterfamily == "listmulti") {
		$myiso.events_listmulti('family');
	}
	if ($myiso.options.displayalpha == "button") { 
		$myiso.events_button('alpha');
	}
}// end of goisotope

JTIsotope.prototype.rangeUpdated = function(){
	let rangeid = this.target;
	let obj = document.querySelector(rangeid);
	let isoid = obj.getAttribute('data');
	let isoobj = jtisotope[isoid];
	isoobj.range_sel = isoobj.rangeSlider.getValue();
	isoobj.range_init = isoobj.rangeSlider.conf.values[0]+','+isoobj.rangeSlider.conf.values[isoobj.rangeSlider.conf.values.length - 1];
	isoobj.CG_Cookie_Set(isoobj.isoid,'range',isoobj.range_sel);
	isoobj.iso.arrange();
}
// remove buttons eventListeners
JTIsotope.prototype.remove_events_button = function(component) {
	agroup= document.querySelectorAll(this.me+'.filter-btn-grp-'+component+' button');
	for (var i=0; i< agroup.length;i++) {
		['click', 'touchstart'].forEach(type => {
			agroup[i].removeEventListener(type,this.listenbutton);
			agroup[i].removeEventListener(type,this.listenmultibutton);
		})
	};
}
JTIsotope.prototype.listenbutton= function(evt){
	evt.stopPropagation();
	evt.preventDefault();
	id = evt.currentTarget.parentNode.getAttribute('data');
	jtisotope[id].filter_button(evt.currentTarget,evt);
	mygroup= evt.currentTarget.parentNode.querySelectorAll('button' );
	for (var g=0; g< mygroup.length;g++) {
		jtisotope[id].removeClass(mygroup[g],'is-checked');
	}
	jtisotope[id].addClass(evt.currentTarget,'is-checked');
    agrp = evt.currentTarget.parentNode.getAttribute('data-fg');
    if (agrp && agrp == 'alpha') {
        if (jtisotope[id].options.displayfilterfamily == 'list' || jtisotope[id].options.displayfilterfamily == 'listmulti') {
            var elChoice = document.querySelector('joomla-field-fancy-select#isotope-select-family');
            var choicesInstance = elChoice.choicesInstance;
            choicesInstance.removeActiveItems();
            choicesInstance.setChoiceByValue('')
        }
        onechar = evt.currentTarget.getAttribute('data-sv');
        if (onechar) jtisotope[id].updateselectfamily(jtisotope[id],onechar);
    }
};
// create buttons eventListeners
JTIsotope.prototype.events_button = function(component) {
	agroup= document.querySelectorAll(this.me+'.filter-btn-grp-'+component+' button');
	for (var i=0; i< agroup.length;i++) {
		['click', 'touchstart'].forEach(type => {
			agroup[i].addEventListener(type,this.listenbutton);
		})
	};
}
// create multiselect buttons eventListeners
JTIsotope.prototype.events_multibutton = function(component) {
	agroup= document.querySelectorAll(this.me+'.filter-btn-grp-'+component+' button');
	for (var i=0; i< agroup.length;i++) {
		['click', 'touchstart'].forEach(type =>{
			agroup[i].addEventListener(type,this.listenmultibutton);
		})
	};
}
JTIsotope.prototype.listenmultibutton = function(evt){
	evt.stopPropagation();
	evt.preventDefault();
	id = evt.currentTarget.parentNode.getAttribute('data');
	jtisotope[id].filter_multi(evt.currentTarget,evt);
	jtisotope[id].set_buttons_multi(evt.currentTarget);
}
// create list eventListeners
JTIsotope.prototype.events_list = function(component) {
	agroup= document.querySelectorAll(this.me+'.filter-btn-grp-'+component);
	for (var i=0; i< agroup.length;i++) {
		agroup[i].addEventListener('choice',(evt, params) => {
			this.filter_list(this,evt,'choice')
			});
		agroup[i].addEventListener('removeItem',(evt, params) => {
			this.filter_list(this,evt,'remove');
		});
			
	};
	var elChoice = document.querySelector('joomla-field-fancy-select#isotope-select-'+component);
	if (!elChoice) return;
	var choicesInstance = elChoice.choicesInstance;
	choicesInstance.setChoiceByValue(this.filters[component]);
}
// create listmulti eventListeners
JTIsotope.prototype.events_listmulti = function(component) {
	agroup= document.querySelectorAll(this.me+'select[id^="isotope-select-'+component+'"]');
	for (var i=0; i< agroup.length;i++) {
		agroup[i].addEventListener('choice',(evt, params) => {
			$myiso.filter_list_multi(this,evt,'choice');
		});
		agroup[i].addEventListener('removeItem',(evt, params) => {
			$myiso.filter_list_multi(this,evt,'remove');
		});
		$parent = agroup[i].parentElement.parentElement.parentElement.getAttribute('data-fg');
		if (typeof $myiso.filters[$parent] === 'undefined' ) { 
            $myiso.filters[$parent] = ['*'];
		}
	};
	if ((this.filters[$parent][0] != '*') && (this.filters[$parent].length == 1)) {
		var elChoice = document.querySelector('joomla-field-fancy-select#isotope-select-'+component);
		var choicesInstance = elChoice.choicesInstance;
		var savefilter = this.filters[$parent][0];
		choicesInstance.removeActiveItemsByValue(''); // remove all 
		choicesInstance.setChoiceByValue(savefilter);
	}	
}

JTIsotope.prototype.update_sort_buttons = function(obj) {
	var sortValue = obj.getAttribute('data-sv');
	if (sortValue == "random") {
		this.CG_Cookie_Set(this.isoid,'sort',sortValue+'-');
		this.iso.shuffle();
		return;
	} 
	sens = obj.getAttribute('data-sens');
	sortValue = sortValue.split(',');
	if (!this.hasClass(obj,'is-checked')) { // first time sorting
		sens = obj.getAttribute('data-init');
		obj.setAttribute("data-sens",sens);
		asc=true;
		if (sens== "-") asc = false;
	} else { // invert order
		if (sens == "-") {
			obj.setAttribute("data-sens","+");
			asc = true;
		} else {
			obj.setAttribute("data-sens","-");
			asc = false;
		}
	}
	sortAsc = {};
	for (i=0;i < sortValue.length;i++) {
		if ( sortValue[i] == "featured"){  // featured always first
			sortAsc[sortValue[i]] = false ;
		} else {
			sortAsc[sortValue[i]] = asc;
		}
	}
	this.CG_Cookie_Set(this.isoid,'sort',sortValue+'-'+asc);
	this.iso.options.sortBy = sortValue;
	this.iso.options.sortAscending = sortAsc;
	this.iso.arrange();
}
JTIsotope.prototype.filter_list = function($this,evt,params) {
	obj = evt.currentTarget;
	$parent = obj.getAttribute('data-fg');
	$isclone = false;
    $selectid = obj.getAttribute('data-fg');
	sortValue = obj.querySelector(".is-highlighted");
	sortValue = sortValue.dataset.value;
	if (typeof sortValue === 'undefined') sortValue = ""
	elChoice = document.querySelector('joomla-field-fancy-select#isotope-select-'+$selectid);
	choicesInstance = elChoice.choicesInstance;
	if (params == 'remove' ) { // remove item from offcanvas => remove button
		$this.removeFilter( $this.filters, $parent, evt.detail.value );
		if ($this.filters[$parent].length == 0) {
			$this.filters[$parent] = ['*'] ;
			choicesInstance.setChoiceByValue('')
			$this.update_cookie_filter();
			$this.updateFilterCounts();
		}	
		return;
	}
	if (sortValue == '')   {
		choicesInstance.removeActiveItems();
		choicesInstance.setChoiceByValue('');
		$this.filters[$parent] = ['*'];
		$buttons = document.querySelectorAll('#clonedbuttons [data-fg="'+$parent+'"]');
		for (var i=0; i< $buttons.length;i++) { // remove buttons
			$buttons[i].remove(); 
		}
	} else { 
		$this.filters[$parent] = [sortValue];
        if (choicesInstance.getValue().value != sortValue) {
            choicesInstance.setChoiceByValue(sortValue);
        }
	}
	$this.update_cookie_filter();
	$this.updateFilterCounts();
}
	// ----- Filter MultiSelect List
JTIsotope.prototype.filter_list_multi = function($this,evt,params) {
		$evnt = evt;
		obj = evt.currentTarget;
		$params = params;
		$parent = obj.parentNode.parentNode.parentNode.getAttribute('data-fg')
		$selectid = obj.getAttribute('id');
		if (typeof $this.filters[$parent] === 'undefined' ) { 
			$this.filters[$parent] = [];
		}
		var elChoice = document.querySelector('joomla-field-fancy-select#'+$selectid);
		var choicesInstance = elChoice.choicesInstance;
		
		if ($params == "remove") { // deselect element except all
			this.removeFilter( $this.filters, $parent, $evnt.detail.value );
			if ($this.filters[$parent].length == 0) {
                $this.filters[$parent] = ['*'] ;
				choicesInstance.setChoiceByValue('')
			}
		}
		if ($params == "choice") {
            let sel = $evnt.detail.choice.value;
 			if (sel == '') {// all
				$this.filters[$parent] = ['*'];
				choicesInstance.removeActiveItems();
				choicesInstance.setChoiceByValue('');
			} else {
				if ($this.filters[$parent].indexOf('*') != -1) { // was all
					choicesInstance.removeActiveItemsByValue('')
					$this.filters[$parent] = []; // remove it
				}
				$this.addFilter( $this.filters, $parent, sel );
			}
			choicesInstance.hideDropdown();
		}
        if ($this.options.default_letter) {
            this.updateselectfamily(this,this.filters['alpha']);
        }
		$this.update_cookie_filter();
		$this.updateFilterCounts();
	}
/*------- grid filter --------------*/
JTIsotope.prototype.grid_filter = function($id,elem) {
	var $myiso = jtisotope[$id];
	var searchResult = $myiso.qsRegex ? elem.textContent.match( $myiso.qsRegex ) : true;
	var	lafam = elem.getAttribute('data-family');
	var buttonResult = false;
	var rangeResult = true;
	var searchAlpha = true;
	if ($myiso.filters['alpha'].indexOf('*') == -1) {// alpha filter
		alpha = elem.getAttribute('data-alpha').substring(0,1);
		if ($myiso.filters['alpha'].indexOf(alpha) == -1) return false;
	}
	if 	($myiso.rangeSlider) {
		var lerange = elem.getAttribute('data-range');
		if ($myiso.range_sel != $myiso.range_init) {
			ranges = $myiso.range_sel.split(",");
			rangeResult = (parseInt(lerange) >= parseInt(ranges[0])) && (parseInt(lerange) <= parseInt(ranges[1]));
		}
	}
	if ($myiso.filters['family'].indexOf('*') != -1)  { return searchResult && rangeResult && true};
	if ($myiso.filters['family'].indexOf('*') == -1) { // on a demandé une classe
		if ($myiso.filters['family'].indexOf(lafam) == -1)  {
			return false; // n'appartient pas à la bonne classe: on ignore
		} else {  // on a trouvé la famille
            buttonResult = true;
        } 
	}
	return searchResult && rangeResult && buttonResult;
} 
     
JTIsotope.prototype.filter_button = function(obj,evt) {
		$myid = obj.parentNode.getAttribute('data');
		$myiso = jtisotope[$myid];
		if ($myiso.hasClass(obj,'disabled')) return; //ignore disabled buttons
		$parent = obj.parentNode.getAttribute('data-fg');
		child =  obj.getAttribute('data-child'); // child group number
		var sortValue = obj.getAttribute('data-sv');
		$isclone = false;
		if (typeof $myiso.filters[$parent] === 'undefined' ) { 
			$myiso.filters[$parent] = {};
		}
		$needclone = false;
		$grdparent = obj.parentNode.parentNode;
		if (sortValue == '*') {
            $myiso.filters[$parent] = ['*'];
            if ($parent == 'alpha' && $myiso.options.default_letter) {
                $myiso.filters[$parent] = [$myiso.options.default_letter];
			}
		} else { 
			$myiso.filters[$parent]= [sortValue];
			if (child) {
				$myiso.set_family($myiso.me,'',child,sortValue,'button');
			}
		}
		$myiso.update_cookie_filter();
		$myiso.updateFilterCounts();
	}
JTIsotope.prototype.filter_multi = function(obj,evt) {
		id = obj.parentNode.getAttribute('data');
		$myiso = jtisotope[id];
		var sortValue = obj.getAttribute('data-sv');
		child =  obj.getAttribute('data-child'); // child group number
		$isclone = false;
		$parent = obj.parentNode.getAttribute('data-fg');
		$myiso.toggleClass(obj,'is-checked');
		var isChecked = $myiso.hasClass(obj,'is-checked');
		// clone offcanvas button
		$needclone = false;
		$grdparent = obj.parentNode.parentNode;

		if (typeof $myiso.filters[$parent] === 'undefined' ) { 
			$myiso.filters[$parent] = [];
		}
		if (sortValue == '*') {
			$myiso.filters[$parent] = ['*'];
			if (child) {
				$myiso.set_family_all($myiso.me,child,'button')
			}
		} else { 
			$myiso.removeFilter($myiso.filters, $parent,'*');
			$myiso.removeFilter($myiso.filters, $parent,'none');
			if ( isChecked ) {
				$myiso.addFilter( $myiso.filters, $parent,sortValue );
				if (child) {
					$myiso.set_family($myiso.me,$parent,child,sortValue,'button')
				}
			} else {
				$myiso.removeFilter( $myiso.filters, $parent, sortValue );
				if ($myiso.filters[$parent].length == 0) {// no more selection
					$myiso.filters[$parent] = ['*'];
				}
				if (child) {
					if ($myiso.filters[$parent] == ['*']) {// no more selection
						$myiso.set_family_all($myiso.me,child,'button')
					} else { // remove current selection
						$myiso.del_family($myiso.me,$parent,child,sortValue,'button')
					}
				}
			}	
		}
		$myiso.update_cookie_filter();
		$myiso.updateFilterCounts();
	}
JTIsotope.prototype.set_buttons_multi = function(obj) {
	$parent = obj.parentNode.getAttribute('data-fg');
	if (obj.getAttribute('data-sv') == '*') { // on a cliqué sur tout => on remet le reste à blanc
		buttons = obj.parentNode.querySelectorAll('button.is-checked');
		for (var i=0; i< buttons.length;i++) { 
				this.removeClass(buttons[i],'is-checked');
		}
		this.addClass(obj,'is-checked');
	} else { // on a cliqué sur un autre bouton : uncheck le bouton tout
		if ((this.filters[$parent].length == 0) || (this.filters[$parent] == '*')) {// plus rien de sélectionné : on remet tout actif
			button_all = obj.parentNode.querySelector('[data-sv="*"]');
			this.addClass(button_all,'is-checked');
			this.filters[$parent] = ['*'];
			this.update_cookie_filter();
			this.iso.arrange();
		}
		else {
			button_all = obj.parentNode.querySelector('[data-sv="*"]');
			this.removeClass(button_all,'is-checked');
		}
	}
}
// check items limit and hide unnecessary items
JTIsotope.prototype.updateFilterCounts = function() {
	this.iso.arrange();
}
JTIsotope.prototype.debounce = function( fn, threshold ) {
	var timeout;
	return function debounced() {
		if ( timeout ) {
			clearTimeout( timeout );
		}
	function delayed() {
		fn();
		timeout = null;
		}
	timeout = setTimeout( delayed, threshold || 100 );
	}  
}
JTIsotope.prototype.addFilter = function( filters, $parent, filter ) {
	if (!$parent) return;
	if ( filters[$parent].indexOf( filter ) == -1 ) {
		filters[$parent].push( filter );
	}
}
JTIsotope.prototype.removeFilter = function( filters, $parent, filter ) {
	if (!Array.isArray(filters[$parent])) filters[$parent] = ['*']; // lost : assume all
	var index = filters[$parent].indexOf( filter);
	if ( index != -1 ) {
		filters[$parent].splice( index, 1 );
	}
}	
JTIsotope.prototype.update_cookie_filter=function() {
	$filter_cookie = "";
	for (x in this.filters) {
		if (x == "null") continue;
		if ($filter_cookie.length > 0) $filter_cookie += ">";
		$filter_cookie += x+'<'+this.filters[x].toString();
	}
	if ($filter_cookie.length > 0) $filter_cookie += ">";
	this.CG_Cookie_Set(this.isoid,'filter',$filter_cookie);
}
JTIsotope.prototype.getCookie = function(name) {
  let matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : '';
}
JTIsotope.prototype.CG_Cookie_Set = function(id,param,b) {
	$myiso = jtisotope[id];
	var expires;
	duration = $myiso.options.cookieduration;
    var d = new Date();
	if( (typeof duration === 'undefined') || (duration == 0) ) expires = ""; // default duration : session
	else if (duration == '-1') expires = ";Max-Age=0;"; // no cookie
	else if (duration == '1d') { // 1 day
		d.setTime(d.getTime() + (1*24*60*60*1000));
		expires = ";expires="+ d.toUTCString();
	} 
	else if (duration == '1w') { // 1 week
		d.setTime(d.getTime() + (7*24*60*60*1000));
		expires = ";expires="+ d.toUTCString();
	}
	else if (duration == '1m') { // 1 month
		d.setTime(d.getTime() + (30*24*60*60*1000));
		expires = ";expires="+ d.toUTCString();
	}
	$secure = "";
	if (window.location.protocol == "https:") $secure="secure;"; 
	lecookie = $myiso.getCookie($myiso.cookie_name);
	$val = param+':'+b;
	$cook = $val;
	if (lecookie != '') {
		if (lecookie.indexOf(param) >=0 ) { // cookie contient le parametre
			$cook = "";
			$arr = lecookie.split('&');
			$arr.forEach($myiso.replaceCookie,$val);
		} else { // ne contient pas encore ce parametre : on ajoute
			$cook = lecookie +'&'+$val;
		}
	}
	document.cookie = $myiso.cookie_name+"="+$cook+expires+"; path=/; samesite=lax;"+$secure;
}
JTIsotope.prototype.replaceCookie = function(item,index,arr) {
	if (this.startsWith('search:') && (item.indexOf('search:') >= 0)) {
		arr[index] = this;
	}
	if (this.startsWith('sort:') && (item.indexOf('sort:') >= 0)) {
		arr[index] = this;
	}
	if (this.startsWith('filter:') && (item.indexOf('filter:') >= 0)) {
		arr[index] = this;
	}
	if (this.startsWith('range:') && (item.indexOf('range:') >= 0)) {
		arr[index] = this;
	}
	if ($cook.length > 0) $cook += "&";
	$cook += arr[index];
}
JTIsotope.prototype.splitCookie = function(isoid,item) {
	// check if quicksearch still exists (may be removed during testing)
	this.quicksearch = document.querySelector(this.me+'.quicksearch');
	if (item.indexOf('search:') >= 0 &&  this.quicksearch ) {
		val = item.split(':');
		this.qsRegex = new RegExp( val[1], 'gi' );
		this.quicksearch.value = val[1];
	}
	if (item.indexOf('sort:') >= 0) {
		val = item.split(':');
        jtisotope[isoid].sort_by = val[1];  // val[0] contains 'sort'
		val = val[1].split('-');
		sort_by = val[0].split(',');
		asc = (val[1] == "true");
        sortAsc = {};
		for (i=0;i < sort_by.length;i++) {
			if ( sort_by[i] == "featured"){  // featured always first
				sortAsc[sort_by[i]] = false ;
			} else {
				sortAsc[sort_by[i]] = asc;
			}
		}
		asc = sortAsc;
        jtisotope[isoid].asc = asc;

		sortButtons = document.querySelectorAll(this.me+'.sort-btn-grp button');
		for(s=0;s < sortButtons.length;s++) {
			if (val[0] != '*') { // tout
				sortButtons[s].classList.remove('is-checked');
				if (sortButtons[s].getAttribute("data-sv") == val[0]) {
					sortButtons[s].classList.add('is-checked');
					sortButtons[s].setAttribute("data-sens","+");
					if (val[1] != "true") sortButtons[s].setAttribute("data-sens","-");
				}
			}
		}
	}
	if (item.indexOf('filter:') >=0) {
		val = item.split(':');
		if (val[1].length > 0) {
			val = val[1].split('>'); // get filters
			for (x=0;x < val.length-1;x++) {
				values = val[x].split("<");
				if (this.std_parents.indexOf(values[0]) != -1) { // not a custom field
					if (values[1] != '*') { // !tout
						this.filters[values[0]] = values[1].split(',');
						if (values[0] == 'lang') {
							filterButtons = document.querySelectorAll(this.me+'.iso_lang button.is-checked');
						} else {
							filterButtons = document.querySelectorAll(this.me+'.filter-btn-grp-'+values[0]+' button.is-checked')
						}
						for(f=0;f < filterButtons.length;f++) {
							filterButtons[f].classList.remove('is-checked');
						}
						for(v=0;v < this.filters[values[0]].length;v++) {
                            $button =  document.querySelector( this.me+'.iso_btn_'+values[0]+'_'+ this.filters[values[0]][v]);
							if (!$button) continue; // not defined : ignore it
							this.addClass($button,'is-checked');
						}
					}
                }
            }
        }
	}
	if (item.indexOf('range:') >=0) {
		val = item.split(':');
		if (val[1].length > 0) {
			spl = val[1].split(",");
			this.min_range =parseInt(spl[0]);
			this.max_range =parseInt(spl[1]);
		}
	}
}
// from https://code.tutsplus.com/tutorials/from-jquery-to-javascript-a-reference--net-23703
JTIsotope.prototype.hasClass = function (el, cl) {
    var regex = new RegExp('(?:\\s|^)' + cl + '(?:\\s|$)');
    return !!el.className.match(regex);
}
JTIsotope.prototype.addClass = function (el, cl) {
    if (!$myiso.hasClass(el, cl)) el.className += ' ' + cl;
},
JTIsotope.prototype.removeClass = function (el, cl) {
    var regex = new RegExp('(?:\\s|^)' + cl + '(?:\\s|$)');
    el.className = el.className.replace(regex, ' ');
},
JTIsotope.prototype.toggleClass = function (el, cl) {
    $myiso.hasClass(el, cl) ? $myiso.removeClass(el, cl) : $myiso.addClass(el, cl);
};
// from https://gist.github.com/andjosh/6764939
JTIsotope.prototype.scrollTo = function(to, duration) {
    var element = document.scrollingElement || document.documentElement,
    start = element.scrollTop,
    change = to - start,
    startTs = performance.now(),
    // t = current time
    // b = start value
    // c = change in value
    // d = duration
    easeInOutQuad = function(t, b, c, d) {
        t /= d/2;
        if (t < 1) return c/2*t*t + b;
        t--;
        return -c/2 * (t*(t-2) - 1) + b;
    },
    animateScroll = function(ts) {
        var currentTime = ts - startTs;
        element.scrollTop = parseInt(easeInOutQuad(currentTime, start, change, duration));
        if(currentTime < duration) {
            requestAnimationFrame(animateScroll);
        }
        else {
            element.scrollTop = to;
        }
    };
    requestAnimationFrame(animateScroll);
}
JTIsotope.prototype.resetToggle = function () {
	if ($myiso.grid_toggle && $myiso.hasClass($myiso.grid_toggle,'isotope-hide')) {
		$myiso.removeClass($myiso.iso_article,'isotope-open');
		$myiso.addClass($myiso.iso_article,'isotope-hide');
		$myiso.removeClass($myiso.grid_toggle,'isotope-hide');
		$myiso.iso_div.refresh;
	} else if ($myiso.iso_article && $myiso.hasClass($myiso.iso_article,'isotope-open')) {
		$myiso.removeClass($myiso.iso_article,'isotope-open');
		$myiso.addClass($myiso.iso_article,'isotope-hide');
		$myiso.iso_article.innerHTML('');
		$myiso.iso_div.refresh;
	}
}
/*------- grid filter --------------*/
grid_filter = function(elem) {
} 
go_click = function($entree,$link) {
	event.preventDefault();
	if (($entree == "webLinks") || (window.event.ctrlKey) ) {
		 window.open($link,'_blank')
	} else {
		location=$link;
	}
}
