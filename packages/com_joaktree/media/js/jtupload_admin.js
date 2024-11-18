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
function jtUploadedFile(filename) {
	var name   = document.getElementById('jform_params_gedcomfile_name');
	name.value = filename; 
let myModalEl = document.getElementById('upload');
let modal = bootstrap.Modal.getInstance(myModalEl)
modal.hide()    
const modalBackdrops = document.getElementsByClassName('modal-backdrop');

//Remove opened modal backdrop
document.body.removeChild(modalBackdrops[0]);
	// document.querySelector('#upload').close();
}
