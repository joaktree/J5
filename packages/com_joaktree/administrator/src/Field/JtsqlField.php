<?php
/**
 * @package     MapsByJoaktree
 * @subpackage  Service
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
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
