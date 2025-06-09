<?php
/**
 * Joomla! component Joaktree
 *
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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;	//replace JFormFieldList
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class GedcomlistField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    public $type = 'GedcomList';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     * @since   11.1
     */
    protected function getInput()
    {
        // Initialize variables.
        $html = array();
        $attr = '';
        $gedcomtype = $this->element['gedcom'];
        $indLiving = $this->form->getValue('living', 'person');
        if (!isset($indLiving)) {
            $indLiving = 1;
        }

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
            $attr .= ' disabled="disabled"';
        }

        $attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

        // Get the field options.
        $options = (array) self::getGedOptions($gedcomtype, $indLiving);

        // Create a read-only list (no name) with a hidden input to store the value.
        if ((string) $this->element['readonly'] == 'true') {
            $html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
            $html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
        }
        // Create a regular list.
        else {
            $html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
        }

        return implode($html);
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     * @since   11.1
     */
    protected function getGedOptions($gedcomtype, $indLiving)
    {
        // Possible gedcom-types are: person, name, relation

        // Initialize variables.
        $options = array();
        $levels  = JoaktreeHelper::getUserAccessLevels();

        // Get the database object.
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getquery(true);
        $query->select(' code ');
        $query->from(' #__joaktree_display_settings ');
        $query->where(' level = :level');
        $query->where(' published = true ');
        $query->where(' code NOT IN ('
                            .$db->quote('NAME').', '
                            .$db->quote('NOTE').', '
                            .$db->quote('ENOT').', '
                            .$db->quote('SOUR').', '
                            .$db->quote('ESOU')
                            .') ');
        if ($indLiving == true) {
            $query->where(' accessLiving IN '.$levels.' ');
        } else {
            $query->where(' access IN '.$levels.' ');
        }
        $level = $db->escape($gedcomtype);
        $query->bind(':level', $level, \Joomla\Database\ParameterType::STRING);
        // Set the query and get the result list.
        $db->setquery($query);
        try {
            $items = $db->loadObjectlist();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'ERROR');
            return $options;
        }

        // Build the field options.
        if (!empty($items)) {
            foreach ($items as $item) {
                $options[] = HTMLHelper::_('select.option', $item->code, Text::_($item->code));
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
