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

        $model = $this->getModel();

        // what is the layout
        $layout = $model->getLayout();

        // Person
        $this->person			= $model->getPerson();
        $this->lists['technology']	= $model->getTechnology();

        if (($layout == '_detailnotes') or ($layout == '_mainnotes')) {
            $notes[ 'type' ] = 		$model->getJtType();
            $notes[ 'subtype' ] = 		$model->getJtSubType();
            $notes[ 'orderNumber' ] = 	$model->getOrderNumber();
            $notes[ 'relation_id' ] = 	$model->getRelationId();

            //$this->assignRef( 'notes',	$notes );
            $this->notes = $notes;
        }

        if (($layout == '_detailsources') or ($layout == '_mainsources')) {
            $sources[ 'type' ] = 		$model->getJtType();
            $sources[ 'subtype' ] = 	$model->getJtSubType();
            $sources[ 'orderNumber' ] = 	$model->getOrderNumber();
            $sources[ 'relation_id' ] = 	$model->getRelationId();

            //$this->assignRef( 'sources',	$sources );
            $this->sources = $sources;
        }

        if ($layout == '_article') {
            $notes[ 'type' ] 		= $model->getJtType();
            $notes[ 'orderNumber' ] = $model->getOrderNumber();
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
