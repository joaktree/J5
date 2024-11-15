/**
 * @package     Joaktree
 * @subpackage  jtfsubmit
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 */
 var jtfsubmitoptions;
window.addEventListener('DOMContentLoaded',function(){
    
  	jtfsubmitoptions = Joomla.getOptions('jtfsubmit');
	if (typeof jtfsubmitoptions === 'undefined' ) {return false}
})

function jtsetrelation(id) { 
    document.getElementById('jform_person_relation_id').value = id; 
} 
function jtsubmitbutton(task, object) { 
    f = document.getElementById('joaktreeForm'); 
    f.object.value = object;  
    if (task == 'delete') { 
        if (confirm(jtfsubmitoptions.message)) { 
            Joomla.submitform(task, f); 
        } 
    } else { 
        Joomla.submitform(task, f); 
    } 
} 
