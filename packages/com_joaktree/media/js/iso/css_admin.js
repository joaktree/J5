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
document.addEventListener('DOMContentLoaded', function() {

    sel = document.querySelector('#fieldset-basic');
    sel = sel.querySelector('.form-grid');
    if (!sel) { return; }
    sel.classList.remove('form-grid');
    sels = sel.querySelectorAll('.control-group .control-label');
    sels.forEach(function (element) {
        element.style.width = '140px';
    });   
    sels = sel.querySelectorAll('.clear');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.clear = 'both';
        element.parentNode.parentNode.style.clear = 'both';
    });
    sels = sel.querySelectorAll('input.left');
    sels.forEach(function (element) {
        element.parentNode.parentNode.style.float = 'left';
    });
    sels = sel.querySelectorAll('div.left');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.float = 'left';
        element.parentNode.parentNode.style.float = 'left';
    });
    sels = sel.querySelectorAll('input.right');
    sels.forEach(function (element) {
        element.parentNode.parentNode.style.float = 'right';
    });

    sels = sel.querySelectorAll('div.right');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.float = 'right';
        element.parentNode.parentNode.style.float = 'right';
    });
    
    sels = sel.querySelectorAll('div.half.radio');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.width = '50%';
        element.parentNode.parentNode.style.width = '50%';
    });
    sels = sel.querySelectorAll('input.half');
    sels.forEach(function (element) {
        element.parentNode.parentNode.style.width = '50%';
        element.parentNode.style.width = '50%';
    });
    sels = sel.querySelectorAll('div.half:not(.radio)');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.width = '50%';
        element.parentNode.parentNode.style.width = '50%';
        element.parentNode.style.width = '50%';
    });
    
    
});
