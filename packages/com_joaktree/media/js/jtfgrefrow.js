/**
 * @package     Joaktree
 * @subpackage  jtfgenrefrow
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 */
var jtfgroptions;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtfgroptions = Joomla.getOptions('jtfgrefrow');
	if (typeof jtfgroptions === 'undefined' ) {return false}
}) 
function remove_row(table_row){
    var row, stat, i, el, elements; 
    // set status
    stat = document.getElementById('stat_' + table_row).value;
    document.getElementById('stat_' + table_row).value = stat+'_deleted';
    // remove main row by hiding it + setting all input element: not required
    row = document.getElementById(table_row);
    row.classList.add('jt-hide');
    elements = row.getElementsByTagName('input');
    for (i=0; i < elements.length; i++ ) {
        el = elements[i];
        if (el.classList.contains('required')) {
            el.classList.remove('required');
        }
    }
    // remove ref row by hiding it + setting all input element: not required
    row = document.getElementById(table_row+'_ref');
    if (row != null) {
        row.classList.add('jt-hide');
        elements = row.getElementsByTagName('input');
        for (i=0; i < elements.length; i++ ) {
            el = elements[i];
            if (el.classList.contains('required')) {
                el.classList.remove('required');
            }
        }
    }
    // remove not row by hiding it + setting all input element: not required
    row = document.getElementById(table_row+'_not');
    if (row != null) {
        row.classList.add('jt-hide');
        elements = row.getElementsByTagName('input')
        for (i=0; i < elements.length; i++ ) {
            el = elements[i];
            if (el.classList.contains('required')) {
                el.classList.remove('required');
            }
        }
    }
}

function move_row(table_row, direction){
    var clicked = document.getElementById(table_row);
    var table   = clicked.parentNode;
    var clickedIndex = clicked.rowIndex;
    var maxrindex = table.getElementsByTagName('tr').length;
    if(clickedIndex == '1' && direction=='up') {
        alert(jtfgroptions.UPPERROW_MESSAGE); return false; }
    if(clickedIndex == maxrindex && direction=='down') {
        alert(jtfgroptions.LOWERROW_MESSAGE); return false; }

    if (direction=='up')   { adjacentIndex = clickedIndex - 1; }
    if (direction=='down') { adjacentIndex = clickedIndex + 1; }
    var adjacnt = table.getElementsByTagName('tr')[adjacentIndex-1];
    //Once that we have established references to both the rows that should change their position, we should clone each of them
    click_clone = clicked.cloneNode(true);
    adjac_clone = adjacnt.cloneNode(true);
    //both the cloned nodes remain ‘invisible’ to the user.
    //now replace the nodes.
   //The below replaceChild() function will replace the adjacentrow with ‘clone of the clicked row’ and then remove the clone on the clicked row.
    table.replaceChild(adjac_clone, clicked);
    table.replaceChild(click_clone, adjacnt);
    // the clones of two rows that we made above were automatically removed by the replaceChild() function.
}
