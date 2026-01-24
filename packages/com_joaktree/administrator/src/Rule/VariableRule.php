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

namespace Joaktree\Component\Joaktree\Administrator\Rule;
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

class VariableRule extends FormRule
{
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        // get showon to find field name
        $showon = (string)$element['showon'];
        $el = explode(':', $showon);
        $type = $el[0];
        $params = $input->get('params');
        if ($params->$type  == 'pick') { // color picker : exit
            return true;
        }
        if (!$value) {
            Factory::getApplication()->enqueueMessage(Text::_('JT_NOTEMPTY'), 'error');
            return false;
        }
        if (substr($value, 0, 2) != '--') {
            Factory::getApplication()->enqueueMessage(Text::_('JT_VARIABLE_START'), 'error');
            return false;
        }
        return true;

    }
}
