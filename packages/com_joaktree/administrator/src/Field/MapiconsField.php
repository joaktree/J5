<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace Joaktree\Component\Joaktree\Administrator\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;		//replace JFormField

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class MapiconsField extends FormField	//JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Mapicons';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html 	= array();
		$script = array();
		
		$script[] = "<script type=\"text/javascript\">";
		$script[] = "function jt_mapicons_toggle() { ";
		$script[] = "  var El = document.querySelector('#".$this->id."'); ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-0')) { El.classList.remove('jt-map-sprite-0'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-1')) { El.classList.remove('jt-map-sprite-1'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-2')) { El.classList.remove('jt-map-sprite-2'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-3')) { El.classList.remove('jt-map-sprite-3'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-4')) { El.classList.remove('jt-map-sprite-4'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-5')) { El.classList.remove('jt-map-sprite-5'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-6')) { El.classList.remove('jt-map-sprite-6'); } ";
		$script[] = "  if (El.classList.contains('jt-map-sprite-7')) { El.classList.remove('jt-map-sprite-7'); } ";
		$script[] = "  El.classList.add('jt-map-sprite-' + El.value); ";

		$script[] = "} ";
		$script[] = "</script>";
		$script[] = "";
		
		$class  = 'jt-map-sprite-'.(int) $this->value;
		$class .= ($this->element['class'] ? (string) ' '.$this->element['class'] : '');

		// Get the field options.
		$html[] = '<select '
				 .($this->element['size'] ? ' size="' . (int) $this->element['size'] . '" ' : '')
				 .'class="'.$class.'" '
				 .'name="'.$this->name.'" '
				 .'id="'.$this->id.'" '
				 .'style="height: 32px;" '
				 .'onchange="jt_mapicons_toggle();" >';
				 
		for ($i=0; $i<8; $i++) {
			$html[] = '<option '
					 .'value="'.$i.'" ' 
					 .(((int) $this->value == $i) ? 'selected="selected" ': '').' '
					 .'class="jt-map-sprite-'.$i.'" '
					 .'>'.$i.'</option>';
		}
		
		$html[] = '</select>';		

		return implode("\n", array_merge($script, $html));
	}
}
