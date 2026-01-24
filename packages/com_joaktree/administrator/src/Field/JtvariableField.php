<?php
/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
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

defined('_JEXEC') or die;
use Joomla\CMS\Form\Field\TextField;

class JtvariableField extends TextField
{
    public $type = 'Jtvariable';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'jtvariable';

    /**
     * Unit
     *
     * @var    string
     */

    protected $unit = "";

    protected function getLayoutPaths()
    {
        $paths = parent::getLayoutPaths();
        $paths[] = JPATH_ADMINISTRATOR.'/components/com_joaktree/layouts';
        return $paths;

    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.2
     */
    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->collectLayoutData());
    }
    /**
     * Method to get the data to be passed to the layout for rendering.
     * The data is cached in memory.
     *
     * @return  array
     *
     * @since 5.1.0
     */
    protected function collectLayoutData(): array
    {
        if ($this->layoutData) {
            return $this->layoutData;
        }

        $this->layoutData = $this->getLayoutData();
        return $this->layoutData;
    }

}
