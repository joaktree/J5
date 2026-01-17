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
    
    const f3Chart = f3.createChart('#FamilyChart', data)
			    	.setTransitionTime(1000)
   				 	.setCardXSpacing(230)
    				.setCardYSpacing(150)
    				.setAncestryDepth(<?php echo $params->get('ancestors', 3);?>)
   					.setProgenyDepth(<?php echo $params->get('descendants', 1);?>)
					.setSingleParentEmptyCard(false)
                    .setShowSiblingsOfMain(false) // show brothers/sisters on main
 
    const f3Card = f3Chart.setCardHtml()
	    .setCardInnerHtmlCreator(d => {
			url = '',birth = ''
			if (d.data.data["url"]) url = "<br><a href='"+d.data.data["url"]+"'target='_blank'>URL</a>";
			if (d.data.data["birthday"]) birth = "<br>"+d.data.data["birthday"];
      return `<div class="card-inner" style="width: 200px; height: auto; padding: 15px; border-radius: 5px; text-align: center;">
        <div>${d.data.data["fullname"]}${birth}${url}</div>
      </div>`
    	})
		.setMiniTree(true)
        <?php if ($params->get('latest', 'true') == 'true') { ?> 
        .setOnCardClick(function (e) {
            element = { id:e.currentTarget.getAttribute('data-id'),
                        fullname:e.currentTarget.parentNode.__data__.data.data.fullname
                    }
            logs.push(element)
            if (logs.length > <?php echo $params->get('latestsize', 5);?>) {
                logs.shift()
            }
            updateLogDropdown(logs)
            f3Chart.updateMainId(e.currentTarget.getAttribute('data-id'))
    	    f3Chart.updateTree({initial: false})
        })
        <?php } ?>
    
	f3Chart.updateTree({initial: true})

    // with person_id this function will update the tree
	function updateTreeWithNewMainPerson(person_id, animation_initial = true) {
    	f3Chart.updateMainId(person_id)
    	f3Chart.updateTree({initial: animation_initial})
  	}
    <?php if ($params->get('latest', 'true') == 'true') { ?>
    //---------- log selected boxes -----------
    let logs = [];
    let prevsbtn = d3.select(document.querySelector("#FamilyChart")).append("button").text('latest selections')
    .attr('id','logprevsbtn')
    .attr("style", "position: absolute; top: 10px; right: 20px; width: 150px;z-index: 1000;display:none")
    .attr('data-bs-toggle','collapse')
    .attr('data-bs-target','#logprevs')
    .on("focusout", () => {
        setTimeout( () => {
                        log_cont.attr('class','collapse')
                    }, 200);
    })
    const log_cont = d3.select(document.querySelector("#FamilyChart")).append("div")
    .attr("style", "position: absolute; top: 40px; right: 20px; width: 150px; z-index: 1000;")
    .attr('class','collapse')
    .attr('id','logprevs')

    function updateLogDropdown(options) {
        prevsbtn.attr("style", "position: absolute; top: 10px; right: 20px; width: 150px;z-index: 1000;display:block")
        options.reverse() // inverse le tableau
        dropdownlog.selectAll("div").data(options).join("div")
        .attr("style", "padding: 5px;cursor: pointer;border-bottom: .5px solid currentColor;")
        .on("click", (e, d) => {
            updateTreeWithNewMainPerson(d.id, true)
        })
        .text(d => d.fullname)
        options.reverse() //remet le tableau dans l'ordre
    }	
    const dropdownlog = log_cont.append("div").attr("style", "overflow-y: auto; max-height: 300px; background-color: <?php echo $params->get('background');?>;")
    .attr("tabindex", "0")
    .on("wheel", (e) => {
      e.stopPropagation()
    })
    <?php } ?>
    <?php if ($params->get('search', 'true') == 'true') { ?>
  //------------ setup search dropdown -----------
    const all_select_options = []
    data.forEach(d => {
    if (all_select_options.find(d0 => d0.value === d["id"])) return
    all_select_options.push({label: `${d.data["fullname"]}`, value: d["id"]})
    })
    const search_cont = d3.select(document.querySelector("#FamilyChart")).append("div")
    .attr("style", "position: absolute; top: 10px; left: 10px; width: 150px; z-index: 1000;")
    .on("focusout", () => {
      setTimeout(() => {
        if (!search_cont.node().contains(document.activeElement)) {
          updateDropdown([]);
        }
      }, 200);
    })
    const search_input = search_cont.append("input")
        .attr("style", "width: 100%;")
        .attr("type", "text")
        .attr("placeholder", "Search")
        .on("focus", activateDropdown)
        .on("input", activateDropdown)

    const dropdown = search_cont.append("div").attr("style", "overflow-y: auto; max-height: 300px; background-color: <?php echo $params->get('background');?>")
        .attr("tabindex", "0")
        .on("wheel", (e) => {
        e.stopPropagation()
    })

    function activateDropdown() {
        const search_input_value = search_input.property("value")
        const filtered_options = all_select_options.filter(d => d.label.toLowerCase().includes(search_input_value.toLowerCase()))
        updateDropdown(filtered_options)
    }

    function updateDropdown(filtered_options) {
        dropdown.selectAll("div").data(filtered_options).join("div")
        .attr("style", "padding: 5px;cursor: pointer;border-bottom: .5px solid currentColor;")
        .on("click", (e, d) => {
    <?php if ($params->get('latest', 'true') == 'true') { ?>
        // store in latest selected items list
            element = { id:d.value,fullname:d.label}
            logs.push(element)
            if (logs.length > <?php echo $params->get('latestsize', 5);?>) { // keep 5 latest clicks 
                logs.shift()
            }
            updateLogDropdown(logs)
    <?php } ?>
            updateTreeWithNewMainPerson(d.value, true)
          })
        .text(d => d.label)
    }	
    <?php } ?>
</script>


