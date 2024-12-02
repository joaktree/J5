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
    if (sel) sel.classList.remove('form-grid');

    sels = document.querySelectorAll('.clear');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.clear = 'both';
        element.parentNode.parentNode.style.clear = 'both';
    });
    sels = document.querySelectorAll('input.left');
    sels.forEach(function (element) {
        element.parentNode.parentNode.style.float = 'left';
    });
    sels = document.querySelectorAll('div.left');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.float = 'left';
        element.parentNode.parentNode.style.float = 'left';
    });
    sels = document.querySelectorAll('input.right');
    sels.forEach(function (element) {
        element.parentNode.parentNode.style.float = 'right';
    });

    sels = document.querySelectorAll('div.right');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.float = 'right';
        element.parentNode.parentNode.style.float = 'right';
    });
    
    sels = document.querySelectorAll('input.half');
    sels.forEach(function (element) {
        element.parentNode.parentNode.style.width = '50%';
        element.parentNode.style.width = '50%';
    });
    sels = document.querySelectorAll('div.half');
    sels.forEach(function (element) {
        element.parentNode.parentNode.parentNode.style.width = '50%';
        element.parentNode.parentNode.style.width = '50%';
        element.parentNode.style.width = '50%';
    });
    
    
});
