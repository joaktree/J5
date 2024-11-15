<?php 
/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joaktree\Component\Joaktree\Site\Helper\Person;

?>

<?php
$startGenNum 	= $this->lists[ 'startGenNum' ];
$endGenNum		= $this->lists[ 'endGenNum' ];
$personIdArray	= $this->personId;
$id				= array();
$id[ 'app_id' ] = $this->lists[ 'app_id' ];


$html = '';
$generationNumber = $startGenNum;
$ancestor = array();
$thisGeneration = array();
$nextGeneration = array();
$linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$this->lists['technology'];
$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';

foreach ($personIdArray as $personId) {
    $thisGeneration[] = $personId;
}

$generationCounter = 0;
$continue = true;
while ($continue == true) {
    $moreGenerations  = false;
    $generationCounter++;

    foreach ($thisGeneration as $gen_i => $generation) {
        $genPerson  = explode('|', $generation);

        $id[ 'person_id' ] 	= $genPerson[0];
        $person	    		= new Person($id, 'ancestor'); //'basic');
        if (isset($genPerson[2])) {
            // This is relationtype
            $person->relationtype = $genPerson[2];
        }
        $ancestor[] 		= $person;

        if ($genPerson[0]) {
            $fathers			= $person->getFathers('basic');
            $mothers			= $person->getMothers('basic');

            // look for next generation: fathers
            if (is_array($fathers)) {
                $tmp = array_shift($fathers);
                if (isset($tmp->id)) {
                    $moreGenerations  = true;
                    $nextGeneration[] = ($tmp->relationtype)
                                            ? $tmp->id.'|'.$person->id.'|'.$tmp->relationtype
                                            : $tmp->id.'|'.$person->id;
                } else {
                    $nextGeneration[] = '|'.$person->id;
                }
                unset($tmp);
            } else {
                $nextGeneration[] = '|'.$person->id;
            }

            // look for next generation: fathers
            if (is_array($mothers)) {
                $tmp = array_shift($mothers);
                if (isset($tmp->id)) {
                    $moreGenerations  = true;
                    $nextGeneration[] = ($tmp->relationtype)
                                            ? $tmp->id.'|'.$person->id.'|'.$tmp->relationtype
                                            : $tmp->id.'|'.$person->id;
                } else {
                    $nextGeneration[] = '|'.$person->id;
                }
                unset($tmp);
            } else {
                $nextGeneration[] = '|'.$person->id;
            }

        } else {
            $nextGeneration[] = '|';
            $nextGeneration[] = '|';
        }

    } // end loop through this generation

    array_splice($thisGeneration, 0);
    $thisGeneration = $nextGeneration;
    array_splice($nextGeneration, 0);

    $generationNumber++;
    if (count($thisGeneration) > 0) {
        if ($generationNumber < $endGenNum) {
            if ($moreGenerations) {
                $continue = true;
            } else {
                $continue = false;
            }
        } else {
            $continue = false;
        }
    } else {
        $continue = false;
    }
}

if ($generationCounter > 1) {
    $html .= '<table>';

    $html .= '<thead>';
    $html .= '<tr>';

    if ($generationCounter > 1) {
        $html .= '<th colspan="2" class="jt-content-th">'.Text::_('JT_PARENTS').'</th>';
    }

    if ($generationCounter > 2) {
        $html .= '<th colspan="2" class="jt-content-th">'.Text::_('JT_GRANDPARENTS').'</th>';
    }

    if ($generationCounter > 3) {
        $html .= '<th colspan="2" class="jt-content-th">'.Text::_('JT_GREATGRANDPARENTS').'</th>';
    }

    for ($k = 1, $p = ($generationCounter - 4); $k <= $p; $k++) {
        $html .= '<th colspan="2" class="jt-content-th">';

        $label = '';
        for ($l = 0, $o = $k; $l < $o; $l++) {
            $label .= Text::_('JT_GREAT_P').'-';
        }

        $label .= Text::_('JT_GREATGRANDPARENTS');
        $html  .= ucfirst(strtolower($label)).'</th>';
    }

    $html .= '</tr>';
    $html .= '</thead>';

    $baseNum = $generationCounter;
    $loopNum = pow(2, ($baseNum - 1));
    $cellCounter = $loopNum;

    for ($i = 0, $n = $loopNum; $i < $n; $i++) {
        $html .= '<tr>';

        if ($i == 0) {
            $innerLoopNum = $baseNum;
        } else {
            $ready = false;
            $innerCounter = 0;
            $innerNumber  = $i;

            while (!$ready) {
                $innerCounter++;
                if (($innerNumber & 1) == 1) {
                    $innerLoopNum = $innerCounter;
                    $ready = true;
                } else {
                    $innerNumber = $innerNumber >> 1;
                }
            }
        }

        $ready = false;
        $startValue = $cellCounter;
        while (!$ready) {
            $tmp1 = $startValue >> 1;
            $tmp2 = $tmp1 << 1;

            if ($tmp2 == $startValue) {
                $startValue = $tmp1;
            } else {
                $ready = true;
            }
        }

        $rowspan = pow(2, ($innerLoopNum - 1));
        for ($j = 0, $m = $innerLoopNum; $j < $m; $j++) {
            if (($i + $j) != 0) {
                $html .= '<td rowspan="'.$rowspan.'" class="jt-ancestor-number">'.$startValue.':</td>';
                $html .= '<td rowspan="'.$rowspan.'" class="jt-ancestor-name">';
                if ($ancestor[($startValue - 1)]->indHasPage == true) {
                    $html .= '<a href="'.Route::_($linkBase
                        .'&Itemid='.$ancestor[($startValue - 1)]->menuItemId
                        .'&treeId='.$ancestor[($startValue - 1)]->tree_id
                        .'&personId='.$ancestor[($startValue - 1)]->app_id.'!'.$ancestor[($startValue - 1)]->id)
                        .'" '.$robot.' >';
                }
                $html .= $ancestor[($startValue - 1)]->fullName;
                if ($ancestor[($startValue - 1)]->indHasPage == true) {
                    $html .= '</a>';
                }
                if ($ancestor[($startValue - 1)]->relationtype) {
                    // show type of father
                    $html .= '&nbsp;|&nbsp;'.Text::_(strtoupper($ancestor[($startValue - 1)]->relationtype));
                }

                if ($this->lists[ 'showDates' ] == 1) {
                    if (!empty($ancestor[($startValue - 1)]->birthDate)
                       || !empty($ancestor[($startValue - 1)]->deathDate)
                    ) {
                        $html .= '<span class="jt-ancestor-date">';
                    }

                    if (!empty($ancestor[($startValue - 1)]->birthDate)) {
                        $html .= '<br />'.Text::_('JT_BORN_ABR').':&nbsp;'.$ancestor[($startValue - 1)]->birthDate;
                    }

                    if (!empty($ancestor[($startValue - 1)]->deathDate)) {
                        $html .= '<br />'.Text::_('JT_DIED_ABR').':&nbsp;'.$ancestor[($startValue - 1)]->deathDate;
                    }

                    if (!empty($ancestor[($startValue - 1)]->birthDate)
                       || !empty($ancestor[($startValue - 1)]->deathDate)
                    ) {
                        $html .= '</span>';
                    }
                }

                $html .= '</td>';
            }

            $startValue = $startValue << 1;
            $rowspan = $rowspan >> 1;
        }

        $cellCounter++;


        $html .= '</tr>';
    }
    $html .= '</table>';

}

echo $html;
?>

