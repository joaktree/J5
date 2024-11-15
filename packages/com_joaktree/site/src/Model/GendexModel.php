<?php
/**
 * Joomla! component Joaktree
 * file		gendex model - gendex.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */
namespace Joaktree\Component\Joaktree\Site\Model;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel ; 	//replace JModelLegacy
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class GendexModel extends BaseDatabaseModel { 
		
	function __construct() {
		parent::__construct();		
	}
			
	public function getItems() {
		// information is only selected for level: public
		$public = true;		

		$userAccessLevels = '(1)';
		$displayAccess    = JoaktreeHelper::getDisplayAccess($public);
						
		// retrieve persons
		$db				=$this->_db;
		$query			= $db->getquery(true);
		
		// select the basics
		$query->select(' jpn.app_id ');
		$query->select(' jpn.id ');
		$query->select(JoaktreeHelper::getSelectFirstName().' AS firstName ');
		$query->select(JoaktreeHelper::getConcatenatedFamilyName().' AS familyName ');
		$query->from(  ' #__joaktree_persons  jpn ');
		
		// privacy filter
		$query->select(' jan.default_tree_id  AS treeId ');
		$query->innerJoin(JoaktreeHelper::getJoinAdminPersons(false));
		$query->innerJoin(' #__joaktree_trees  jte '
						 .' ON (   jte.app_id    = jan.app_id '
						 .'    AND jte.id        = jan.default_tree_id '
						 .'    AND jte.published = true '
						 .'    AND jte.access    IN '.$userAccessLevels.' '
						 // only trees with Gendex = yes (=2)
						 .'    AND jte.indGendex = 2 '
						 .'    ) '
						 );
		
		// birth info
		$query->select(' birth.eventDate  AS birthDate ');
		$query->select(' birth.location   AS birthPlace ');
		$query->leftJoin(' #__joaktree_person_events  birth '
						.' ON (   birth.app_id    = jpn.app_id '
						.'    AND birth.person_id = jpn.id '
						.'    AND birth.code      = '.$this->_db->Quote('BIRT').' '
						// no alternative text is shown 
						.'    AND (  (jan.living = false AND '.$displayAccess['BIRTperson']->notLiving.' = 2 ) '
						.'        OR (jan.living = true  AND '.$displayAccess['BIRTperson']->living.'    = 2 ) '
						.'        ) '
						.'    ) '
						);
		
		// death info
		$query->select(' death.eventDate  AS deathDate ');
		$query->select(' death.location   AS deathPlace ');
		$query->leftJoin(' #__joaktree_person_events  death '
						.' ON (   death.app_id    = jpn.app_id '
						.'    AND death.person_id = jpn.id '
						.'    AND death.code = '.$this->_db->Quote('DEAT').' '
						// no alternative text is shown 
						.'    AND (  (jan.living = false AND '.$displayAccess['DEATperson']->notLiving.' = 2 ) '
						.'        OR (jan.living = true  AND '.$displayAccess['DEATperson']->living.'    = 2 ) '
						.'        ) '
						.'    ) '
						);

        $this->_db->setquery($query);
		$result = $this->_db->loadObjectList();
		
		return $result;
	}
}
?>
