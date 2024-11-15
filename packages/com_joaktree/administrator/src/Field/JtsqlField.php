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

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Field\SqlField;

class JtsqlField extends SqlField
{
    public $type = 'Jtsql';

    protected function getInput()
    {
        $data = $this->collectLayoutData();

        $data['options'] = (array) $this->getOptions();

        $this->layout = 'jtlist-fancy-select';

        return $this->getRenderer($this->layout)->render($data);
    }

}
