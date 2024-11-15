<?php
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

namespace Joaktree\Component\Joaktree\Site\Field;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class SourceField extends FormField		//JFormField
{
    protected $type = 'source';

    public function getInput()
    {
        // Initialize variables.
        $html = array();

        // Initialize some field attributes.
        $attr = $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
        $attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // Load the current record if available.
        //		$table = Table::getInstance('SourcesTable','Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $table	= Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Sources', '', array('dbo' => $db));

        if ($this->value) {
            $id = explode('!', $this->value);
            $appId = $id[0];

            if (count($id) == 2) {
                $table->set('app_id', $appId);
                $table->set('id', $id[1]);
                $table->load();
            }
        }

        // set up the magic between javascript and php
        if (isset($id[1])) {
            // php - for fields which are present while loading the form
            $iframe = '\'iframe\'';
            $counter = $this->form->getValue('counter');

            // Load the modal behavior script.
            HTMLHelper::_('bootstrap.modal', 'a.modal_src_'.$counter);
            //HTMLHelper::_('behavior.modal', 'a.modal_src_'.$counter);

            $link =  'index.php?option=com_joaktree'
                    .'&amp;view=sources'
                    .'&amp;tmpl=component'
                    .'&amp;appId='.$appId
                    .'&amp;action=select'
                    .'&amp;counter='.$counter;
        } else {
            // javascript - for fields while adding a new row to the form (after loading).
            $iframe  = '\\\'iframe\\\'';
            $counter = '\'+orderNumber+\'';
            $link =  'index.php?option=com_joaktree'
                    .'&amp;view=sources'
                    .'&amp;tmpl=component'
                    .'&amp;appId='.$appId
                    .'&amp;action=select';
        }

        // Create a dummy text field with the source title.
        $html[] = '<div >';
        $html[] = '	<input type="text" id="src_'.$counter.'_name"' .
                    ' value="'.htmlspecialchars($table->title ? $table->title : "", ENT_COMPAT, 'UTF-8').'"' .
                    ' disabled="disabled"'.$attr.' />';

        // Create the select and clear buttons.
        if ($this->element['readonly'] != 'true') {
            $html[] = '<div class="jt-clearfix"></div>';
            // empty label for layout
            $html[] = '<label>&nbsp;</label>';

            // button 1
            /*$html[] = '		<a id="modalid_'.$counter.'" class="modal_src_'.$counter.' jt-button-closed jt-buttonlabel" title="'.Text::_('JTSELECT').'"' .
                            ' href="'.$link.'"' .
                            ' rel="{handler: '.$iframe.', size: {x: 800, y: 500}}">';
            $html[] = '			'.Text::_('JTSELECT').'</a>';
*/
            HTMLHelper::_('bootstrap.modal', '.modal', []);
            $html[] = '<a class="jt-button-closed jt-buttonlabel" data-bs-toggle="modal" data-bs-target="#modalsrc_'.$counter.'_id">'.Text::_('JTSELECT').'</a>';
            $html[] = ' <div class="modal fade modal-xl"  id="modalsrc_'.$counter.'_id" tabindex="-1" aria-labelledby="modalsrc_'.$counter.'_id" aria-hidden="true">
            <div class="modal-dialog h-75 ">
                <div class="modal-content h-100 ">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body h-100 ">
                        <iframe id="iframeModalsrc_'.$counter.'_id" height="100%" src="'.$link.'" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
            </div>';




        }

        // Create the real field, hidden, that stored the user id.
        $html[] = ' <input type="hidden" '
                         .'id="src_'.$counter.'_id" '
                         .'name="'.$this->name.'" '
                         .'value="'.(isset($id[1]) ? $id[1] : null).'" '
                         .$attr
                    .' />';

        $html[] = '</div>';
        return implode("\n", $html);
    }

}
