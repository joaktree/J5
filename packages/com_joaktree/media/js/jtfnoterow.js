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
var jtfnroptions;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtfnroptions = Joomla.getOptions('jtfnoterow');
	if (typeof jtfnroptions === 'undefined' ) {return false}
})
function inject_notrow(table_body, table_row, appid, obj_type, obj_number){
    var htmlToElements = function(str){
        var objhtml = { html:'<table><tbody>' + str + '</tbody></table>' };
        let block = document.createElement('div');
        block.innerHTML = objhtml.html;
        return block;
    }
    var el = document.getElementById(table_body);
    var orderNumber = parseInt(document.getElementById('notcounter').value) + 1;
    document.getElementById('notcounter').value = orderNumber;
    var rownot = table_row + '_n_' + orderNumber;
    // create a table row as a string
    var row_str = '';

    jtfnroptions.rows.forEach(($html)=>{
        $html = $html.replaceAll("\\'","'");
        $html = $html.replaceAll("'+orderNumber+'",orderNumber);
        $html = $html.replaceAll("+orderNumber+",orderNumber);
        $html = $html.replaceAll("'+rownot+'",rownot);
        $html = $html.replaceAll("+rownot+",rownot);
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