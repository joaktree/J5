<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace Joaktree\Component\Joaktree\Site\Field;
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\Form\FormField;		//replace JFormField

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class MonthField extends FormField		//JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'month';

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

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();	

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
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JT_EMPTY'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'JAN', Text::_('January'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'FEB', Text::_('February'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'MAR', Text::_('March'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'APR', Text::_('April'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'MAY', Text::_('May'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'JUN', Text::_('June'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'JUL', Text::_('July'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'AUG', Text::_('August'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'SEP', Text::_('September'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'OCT', Text::_('October'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'NOV', Text::_('November'), 'value', 'text', false);
		$options[] = HTMLHelper::_('select.option', 'DEC', Text::_('December'), 'value', 'text', false);
		
		reset($options);
		return $options;
	}
}
