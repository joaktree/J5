/**
 * @package     MapsByJoaktree
 * @subpackage  jtlocations
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 */
var jtlsptions;
window.addEventListener('DOMContentLoaded',function(){
  	jtlsptions = Joomla.getOptions('joaktreestart');
	if (typeof jtlsptions === 'undefined' ) {return false}

    const btn_alpha = document.querySelectorAll('.jt-content-accordion'); 
    btn_alpha.forEach((item)=>{ 
        item.addEventListener('click', function(){ 
            let btnSource = item.getAttribute('btn_a_index');  
            loadData(btnSource);
        });  
    });
})

function loadData(Source) {
    if ((typeof Source === 'undefined')|| (typeof Source !== 'string')){
        return 0; 
    }
    try { 
        var selSource = Source;
        var selDel = false;
    } catch (e) { 
        var selSource = '0-jt-cnt' ;
        var selDel = true;
    }
    const jtDest = document.querySelectorAll('#jt-accordion div.jt-showr');  
    jtDest.forEach((elem)=>{ 
        elem.classList.remove('jt-showr');
        elem.classList.add('jt-ajax');
    })
    var jtElement = document.getElementById( selSource );
    if (jtElement && jtElement.classList.contains('jt-ajax')) {
        jtElement.classList.remove('jt-ajax');
        jtElement.classList.add('jt-showr');
        var index = jtElement.id;
        var myRequest = new XMLHttpRequest();
        myRequest.open('GET', 'index.php?option=com_joaktree&format=raw&tmpl=component&view=joaktreestart&layout=_names&treeId='+jtlsptions.lists['tree_id']+'&filter=' + index.replace('-jt-cnt', ''), true);
        url: 'index.php?option=com_joaktree&format=raw&tmpl=component&view=joaktreestart&layout=_names&treeId='+jtlsptions.lists["tree_id"]+'&filter='+index.replace('-jt-cnt', ''), 
        myRequest.onerror = function() {
            alert('Error occured for url: ' + url); 
            }
        myRequest.onload = function() {
            if (myRequest.status >= 200 && myRequest.status < 400) {
                HandleResponseInd(index, myRequest.responseText);
            } else {
                alert('Error occured for url: ' + url); 
            };
        };
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
function HandleResponseInd(id, response) {
    var El = document.getElementById(id);
    El.innerHTML = response;
    loadData();
}
