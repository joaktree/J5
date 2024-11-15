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

namespace Joaktree\Component\Joaktree\Site\View\Joaktree;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML View class for the Joaktree component
 */
class RawView extends BaseHtmlView
{
    public function display($tpl = null)
    {

        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        // what is the layout
        $layout = $this->get('layout');

        // Person
        $this->person			= $this->get('person');
        $this->lists['technology']	= $this->get('technology');

        if (($layout == '_detailnotes') or ($layout == '_mainnotes')) {
            $notes[ 'type' ] = 		$this->get('jttype');
            $notes[ 'subtype' ] = 		$this->get('jtsubtype');
            $notes[ 'orderNumber' ] = 	$this->get('orderNumber');
            $notes[ 'relation_id' ] = 	$this->get('relationId');

            //$this->assignRef( 'notes',	$notes );
            $this->notes = $notes;
        }

        if (($layout == '_detailsources') or ($layout == '_mainsources')) {
            $sources[ 'type' ] = 		$this->get('jttype');
            $sources[ 'subtype' ] = 	$this->get('jtsubtype');
            $sources[ 'orderNumber' ] = 	$this->get('orderNumber');
            $sources[ 'relation_id' ] = 	$this->get('relationId');

            //$this->assignRef( 'sources',	$sources );
            $this->sources = $sources;
        }

        if ($layout == '_article') {
            $notes[ 'type' ] 		= $this->get('jttype');
            $notes[ 'orderNumber' ] = $this->get('orderNumber');
            $notes[ 'app_id' ] 		= $this->person->app_id;
            $notes[ 'person_id' ] 	= $this->person->id;

            //$this->assignRef( 'notes',	$notes );
            $this->notes = $notes;
        }

        $canDo		 	= null;
        $this->canDo = $canDo;
        parent::display($tpl);
    }
}
