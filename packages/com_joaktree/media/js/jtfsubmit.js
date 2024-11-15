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
