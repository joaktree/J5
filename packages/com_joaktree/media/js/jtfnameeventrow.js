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
var jtfneroptions;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtfneroptions = Joomla.getOptions('jtfnameeventrow');
	if (typeof jtfneroptions === 'undefined' ) {return false}
})

function inject_namevtrow(table_body, appid){
    var htmlToElements = function(str){
    var objhtml = { html:'<table><tbody>' + str + '</tbody></table>' };
    let block = document.createElement('div');
    block.innerHTML = objhtml.html;
    return block;
    }
    var el = document.getElementById(table_body);
    var orderNumber = parseInt(document.getElementById('namevtcounter').value) + 1;
    document.getElementById('namevtcounter').value = orderNumber;
    var rownam = 'rN_' + orderNumber;
        // create a table row as a string
    var row_str = '';
    var newRow  = '';
    jtfneroptions.rows.forEach(($html)=>{
        $html = $html.replaceAll("\\'","'");
        $html = $html.replaceAll("\'+orderNumber+\'",orderNumber);
        $html = $html.replaceAll("+orderNumber+",orderNumber);
        row_str += $html;
        if ($html.indexOf($html, '<!-- end row -->') > 0) { 
        //convert string to table wrapped in a div element
            newRow = htmlToElements( row_str );
        // inject the new row into the table body
            el.appendChild(newRow);
            row_str = '';
        } 
    }) 
    if (row_str) {
            newRow = htmlToElements( row_str );
        // inject the new row into the table body
            el.appendChild(newRow);
    }
}
// function for switching between date types
function switch_datetype(orderNumber){
    var nwType =  document.getElementById('select_datetype_'+orderNumber).value; 
    var elL1   =  document.getElementById('ed_l1_'+orderNumber); // extended only
    var elD1   =  document.getElementById('ed_d1_'+orderNumber);  // simple + extended
    var elD2   =  document.getElementById('ed_d2_'+orderNumber);  // extended only
    var elDesc =  document.getElementById('ed_desc_'+orderNumber);// description only

    if (nwType == 'simple') { 
        if(isClassInElement(elL1,   'jt-show')) swapClassInElement(elL1,'jt-show','jt-hide'); 
        if(isClassInElement(elD2,   'jt-show')) swapClassInElement(elD2,'jt-show','jt-hide'); 
        if(isClassInElement(elDesc, 'jt-show')) swapClassInElement(elDesc,'jt-show','jt-hide'); 
        if(isClassInElement(elD1,   'jt-hide')) swapClassInElement(elD1,'jt-hide','jt-show'); 
    } else if (nwType == 'extended') { 
        if(isClassInElement(elDesc, 'jt-show')) swapClassInElement(elDesc,'jt-show','jt-hide'); 
        if(isClassInElement(elL1,   'jt-hide')) swapClassInElement(elL1,'jt-hide','jt-show'); 
        if(isClassInElement(elD1,   'jt-hide')) swapClassInElement(elD1,'jt-hide','jt-show'); 
        if(isClassInElement(elD2,   'jt-hide')) swapClassInElement(elD2,'jt-hide','jt-show'); 
    } else if (nwType == 'description') { 
        if(isClassInElement(elL1,   'jt-show')) swapClassInElement(elL1,'jt-show','jt-hide'); 
        if(isClassInElement(elD1,   'jt-show')) swapClassInElement(elD1,'jt-show','jt-hide'); 
        if(isClassInElement(elD2,   'jt-show')) swapClassInElement(elD2,'jt-show','jt-hide'); 
        if(isClassInElement(elDesc, 'jt-hide')) swapClassInElement(elDesc,'jt-hide','jt-show');;
    } 
}
