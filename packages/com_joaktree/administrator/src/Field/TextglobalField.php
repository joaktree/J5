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

namespace Joaktree\Component\Joaktree\Administrator\Field;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class TextglobalField extends TextField	//JFormField
{
    protected $type = 'Textglobal';

    protected function getInput() {
        if ($this->element['useglobal']) {
            $component = Factory::getApplication()->getInput()->getCmd('option');

            // Get correct component for menu items
            if ($component === 'com_menus') {
                $link      = $this->form->getData()->get('link');
                $uri       = new Uri($link);
                $component = $uri->getVar('option', 'com_menus');
            }

            $params = ComponentHelper::getParams($component);
            $value  = $params->get($this->fieldname);

            // Try with global configuration
            if (\is_null($value)) {
                $value = Factory::getApplication()->get($this->fieldname);
            }

            // Try with menu configuration
            if (\is_null($value) && Factory::getApplication()->getInput()->getCmd('option') === 'com_menus') {
                $value = ComponentHelper::getParams('com_menus')->get($this->fieldname);
            }

            if (!\is_null($value)) {
                $value = (string) $value;

                $this->value = $value;
            }
        }

        return $this->getRenderer($this->layout)->render($this->collectLayoutData());
    }


}
