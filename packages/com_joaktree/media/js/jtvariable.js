/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud 
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */
/* handle jt Variable field */
document.addEventListener('DOMContentLoaded', function() {
    let cgvariables = document.querySelectorAll('.form-jtvariable');
    for(var i=0; i< cgvariables.length; i++) {
        // init 
        bsvar = cgvariables[i].value;
        id = cgvariables[i].getAttribute('id');
        value = cgvariables[i].value;
        check_color(id,value);
        // add listener
        cgvariables[i].addEventListener('input',function() {
            let id = this.getAttribute('id');
            value = this.value;
            check_color(id,value);
        })
    }

})
function check_color(id,value) {
    let jtcolors = document.querySelectorAll('.'+id+'_color');
    let light = document.getElementById(id+'_light');
    let dark = document.getElementById(id+'_dark');
    light.style.backgroundColor = '';
    dark.style.backgroundColor = '';
    bsvar = value;
    let root = document.documentElement;
    let color =  getComputedStyle(root).getPropertyValue(bsvar);
    let bsvar_cassio = bsvar.replace('--bs-','--');
    let color_cassio = getComputedStyle(root).getPropertyValue(bsvar_cassio);
    if (!color && !color_cassio) {
        for(var j=0; j< jtcolors.length; j++) {
            jtcolors[j].style.display = "none";
        }
        return;
    }
    for(var j=0; j< jtcolors.length; j++) {
        jtcolors[j].style.display = "inline-block";
    }
    if (color) {
        light.style.backgroundColor = 'var('+bsvar+')';
        dark.style.backgroundColor = 'var('+bsvar+')';
    } else if (color_cassio) {
        light.style.backgroundColor = 'var('+bsvar_cassio+')';
        dark.style.backgroundColor = 'var('+bsvar_cassio+')';
    }
}