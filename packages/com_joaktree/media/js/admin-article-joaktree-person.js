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
function jSelectPerson(id, title, appid, apptitle) {
	var tag = '{joaktree person|' + appid + '|' + id + '|content}';
    try {
        if (window['tinyMCE']) {
            window['tinyMCE'].execCommand('mceInsertContent',false,tag);
        }
        if (window['WFEditor']) { // JCE
            WfEditor.insert('name', tag);
        }
        if(window['CKEDITOR']){ // ArkEditor
            for(var inst in CKEDITOR.instances)
               CKEDITOR.instances[inst].insertText(tag);
        }
        else {// codemirror / non wysiwyg
            for(var inst in Joomla.editors.instances)
               Joomla.editors.instances[inst].replaceSelection(tag);
        }
    } catch (e) {}
 document.querySelector('joomla-dialog').close();
}
