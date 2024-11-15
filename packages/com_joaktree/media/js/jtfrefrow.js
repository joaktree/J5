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

var jtfroptions;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtfroptions = Joomla.getOptions('jtfrefrow');
	if (typeof jtfroptions === 'undefined' ) {return false}
})
function jtSelectSource(idid, titleid, id, title) {
    var old_id = document.getElementById(idid).value;
    if (old_id != id) {
        document.getElementById(idid).value = id;
        document.getElementById(titleid).value = title;
    }
    console.log('modal close : '+'modal'+idid);
    if (document.querySelector('#modal'+idid)) {
        document.querySelector('#modal'+idid).style.display = "none";
        document.querySelector('.modal-backdrop').remove();        
        // document.querySelector('#modal'+idid).close();
    }
}
function inject_refrow(table_body, table_row, appid, obj_type, obj_number){
    var htmlToElements = function(str){
        var objhtml = { html:'<table><tbody>' + str + '</tbody></table>' };
        let block = document.createElement('div');
        block.innerHTML = objhtml.html;
        return block;
    }
    var el = document.getElementById(table_body);
    var orderNumber = parseInt(document.getElementById('refcounter').value) + 1;
    document.getElementById('refcounter').value = orderNumber;
    var rowref = table_row + '_r_' + orderNumber;
    var rownot = table_row + '_n_' + orderNumber;
    // create a table row as a string
    var row_str = '<tr id="'+rowref+'" class="jt-table-entry3">';
    jtfroptions.rows.forEach(($html)=>{
        $html = $html.replaceAll("\\'","'");
        $html = $html.replaceAll("\'+orderNumber+\'",orderNumber);
        $html = $html.replaceAll("+orderNumber+",orderNumber);
        $html = $html.replaceAll("\'+obj_type+\'",obj_type);
        $html = $html.replaceAll("\'+obj_number+\'",obj_number);
        $html = $html.replaceAll("\'+rowref+\'",rowref);
        $html = $html.replaceAll("+rowref+",rowref);
        $html = $html.replaceAll("'+rownot+'",rownot);
        $html = $html.replaceAll("+rownot+",rownot);

        row_str += $html;
    })
    row_str += '</tr>';
    // convert string to table wrapped in a div element
    var newRow = htmlToElements( row_str );
    // inject the new row into the table body
    el.appendChild(newRow);
    // setup modal
    // SqueezeBox.assign($$('a.modal_src_'+orderNumber), { parse: 'rel' });
    var url=document.getElementById('iframeModalsrc_'+orderNumber+'_id').src;
    //if (jtfroptions.sef = 1) {
    //    document.getElementById('iframeModal'+orderNumber).src = url+'?counter='+orderNumber;
    //} else {
        document.getElementById('iframeModalsrc_'+orderNumber+'_id').src = url+'&counter='+orderNumber;
    //}
}
