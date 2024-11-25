/**
 * @package     Joaktree
 * @subpackage  article-joaktree-person
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 3 or later; see LICENSE
 *
 */
function jSelectPerson(id, title, appid, apptitle) {
	var tag = '{joaktree person|' + appid + '|' + id + '|content}';
    try {
        if (window['tinyMCE']) {
            window['tinyMCE'].execCommand('mceInsertContent',false,tag);
        }
        else if (window['WFEditor']) { // JCE
            WfEditor.insert('name', tag);
        }
        else if(window['CKEDITOR']){ // ArkEditor
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
