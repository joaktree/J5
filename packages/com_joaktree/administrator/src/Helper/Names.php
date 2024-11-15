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
namespace Joaktree\Component\Joaktree\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;		//replace JFactory
use Joomla\Database\DatabaseInterface;

class Names extends \StdClass {
	/*
	** Function to find for every person in table his/her patronym from relation table.
	*/
	public static function setPatronyms($appId) {
		$ret 	= true;
		$appId	= (int) $appId;
		$db     = Factory::getContainer()->get(DatabaseInterface::class);
		
		// set up query for selecting person_ids and father's first name
		$query = 'UPDATE     #__joaktree_persons           jpn0 '
				.',          ( SELECT    jpn.app_id     AS app_id '
				.'           ,           jpn.id         AS id '
				.'           ,	         jpn2.firstName AS patronym '
				.'           FROM        #__joaktree_persons   jpn '
				.'           LEFT JOIN	 #__joaktree_relations jrn '
				.'           ON		     (   jrn.app_id       = jpn.app_id '
				.'                       AND jrn.person_id_1  = jpn.id '
				.'		                 AND jrn.type 		 = ' .  $db->Quote( 'father' ) . ' '
				.'		                 AND jrn.person_id_2  = '
				.'                           ( SELECT MIN( jrn2.person_id_2 ) '
				.'                             FROM	  #__joaktree_relations jrn2 '
				.'                             WHERE  jrn2.app_id      = jrn.app_id '
				.'                             AND    jrn2.person_id_1 = jrn.person_id_1 '
				.'                             AND    jrn2.type = '.  $db->Quote( 'father' ) . ' '
				.'                           ) '
				.'                       ) '
				.'           LEFT JOIN	 #__joaktree_persons	jpn2 '
				.'           ON		     (   jpn2.app_id        = jrn.app_id '
				.'                       AND jpn2.id            = jrn.person_id_2 '
				.'                       ) '
				.'           WHERE      jpn.app_id = '.$appId.' '
				.'           )          jpn_iv '
				.'SET        jpn0.patronym = jpn_iv.patronym '
				.'WHERE      jpn0.app_id   = '.$appId.' '
				.'AND        jpn0.id       = jpn_iv.id ';
				
		$db->setQuery( $query );
		
		//$ret = $db->query();
		$ret = $db->execute();
		
		return $ret;		
	}
	
	
	/*
	** Function to extract prepositions (dutch) from family name
	*/
	public static function setFamilyName( &$inputName, &$persons, &$familynameSetting) {		
		$ret = true;
		$nameElement  = explode(" ", $inputName);
		$num_prepos = 0;
		
		if ($familynameSetting == 1) {
			// names are separated into two parts
			switch ( strtoupper($nameElement[0]) ) {
				case "IN":	// do nothing
				case "OP":	// do nothing
				case "TOT":	// do nothing
				case "UIJT":	// do nothing
				case "UIT":	// do nothing
				case "UYT":	// do nothing
				case "VAN":	// first part is Dutch preposition - check for second part
						switch ( strtoupper($nameElement[1]) ) {
							case "DE":	// do nothing
							case "DER":	// do nothing
							case "DEN":	// do nothing
							case "HET":	// do nothing
							case "'T":	// do nothing
							case "TE":	// do nothing
							case "TER":	// do nothing
							case "TEN":	// do nothing
							case "EEN":	// second part has a Dutch particle
									$num_prepos = 2;
									BREAK;
							default:	// no particle - just a preposition
									$num_prepos = 1;
									BREAK;
						}
						BREAK;
				// no preposition check for Dutch particles
				case "DE":	// do nothing
				case "DER":	// do nothing
				case "DEN":	// do nothing
				case "HET":	// do nothing
				case "'T":	// do nothing
				case "TE":	// do nothing
				case "TER":	// do nothing
				case "TEN":	// do nothing
				case "EEN":	// second part has a Dutch particle
						$num_prepos = 1;
						BREAK;
				default:	// nothing - just a family name
						$num_prepos = 0;
						BREAK;
			}
			
			if ($num_prepos == 1) {
				// namePreposition exists of 1 element
				$persons->set( 'namePreposition', $nameElement[0]);
				
				// remove the one element from the array of name elements
				$tmp = array_shift($nameElement);
				
				// family name is constructed from the remaining elements and saved
				$familyName        = implode(" ", $nameElement);
				$persons->set( 'familyName', $familyName);
			} else if ($num_prepos == 2) {
				// namePreposition exists of 2 element
				$persons->set( 'namePreposition', $nameElement[0] .' '. $nameElement[1]);
				
				// remove the two elements from the array of name elements
				$tmp = array_shift($nameElement);
				$tmp = array_shift($nameElement);
				
				// family name is constructed from the remaining elements and saved
				$familyName        = implode(" ", $nameElement);
				$persons->set( 'familyName', $familyName);
			} else {
				// no preposition - family name is saved unchanged
				$persons->set( 'namePreposition', null);
				$persons->set( 'familyName', $inputName);
			}
		} else {
			// names are left unchanged
			$persons->set( 'namePreposition', null);
			$persons->set( 'familyName', $inputName);
		}
			
		return $ret;
	}
}
?>
