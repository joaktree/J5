<?php
/**
 * Joomla! component Joaktree
 * file		person element - person.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */

namespace Joaktree\Component\Joaktree\Administrator\Field;

// no direct access
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\HTML\HTMLHelper;		//replace JHtml
use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\CMS\Form\FormField;		//replace JFormField
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class PersonField extends FormField	//JFormField
{
    protected $type = 'person';

    public function getInput()
    {
        $person = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Persons');

        // $person 		= Table::getInstance('PersonsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        if ($this->value) {
            $id = $this->checkValue($this->fieldname, $this->value);
            $person->set('app_id', $id[0]);
            $person->set('id', $id[1]);
            $person->load();

        } else {
            $person->firstName 	= Text::_('JTFIELD_PERSON_SELECTPERSON');
        }
        HtmlHelper::_('bootstrap.modal', '.modal', []);
        HTMLHelper::script(JoaktreeHelper::jsfile());
        $apps = JoaktreeHelper::getApplications();

        $linkTree   = 'index.php?option=com_joaktree&amp;view=trees&amp;layout=element&amp;task=element&amp;tmpl=component&amp;object='.$this->fieldname;
        $linkPerson = 'index.php?option=com_joaktree&amp;view=persons&amp;layout=element&amp;task=element&amp;tmpl=component&amp;object='.$this->fieldname;

        $html  = "\n".'<br /><br /><div style="clear: both;">';
        $html .= '<input style="background: #ffffff;" type="text" size="50" id="jform_personName" value="'.htmlspecialchars($person->firstName.' '.$person->familyName, ENT_QUOTES, 'UTF-8').'" disabled="disabled" title="'.Text::_('JTFIELD_PERSON_DESC_PERSON').'" />';

        $html .= '<select class="inputbox" id="jform_appTitle" name="apptitle" disabled="disabled">';
        $html .= HTMLHelper::_('select.options', $apps, 'value', 'text', $person->app_id);
        $html .= '</select>';
        $html .= '</div>';

        // buttons
        $html .= "\n".'<div style="clear: both; display:flex">';
        $html .= '<button type="button" class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#selectperson">'.Text::_('JTFIELD_PERSON_BUTTONDESC_PERSON').'</button>';
        $html .= ' <div class="modal fade modal-xl"  id="selectperson" tabindex="-1" aria-labelledby="selectperson" aria-hidden="true">
            <div class="modal-dialog h-75">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body h-100">
                        <iframe id="iframeModalWindow" height="100%" src="'.$linkPerson.'" name="iframe_modal"></iframe>      
                    </div>
                </div>
            </div>
        </div>';

        $html .= '<button type="button" class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#selecttree">'.Text::_('JTFIELD_PERSON_BUTTONDESC_TREE').'</button>';
        $html .= ' <div class="modal fade modal-xl"  id="selecttree" tabindex="-1" aria-labelledby="selecttree" aria-hidden="true">
            <div class="modal-dialog h-75">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body h-100">
                        <iframe id="iframeModalWindow" height="100%" src="'.$linkTree.'" name="iframe_modal"></iframe>      
                    </div>
                </div>
            </div>
        </div>';

        $html .= "\n".'<input type="hidden" id="jform_personId" name="'.$this->name.'" value="'.$this->value.'" />';

        return $html;
    }

    private function checkValue($name, $value)
    {
        static $initCharacters;
        $db	= Factory::getDBO();

        if ($name == 'personId') {
            $tmp = explode('!', $value);

            if (strlen($tmp[1]) > (int) JoaktreeHelper::getIdlength()) {
                die('wrong request');
            }

            $tmp[0] = (int) $tmp[0];
            $tmp[1] = $db->escape($tmp[1]);
            $retValue = $tmp;
        } else {
            $retValue = $db->escape($value);
            $retValue = (int) $retValue;
        }

        return $retValue;
    }

}
