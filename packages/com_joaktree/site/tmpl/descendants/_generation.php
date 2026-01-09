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
defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Person;

$linkbase = 'index.php?option=com_joaktree&view=joaktree'
				.'&tech='.$this->lists['technology']
				.'&Itemid='.$this->person->menuItemId
				.'&treeId='.$this->lists['treeId']
				.'&personId=';
$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';			
				
$startGenNum 	= $this->lists[ 'startGenNum' ];
$endGenNum		= $this->lists[ 'endGenNum' ];
$personIdArray	= $this->personId;
$id				= array();
$id[ 'app_id' ] = $this->lists[ 'app_id' ];

$html = '';
$generationNumber = $startGenNum;
$thisGeneration = array();
$nextGeneration = array();

foreach ($personIdArray as $personId) {
	$thisGeneration[] = $personId;
}

$continue = true;		
while ($continue == true) {
	$displayGenerationNum = JoaktreeHelper::displayEnglishCounter($generationNumber);
	$displayThisGenNumber = JoaktreeHelper::arabicToRomanNumeral($generationNumber);
	$displayNextGenNumber = JoaktreeHelper::arabicToRomanNumeral($generationNumber+1);
	
	$nextGenerationCounter = 0;
	$html .= '<div class="jt-clearfix"><div class="jt-h3">'.Text::_($displayGenerationNum).'&nbsp;'.Text::_('JT_GENERATION').'</div></div>';
	$person_displayed = [];
	foreach ($thisGeneration as $gen_i => $generation) {
		$genPerson = explode('|', $generation);
		
		$id[ 'person_id' ] 	= $genPerson[0] ;		
		$person	    		= new Person($id, 'basic');		
		if (isset($genPerson[3])) {
			// This is relationtype
			$person->relationtype = $genPerson[3];
		}			
		
		$html .= '<div class="jt-clearfix jt-high-row">';
		$html .= '<a name="P'. $person->id .'"></a>';
		$person_displayed[$person->id] = $person->fullName; // save it 
		if ( !(($generationNumber == 1) and ($genPerson[1] == 1)) ) {
			$html .= '<a href="#P' . $genPerson[2] . '">';			
		} 
		$html .= '<span class="jt-desc-num-label">';
		$html .= $displayThisGenNumber.'-'.$genPerson[1];
		$html .= '</span>';
		if ( !(($generationNumber == 1) and ($genPerson[1] == 1)) ) {
			$html .= '</a>';
		} 
		
		if ( (!(($generationNumber == 1) and ($genPerson[1] == 1))) and $person->indHasPage ) {
			$html .= '<a href="' . Route::_($linkbase.$this->lists[ 'app_id' ].'!'.$person->id) . '" '.$robot.' >';
		}			
		$html .= $person->fullName;
		if ( (!(($generationNumber == 1) and ($genPerson[1] == 1))) and $person->indHasPage ) {
			$html .= '</a>';
		}
		if ($person->relationtype) {
			// show type of relation
			$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($person->relationtype));
		}
		
		
		if ($person->birthDate != null) {
			$html .= ',&nbsp;';
			if ($person->birthDate != Text::_('JT_ALTERNATIVE') ) {
				$html .= Text::_('JT_BORN').'&nbsp;';
			}
			$html .= $person->birthDate;
		}
		if ($person->deathDate != null) {
			$html .= ',&nbsp;'.Text::_('JT_DIED').'&nbsp;'.$person->deathDate;
		}
		
		$html .= '</div>';
		
		$children	= $person->getChildren('basic');		
		$partners	= $person->getPartners('basic');
			
		// when there are no children , skip this routine
		if (count($children) > 0 ) {
			// check for children with only one parent
			$counter_1 = 0;
			foreach ($children as $child) {
				if ( $child->secondParent_id == null ) {
					$counter_1++;
				}
			}
			
			if ($counter_1 > 0) {
				// children with only one parent
				$html .= '<div><span class="jt-desc-num-label">&nbsp;</span><em>' . Text::_('JT_CHILDREN') . '</em></div>';
				
				// loop through the children and filter on the one parent children
				foreach ($children as $child) {
					if ( $child->secondParent_id == null ) {
						$nextGenerationCounter++;
						$html .= '<div class="jt-clearfix">';
						$html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
						
						// name of person
						$html .= '<span class="jt-low-row">';
						if ($child->indHasChild) {
							$nextGeneration[] = ($child->relationtype) 
												? $child->id
												  .'|'.$nextGenerationCounter
												  .'|'.$person->id
												  .'|'.$child->relationtype
												: $child->id
												  .'|'.$nextGenerationCounter
												  .'|'.$person->id;
							$html .= '<a name="C'. $child->id .'"></a>'; 
						}

						if ($child->indHasChild) {
							$html .= '<a href="#P' . $child->id . '">'; 
						}						
						$html .= '<span class="jt-desc-num-label">';
						$html .= $displayNextGenNumber.'-'.$nextGenerationCounter;
						$html .= '</span>';
						if ($child->indHasChild) { 
							$html .= '</a>'; 
						}
						
						if ($child->indHasPage) {
							$html .= '<a href="' . Route::_($linkbase.$this->lists[ 'app_id' ].'!'.$child->id) . '" '.$robot.' >';
						}			
						$html .= $child->fullName;
						if ($child->indHasPage) {
							$html .= '</a>';
						} 
						if ($child->relationtype) {
							// show type of relation
							$html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($child->relationtype));
						}
						
						$html .= '</span>';
						
						// basic information
						if ($child->birthDate != null) {
							$html .= ',&nbsp;';
							if ($child->birthDate != Text::_('JT_ALTERNATIVE') ) {
								$html .= Text::_('JT_BORN').'&nbsp;';
							}
							$html .= $child->birthDate;
						}
						if ($child->deathDate != null) {
							$html .= ',&nbsp;'.Text::_('JT_DIED').'&nbsp;'.$child->deathDate;
						}
						
						$html .= '</div>';
						
						// no children but partners
						if ( (!$child->indHasChild) and ($child->indHasPartner) ) {
							$id[ 'person_id' ] 	= $child->id;		
							$person2   = new Person($id, 'basic');
							$partners2 = $person2->getPartners('basic');
							
							foreach ($partners2 as $partner2) {
								$html .= '<div class="jt-clearfix">';
								$html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
								$html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
								$html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
								if ($person2->sex == 'M') {
									$html .= ucfirst( Text::_('JT_HE') );
								} else if ($person2->sex == 'F') {
									$html .= ucfirst( Text::_('JT_SHE') );
								} else {
									$html .= ucfirst( Text::_('JT_HE').'/'.Text::_('JT_SHE') );
								}
							
								$html .= '&nbsp;'.Text::_('JT_MARRIED').'&nbsp;';
								$html .= $partner2->fullName.'</div>';
							}
						} 						
					}
				}
			}
			
			// loop through the partners of person
			foreach ($partners as $partner) {
				$html .= '<div class="jt-clearfix">';
				$html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
				if ($person->sex == 'M') {
					$html .= ucfirst( Text::_('JT_HE') );
				} else if ($person->sex == 'F') {
					$html .= ucfirst( Text::_('JT_SHE') );
				} else {
					$html .= ucfirst( Text::_('JT_HE').'/'.Text::_('JT_SHE') );
				}
			
				$html .= '&nbsp;'.Text::_('JT_MARRIED').'&nbsp;';
				$html .= $partner->fullName.'</div>';
				
				// count children for this partner
				$counter = 0;
				foreach ($children as $child) {
					if ( $child->secondParent_id == $partner->id ) {
						$counter++;
					}
				}
				
				if ($counter > 0) {
					// children with this partner
					if (array_key_exists($partner->id,$person_displayed)) { // partner already displayed, don't display children again
						$html .= '<div><a href="#CP'. $partner->id.'"><span class="jt-desc-num-label">&nbsp;</span><em>' . Text::_('JT_LABEL_FAMILY3').' '.$person_displayed[$partner->id].'</em></a></div>';
					} else { 
						$html .= '<div><a name="CP'. $person->id.'" title="' . Text::_('JT_LABEL_FAMILY3').' '.$partner->fullName.'"><span class="jt-desc-num-label">&nbsp;</span><em>' . Text::_('JT_CHILDREN_WITH') . '</em></a></div>';
						// loop through the children and filter on the correct parent 
						foreach ($children as $child) {
                            if ( $child->secondParent_id == $partner->id ) {
                                $nextGenerationCounter++;
                                $html .= '<div class="jt-clearfix">';
                                $html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
                                // name of person
                                $html .= '<span class="jt-low-row">';
                                if ($child->indHasChild) { 
                                    $nextGeneration[] = ($child->relationtype) 
                                                        ? $child->id
                                                        .'|'.$nextGenerationCounter
                                                        .'|'.$person->id
                                                        .'|'.$child->relationtype
                                                        : $child->id
                                                        .'|'.$nextGenerationCounter
                                                        .'|'.$person->id;
                                    $html .= '<a name="C'. $child->id .'"></a>'; 
                                }
                                if ($child->indHasChild) { 
                                    $html .= '<a href="#P' . $child->id . '">'; 
                                }
                                $html .= '<span class="jt-desc-num-label">';
                                $html .= $displayNextGenNumber.'-'.$nextGenerationCounter;
                                $html .= '</span>';
                                if ($child->indHasChild) { 
                                    $html .= '</a>'; 
                                }
                                if ($child->indHasPage) {
                                    $html .= '<a href="' . Route::_($linkbase.$this->lists[ 'app_id' ].'!'.$child->id) . '" '.$robot.' >';
                                }
                                $html .= $child->fullName;
                                if ($child->indHasPage) {
                                    $html .= '</a>';
                                }
                                if ($child->relationtype) {
                                    // show type of relation
                                    $html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($child->relationtype));
                                }
                                $html .= '</span>';
                                // basic information
                                if ($child->birthDate != null) {
                                    $html .= ',&nbsp;';
                                    if ($child->birthDate != Text::_('JT_ALTERNATIVE') ) {
                                        $html .= Text::_('JT_BORN').'&nbsp;';
                                    }
                                    $html .= $child->birthDate;
                                }
                                if ($child->deathDate != null) {
                                    $html .= ',&nbsp;'.Text::_('JT_DIED').'&nbsp;'.$child->deathDate;
                                }
                                $html .= '</div>';
                                // no children but partners
                                if ( (!$child->indHasChild) and ($child->indHasPartner) ) {
                                    $id[ 'person_id' ] 	= $child->id;	
                                    $person2   = new Person($id, 'basic');
                                    $partners2 = $person2->getPartners('basic');
                                    foreach ($partners2 as $partner2) {
                                        $html .= '<div class="jt-clearfix">';
                                        $html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
                                        $html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
                                        $html .= '<span class="jt-desc-num-label">&nbsp;</span>';	
                                        if ($person2->sex == 'M') {
                                            $html .= ucfirst( Text::_('JT_HE') );
                                        } else if ($person2->sex == 'F') {
                                            $html .= ucfirst( Text::_('JT_SHE') );
                                        } else {
                                            $html .= ucfirst( Text::_('JT_HE').'/'.Text::_('JT_SHE') );
                                        }
                                        $html .= '&nbsp;'.Text::_('JT_MARRIED').'&nbsp;';
                                        $html .= $partner2->fullName.'</div>';
                                    } // end loop through partners of children with no children
                                } // end: children with no children	
                            } // end: children for this partner
                        } // end loop through children
                    } // end of check for partner already displayed
                } // end: check for children for this partner
                // empty line before next partner
                $html .= '<div class="jt-clearfix">&nbsp;</div>';
            } // end loop through partners
        } // end: check for children in general
        // empty line before next person in generation
        $html .= '<div class="jt-clearfix">&nbsp;</div>';
    } // end loop through this generation
	
	array_splice($thisGeneration, 0);
	$thisGeneration = $nextGeneration;
	array_splice($nextGeneration, 0);

	$generationNumber++;
	if (count($thisGeneration) > 0) {
		if ($generationNumber <= $endGenNum) {
			$continue = true;
		} else {
			$continue = false;
		}
	} else {
		$continue = false;
	}
}

echo $html;	
?>

