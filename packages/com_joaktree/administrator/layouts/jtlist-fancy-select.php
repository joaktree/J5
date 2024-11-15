<?php

/**
 * Joomla! component Joaktree
 * Layout file : jtlist-fancy-select
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
/*
    fill up list box with all possible values if no value has been selected yet
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $options         Options available for this field.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*
 */

$html = [];
$attr = '';

// Initialize the field attributes.
$attr .= !empty($size) ? ' size="' . $size . '"' : '';
$attr .= $multiple ? ' multiple' : '';
$attr .= $autofocus ? ' autofocus' : '';
$attr .= $onchange ? ' onchange="' . $onchange . '"' : '';
$attr .= $dataAttribute;

// To avoid user's confusion, readonly="readonly" should imply disabled="disabled".
if ($readonly || $disabled) {
    $attr .= ' disabled="disabled"';
}

$attr2  = '';
$attr2 .= !empty($class) ? ' class="' . $class . '"' : '';
$attr2 .= ' placeholder="' . $this->escape($hint ?: Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS')) . '" ';

if ($required) {
    $attr  .= ' required class="required"';
    $attr2 .= ' required';
}
if (!$value) { // empty : add all values
    $value = [];
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $query = $db->getQuery(true);
    $query->select($db->qn('code'))
    ->from('#__joaktree_display_settings')
    ->where($db->qn('level'). ' = ' .$db->q('person'))
    ->where($db->qn('published'). '= true')
    ->where($db->qn('code'). ' NOT IN ("NOTE", "ENOT", "SOUR", "ESOU")')
    ->order('code ASC')
    ->union('SELECT code FROM #__joaktree_display_settings WHERE level = "relation" AND published = true AND code NOT IN ("ENOT", "ESOU") ORDER BY code ASC');
    $value[] = 'CREM';
    $value[] = 'BAPM';
    $db->setQuery($query);
    $results = $db->loadObjectList();
    foreach ($results as $result) {
        $value[] = $result->code;
    }
}
// Create a read-only list (no name) with hidden input(s) to store the value(s).
if ($readonly) {
    $html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $value, $id);

    // E.g. form field type tag sends $this->value as array
    if ($multiple && is_array($value)) {
        if (!count($value)) {
            $value[] = '';
        }

        foreach ($value as $val) {
            $html[] = '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($val, ENT_COMPAT, 'UTF-8') . '">';
        }
    } else {
        $html[] = '<input type="hidden" id="' . $id . '-value" name="' . $name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '">';
    }
} else { // Create a regular list.
    $html[] = HTMLHelper::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);
}

Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

Factory::getApplication()->getDocument()->getWebAssetManager()
    ->usePreset('choicesjs')
    ->useScript('webcomponent.field-fancy-select');

?>

<joomla-field-fancy-select <?php echo $attr2; ?>><?php echo implode($html); ?></joomla-field-fancy-select>
