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

defined('_JEXEC') or die;
use Joomla\CMS\Form\FormField;		//replace JFormField

class JtstatusField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Jtstatus';

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
