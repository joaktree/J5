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

namespace Joaktree\Component\Joaktree\Site\View\Interactivetree;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Person;

class RawView extends BaseHtmlView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        if (!Session::checkToken('get')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            exit;
        }

        $app = Factory::getApplication();
        $params			= JoaktreeHelper::getJTParams();

        $input = $app->getInput();
        $this->personId = $input->get('personId');
        $what = $input->get('what');
        // rawview : lang might be wrong, reload it using original menu language
        $menulang = $input->get('lang');
        $generations = $input->getInt('generations');
        $lang 	= $app->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR, $menulang);
        $lang->load('com_joaktree', JPATH_BASE, $menulang, true);

        $model = $this->getModel();
        $lists['userAccess'] 	= $model->getAccess();
        $lists['treeId'] 		= $model->getTreeId();
        $lists['technology'] 	= $model->getTechnology();
        $this->person			= $model->getPerson();

        $lists[ 'app_id' ]		= $this->person->app_id;

        if ($what == 'full') {
            // Access
            // Person + generations
            $personId	 			= array();
            $personId[]		 		= $this->person->id.'|1';
            $lists[ 'startGenNum' ]	= 1;
            $lists[ 'endGenNum' ]	= $generations;
            $this->personId = $personId;
            $this->lists = $lists;
            $tree =  $this->build_tree();
            echo new JsonResponse($tree);
            return true;
        }
        if ($what == "more") { // need more descendants/ancestors
            $personId	 			= array();
            $personId[]		 		= $this->person->id.'|1';
            $lists[ 'startGenNum' ]	= 1;
            $lists[ 'endGenNum' ]	= $generations;
            $this->personId = $personId;
            $this->lists = $lists;
            $tree =  $this->build_tree();
            echo new JsonResponse($tree);
            return true;
        }
        if ($what != "info") { // wrong message
            return false;
        }
        // $what == info => need more information about one person
        $id['app_id']           = $this->person->app_id;
        $id[ 'person_id' ]      = $this->person->id;
        $person                 = new Person($id, 'ancestor');
        $picArray               = $this->person->getPictures(false);
        $events                 = $this->person->getPersonEvents();
        $partners	            = $person->getPartners('basic');
        $data = [];
        if ($person->deathDate) {
            $data['deathday'] = $person->deathDate;
        }
        if (count($picArray)) {
            $picture = $picArray[0]; // take1st image
            $img = $this->getPictureHtml($picture, $params->get('pxHeight', 0), $params->get('pxWidth', 0));
            $pictureName = (empty($picture->title)) ? $params->get('TitleSlideshow') : $picture->title;
            // note : popup width = 350px, so limit img width to 100px
            $data['img'] = '<img style="float: right;max-width:100px" '.$img.' title="'.$pictureName.'" alt="'.$pictureName.'" />';
        }
        foreach ($events as $event) {
            if (($event->code == 'BIRT') && $event->location && $person->birthDate) {
                $data['birthlocation'] = $event->location;
            }
            if (($event->code == 'DEAT') && $event->location && $person->deathDate) {
                $data['deathlocation'] = $event->location;
            }
        }
        if ($person->indHasPage) {
            $url = Route::_('index.php?option=com_joaktree&view=joaktree'
                .'&tech='.$lists['technology']
                .'&Itemid='.$this->person->menuItemId
                .'&treeId='.$lists['treeId']
                .'&personId='.$lists[ 'app_id' ].'!'.$person->id.'&lang='.$menulang);
            $data['url'] = '<a href="'.$url.'" target="_blank">'.Text::_('JT_TREE_MORE').'</a>';
        }
        // sort partners by marriage dates
        $partnersbymarr = array();
        $partnerevents = [];
        foreach ($partners as $partner) {
            $partnerevents[$partner->id] = $this->person->getPartnerEvents($partner->id, $partner->living);
            $marr = "";
            foreach ($partnerevents[$partner->id] as $event) {
                if ($event->code == 'MARR') {
                    if (!isset($event->eventDate)) {
                        continue;
                    }
                    if (is_numeric(strtotime($event->eventDate))) {
                        $marr = HtmlHelper::date($event->eventDate, 'Y-m-d');
                    } else {
                        preg_match_all('!\d+!', $event->eventDate, $matches);
                        foreach ($matches as $match) {
                            foreach ($match as $one) {
                                if ($one > 1000 and $one < date("Y")) {
                                    $marr = $one.'-01-01';
                                }
                            }
                        }
                    }
                }
            }
            if ($marr) {
                $partnersbymarr[$marr] = $partner;
            } else {
                $partnersbymarr[] = $partner;
            }
        }
        ksort($partnersbymarr);
        $partners = $partnersbymarr;

        foreach ($partners as $partner) {
            $events = $this->person->getPartnerEvents($partner->id, $partner->living);
            foreach ($events as $event) {
                $loc = ($event->location) ? '&nbsp;'.Text::_('JT_IN') . '&nbsp;'.$event->location : '';
                $data[Text::_($event->code).' : '.$partner->fullName] = JoaktreeHelper::displayDate($event->eventDate).$loc;
            }
        }
        $list[] = ['id' => $this->person->id,'data' => $data];
        echo new JsonResponse($list);
        return true;
    }
    private function add_person(&$list_tree, $person_id, $once = false)
    {
        $id				    = array();
        $id[ 'app_id' ]     = $this->lists[ 'app_id' ];
        $id[ 'person_id' ]  = $person_id;
        $person	    		= new Person($id, 'basic');
        if (array_key_exists($person->id, $list_tree)) {
            $obj = $list_tree[$person->id];
            $data = $obj->data;
            $rels = $obj->rels;
        } else {
            $obj = new \StdClass();
            $data = [];
            $rels = [];
        }
        $data['fullname'] =  $person->fullName;
        $data['gender'] = $person->sex;
        $person_displayed[$person->id] = $person->fullName; // save it
        if ($person->birthDate) {
            $data['birthday'] = $person->birthDate;
        } else {
            $id['app_id']           = $person->app_id;
            $id[ 'person_id' ]      = $person->id;
            $newperson              = new Person($id, 'ancestor');
            $data['birthday']       = $newperson->birthDate;
        }
        $data['needmore'] = '';
        $parents = [];
        $spouses = [];
        $childs = [];
        $children	= $person->getChildren('basic');
        $partners	= $person->getPartners('basic');
        $fathers	= $person->getFathers();
        $mothers	= $person->getMothers();
        if ($once) {
            if ($fathers) {
                $parents[] = $fathers[0]->id;
            }
            if ($mothers) {
                $parents[] = $mothers[0]->id;
            }
            if ($partners) {
                foreach ($partners as $partner) {
                    $spouses[] = $partner->id;
                }
            }
            if ($children) {
                foreach ($children as $child) {
                    $childs[] = $child->id;
                }
            }
        } else {
            if ($children || $partners || $fathers || $mothers) {
                $data['needmore'] = $id[ 'app_id' ] .'!'.$person_id;
            }
        }
        $rels['parents'] = $parents;
        $rels['spouses'] = $spouses;
        $rels['children'] = $childs;
        $obj->rels = $rels;
        $obj->data = $data;
        $list_tree[$person->id] = $obj;
        // fill missing keys
        if ($fathers) {
            if (!array_key_exists($fathers[0]->id, $list_tree)) {
                $this->add_person($list_tree, $fathers[0]->id);
            }
        }
        if ($mothers) {
            if (!array_key_exists($mothers[0]->id, $list_tree)) {
                $this->add_person($list_tree, $mothers[0]->id);
            }
        }
        if ($once) {
            foreach ($partners as $partner) {
                if (array_key_exists($partner->id, $list_tree)) {
                    continue;
                }
                $this->add_person($list_tree, $partner->id);
            }
        }
        if ($once) {
            foreach ($children as $child) {
                if (array_key_exists($child->id, $list_tree)) {
                    continue;
                }
                $this->add_person($list_tree, $child->id);
            }
        }
    }
    public function build_tree()
    {
        $personIdArray	= $this->personId;
        $id				= array();
        $id[ 'app_id' ] = $this->lists[ 'app_id' ];
        $thisGeneration = array();
        $nextGeneration = array();

        foreach ($personIdArray as $personId) {
            $thisGeneration[] = $personId;
        }
        $generationNumber = $this->lists[ 'startGenNum' ];
        $endGenNum = $this->lists[ 'endGenNum' ];
        $list_tree = [];
        // get children
        $continue = true;
        while ($continue == true) {
            $person_displayed = [];
            $nextGenerationCounter = 0;

            foreach ($thisGeneration as $gen_i => $generation) {
                $genPerson = explode('|', $generation);
                $id[ 'person_id' ] 	= $genPerson[0] ;
                $person	    		= new Person($id, 'basic');
                if (isset($genPerson[3])) {
                    // This is relationtype
                    $person->relationtype = $genPerson[3];
                }

                if (array_key_exists($person->id, $list_tree)) {
                    $obj = $list_tree[$person->id];
                    $data = $obj->data;
                    $rels = $obj->rels;
                } else {
                    $obj = new \StdClass();
                    $data = [];
                    $rels = [];
                }
                $data['fullname'] =  $person->fullName;
                $data['gender'] = $person->sex;
                $person_displayed[$person->id] = $person->fullName; // save it
                if ($person->birthDate) {
                    $data['birthday'] = $person->birthDate;
                } else {
                    $id['app_id']           = $person->app_id;
                    $id[ 'person_id' ]      = $person->id;
                    $newperson              = new Person($id, 'ancestor');
                    $data['birthday']       = $newperson->birthDate;
                }
                $obj->data = $data;
                $children	= $person->getChildren('basic');
                $partners	= $person->getPartners('basic');
                $fathers	= $person->getFathers();
                $mothers	= $person->getMothers();
                $parents = [];
                if ($fathers) {
                    $parents[] = $fathers[0]->id;
                    $this->add_person($list_tree, $fathers[0]->id, true);
                }
                if ($mothers) {
                    $parents[] = $mothers[0]->id;
                    $this->add_person($list_tree, $mothers[0]->id, true);
                }
                $rels['parents'] = $parents;
                $spouses = [];
                if ($partners) {
                    foreach ($partners as $partner) {
                        $spouses[] = $partner->id;
                        $this->add_person($list_tree, $partner->id, true);
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
                $obj->rels = $rels;
                $list_tree[$person->id] = $obj;

                if (count($children) > 0) {
                    // check for children with only one parent
                    $counter_1 = 0;
                    foreach ($children as $child) {
                        if ($child->secondParent_id == null) {
                            $counter_1++;
                        }
                    }
                    if ($counter_1 > 0) {
                        foreach ($children as $child) {
                            if ($child->secondParent_id == null) {
                                $nextGenerationCounter++;
                                if ($child->indHasChild) {
                                    $nextGeneration[] = ($child->relationtype)
                                                ? $child->id
                                                  .'|'.$nextGenerationCounter
                                                  .'|'.$person->id
                                                  .'|'.$child->relationtype
                                                : $child->id
                                                  .'|'.$nextGenerationCounter
                                                  .'|'.$person->id;
                                }
                            }
                        }
                    }
                }
                foreach ($partners as $partner) {
                    // count children for this partner
                    $counter = 0;
                    foreach ($children as $child) {
                        if ($child->secondParent_id == $partner->id) {
                            $counter++;
                        }
                    }
                    if ($counter > 0) {
                        // children with this partner
                        if (!array_key_exists($partner->id, $person_displayed)) { // partner already displayed, don't display children again
                            foreach ($children as $child) {
                                if ($child->secondParent_id == $partner->id) {
                                    $nextGenerationCounter++;
                                    if ($child->indHasChild) {
                                        $nextGeneration[] = ($child->relationtype)
                                                        ? $child->id
                                                        .'|'.$nextGenerationCounter
                                                        .'|'.$person->id
                                                        .'|'.$child->relationtype
                                                        : $child->id
                                                        .'|'.$nextGenerationCounter
                                                        .'|'.$person->id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
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
        // build list to send to js library
        $list = [];
        foreach ($list_tree as $key => $one) {
            $data = $one->data;
            $rels = $one->rels;
            $parents = isset($rels['parents']) ? $rels['parents'] : null;
            $spouses = isset($rels['spouses']) ? $rels['spouses'] : null;
            $children = isset($rels['children']) ? $rels['children'] : null;
            $datainfo = [];
            foreach ($data as $keyd => $oned) {
                if ($oned) {
                    $datainfo[$keyd] = $oned;
                }
            }
            $childrenlist = [];
            if ($children) {
                foreach ($children as $child) {
                    if (array_key_exists($child, $list_tree)) {
                        $childrenlist[] = $child;
                    }
                }
            }
            $rels = ['parents' => $parents, 'spouses' => $spouses, 'children' => $childrenlist];
            $list[] = ['id' => $key,'data' => $datainfo,'rels' => $rels];
        }
        return $list;
    }
    private function getPictureHtml(&$picture, $picHeight, $picWidth)
    {
        $html = '';
        // retrieve size of picture
        $uri = Uri::getInstance($picture->file);
        $scheme = $uri->getScheme();
        if (!$scheme) { // local file
            $picturefile = Uri::root().$picture->file;
            $imagedata   = GetImageSize($picture->file);
        } else { // remote file
            $picturefile = $picture->file;
        }
        $html .= ' src="'.$picturefile.'" height="'.$picHeight.'" width="'. $picWidth.'" ';
        return $html;
    }
}
