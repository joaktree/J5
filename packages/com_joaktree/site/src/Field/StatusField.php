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
use Joomla\CMS\Form\FormField;		//replace JFormField

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class StatusField extends FormField		//JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Status';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$tmp 	= explode('!', $this->value);
		$id  	= 'stat_'.$tmp[0];
		$value 	= $tmp[1];
		
		return '<input type="hidden" name="'.$this->name.'" id="'.$id.'"' .
				' value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'"' .
				'/>';
	}
}
