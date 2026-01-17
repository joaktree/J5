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
 * use https://github.com/donatso/family-chart/tree/master
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Person;

function create_tree(&$list_tree, $person, $url, $fathers, $mothers, $partners, $children, $count = 0)
{
    $count += 1;
    if (array_key_exists($person->id, $list_tree)) {
        $obj = $list_tree[$person->id];
        $data = $obj->data;
        $rels = $obj->rels;
    } else {
        $obj = new \StdClass();
        $data = [];
        $rels = [];
    }
    $data['fullname'] =  str_replace("'", "\'", $person->fullName);
    $data['gender'] = $person->sex;
    $data['birthday'] = $person->birthDate;
    if ($url) {
        $data['url'] = $url;
    }
    $obj->data = $data;
    $parents = [];
    if ($fathers) {
        $parents[] = $fathers[0]->id;
    }
    if ($mothers) {
        $parents[] = $mothers[0]->id;
    }
    $rels['parents'] = $parents;
    $spouses = [];
    if ($partners) {
        foreach ($partners as $partner) {
            $spouses[] = $partner->id;
        }
    }
    $rels['spouses'] = $spouses;
    $childs = [];
    if ($children) {
        foreach ($children as $child) {
            $childs[] = $child->id;
        }
    }
    $rels['children'] = $childs;
    /*
    if ($count > 1) { // only on level for parents in another tree
        $rels['parents'] = [];
    }
    */
    $obj->rels = $rels;
    $list_tree[$person->id] = $obj;
    // check if all keys exist, if not, just create them
    /*
    if ($count > 1 && count($partners) <= 1) {
        return;
    }
    */
    if ($fathers && !array_key_exists($fathers[0]->id, $list_tree)) {
        $onef = $fathers[0];
        $onef_children	= $onef->getChildren('basic');
        $onef_partners	= $onef->getPartners('basic');
        $onef_fathers	= $onef->getFathers();
        $onef_mothers	= $onef->getMothers();
        $child_url = "";
        if ($onef->indHasPage) {
            $child_url = $onef->id;
        }
        create_tree($list_tree, $onef, $child_url, $onef_fathers, $onef_mothers, $onef_partners, $onef_children, $count);
    }

    if ($mothers && !array_key_exists($mothers[0]->id, $list_tree)) {
        $onem = $mothers[0];
        $onem_children	= $onem->getChildren('basic');
        $onem_partners	= $onem->getPartners('basic');
        $onem_fathers	= $onem->getFathers();
        $onem_mothers	= $onem->getMothers();
        $child_url = "";
        if ($onem->indHasPage) {
            $child_url = $onem->id;
        }
        create_tree($list_tree, $onem, $child_url, $onem_fathers, $onem_mothers, $onem_partners, $onem_children, $count);
    }
    if ($partners) {
        foreach ($partners as $onep) {
            if (!array_key_exists($onep->id, $list_tree)) {
                $onep_children	= $onep->getChildren('basic');
                $onep_partners	= $onep->getPartners('basic');
                $onep_fathers	= $onep->getFathers();
                $onep_mothers	= $onep->getMothers();
                $child_url = "";
                if ($onep->indHasPage) {
                    $child_url = $onep->id;
                }
                create_tree($list_tree, $onep, $child_url, $onep_fathers, $onep_mothers, $onep_partners, $onep_children);
            }
        }
    }

    if ($children) {
        foreach ($children as $onec) {
            if (!array_key_exists($onec->id, $list_tree)) {
                $onec_children	= $onec->getChildren('basic');
                $onec_partners	= $onec->getPartners('basic');
                $onec_fathers	= $onec->getFathers();
                $onec_mothers	= $onec->getMothers();
                $child_url = "";
                if ($onec->indHasPage) {
                    $child_url = $onec->id;
                }
                create_tree($list_tree, $onec, $child_url, $onec_fathers, $onec_mothers, $onec_partners, $onec_children);
            }
        }
    }

}
$params = JoaktreeHelper::getJTParams();

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

$generationNumber = $startGenNum;
$thisGeneration = array();
$nextGeneration = array();

foreach ($personIdArray as $personId) {
    $thisGeneration[] = $personId;
}
$continue = true;

$list_tree = [];

while ($continue == true) {
    $displayGenerationNum = JoaktreeHelper::displayEnglishCounter($generationNumber);
    $displayThisGenNumber = JoaktreeHelper::arabicToRomanNumeral($generationNumber);
    $displayNextGenNumber = JoaktreeHelper::arabicToRomanNumeral($generationNumber + 1);

    $nextGenerationCounter = 0;
    $url = "";
    foreach ($thisGeneration as $gen_i => $generation) {
        $genPerson = explode('|', $generation);

        $id[ 'person_id' ] 	= $genPerson[0] ;
        $person	    		= new Person($id, 'basic');
        if (isset($genPerson[3])) {
            // This is relationtype
            $person->relationtype = $genPerson[3];
        }

        $children	= $person->getChildren('basic');
        $partners	= $person->getPartners('basic');
        $fathers	= $person->getFathers();
        $mothers	= $person->getMothers();
        $url = "";
        if ($person->indHasPage) {
            $url = $person->id;
        }

        create_tree($list_tree, $person, $url, $fathers, $mothers, $partners, $children);

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

// format data to be used in the family-chart library
$list = 'const data = [';

foreach ($list_tree as $key => $one) {
    $data = $one->data;
    $rels = $one->rels;
    $parents = isset($rels['parents']) ? $rels['parents'] : null;
    $spouses = isset($rels['spouses']) ? $rels['spouses'] : null;
    $children = isset($rels['children']) ? $rels['children'] : null;

    $datainfo = "'data':{";
    foreach ($data as $keyd => $oned) {
        if ($oned) {
            if ($keyd == 'url') {
                $oned = Route::_($linkbase.$this->lists[ 'app_id' ].'!'.$oned);
            }
            $datainfo .= "'".$keyd."':'".$oned."',";
        }
    }

    $datainfo .= "}";

    $parentslist = "'parents':['',''],";
    if ($parents) {
        $tmp  = implode("','", $parents);
        $parentslist = "'parents':['".$tmp."'],";
    }
    $childrenlist = "";
    if ($children) {
        $childrenlist = "'children':[";
        foreach ($children as $child) {
            if (array_key_exists($child, $list_tree)) {
                $childrenlist .= "'".$child."',";
            }
        }
        $childrenlist .= "]";
    }
    $spouseslist = "";
    if ($spouses) {
        $spouseslist = "'spouses':[";
        foreach ($spouses as $spouse) {
            $spouseslist .= "'".$spouse."',";
        }
        $spouseslist .= "],";
    }
    $list .= "{'id':'".$key."',".$datainfo.",'rels':{".$parentslist.$spouseslist.$childrenlist."}},";

}

$list .= ']';

HTMLHelper::_('bootstrap.collapse', '#logprevsbtn');
?>

<div id="FamilyChart" class="f3" style="width:100%;height:90vh;margin:auto;background-color:<?php echo $params->get('background', '#e0e0e0');?>;color:<?php echo $params->get('color', '#737272');?>;"></div>

<script>
   <?php echo $list;?>;
</script>


