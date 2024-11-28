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

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

class ApikeyRule extends FormRule
{
    private $errorMsg = '';

    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        if ($input->get('geocode') == 'Google' || $input->get('interactivemap') == 'Google' || $input->get('staticmap') == 'Google') {
            if (trim($value) == "") {
                $element->addAttribute('message', Text::_('MBS_GOOGLE_APIKEY_REQ'));
                return false;
            }
        }
        return $value;
    }

}
