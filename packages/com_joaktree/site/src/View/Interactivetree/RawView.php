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
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
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
        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $input = Factory::getApplication()->getInput();
        $this->personId = $input->get('personId');
        $what = $input->get('what');
        $model = $this->getModel();
        $lists['userAccess'] 	= $model->getAccess();
        $lists['treeId'] 		= $model->getTreeId();
        $lists['technology'] 	= $model->getTechnology();
        $this->person			= $model->getPerson();
        $lists[ 'app_id' ]		= $this->person->app_id;
        $params			= JoaktreeHelper::getJTParams();
        if ($what == 'full') {
            // Access
            // Person + generations
            $personId	 			= array();
            $personId[]		 		= $this->person->id.'|1';
            $lists[ 'startGenNum' ]	= 1;
            $lists[ 'endGenNum' ]	= (int) $params->get('descendantlevel', 20);
            $this->personId = $personId;
            $this->lists = $lists;
            $tree =  $this->build_tree();
            echo new JsonResponse($tree);
            return true;
        } elseif ($what == "more") {
            $data = [];
            $picArray = $this->person->getPictures(false);
            if (count($picArray)) {
                $picture = $picArray[0]; // take 1st image
                $img = $this->getPictureHtml($picture, $params->get('pxHeight', 0), $params->get('pxWidth', 0));
                $pictureName = (empty($picture->title)) ? $params->get('TitleSlideshow') : $picture->title;
                $data['img'] = '<img style="float: right;" '.$img.' title="'.$pictureName.'" alt="'.$pictureName.'" />';
            }
            $events = $this->person->getPersonEvents();
            foreach ($events as $event) {
                if (($event->code == 'BIRT') && $event->location) {
                    $data['birthlocation'] = $event->location;
                }
                if (($event->code == 'DEAT') && $event->location) {
                    $data['deathlocation'] = $event->location;
                }
            }
            $list[] = ['id' => $this->person->id,'data' => $data];
            echo new JsonResponse($list);
            return true;
        }
        return false;
    }
    public function create_tree(&$list_tree, $person, $url, $fathers, $mothers, $partners, $children, $count = 0)
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
        $data['fullname'] =  $person->fullName;
        $data['gender'] = $person->sex;
        $data['birthday'] = $person->birthDate;
        $data['deathday'] = $person->deathDate;
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

        $obj->rels = $rels;
        $list_tree[$person->id] = $obj;

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
            $this->create_tree($list_tree, $onef, $child_url, $onef_fathers, $onef_mothers, $onef_partners, $onef_children, $count);
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
            $this->create_tree($list_tree, $onem, $child_url, $onem_fathers, $onem_mothers, $onem_partners, $onem_children, $count);
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
                    $this->create_tree($list_tree, $onep, $child_url, $onep_fathers, $onep_mothers, $onep_partners, $onep_children);
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
                    $this->create_tree($list_tree, $onec, $child_url, $onec_fathers, $onec_mothers, $onec_partners, $onec_children);
                }
            }
        }
    }
    public function build_tree()
    {
        $linkbase = 'index.php?option=com_joaktree&view=joaktree'
                .'&tech='.$this->lists['technology']
                .'&Itemid='.$this->person->menuItemId
                .'&treeId='.$this->lists['treeId']
                .'&personId=';

        $personIdArray	= $this->personId;
        $id				= array();
        $id[ 'app_id' ] = $this->lists[ 'app_id' ];
        $thisGeneration = array();

        foreach ($personIdArray as $personId) {
            $thisGeneration[] = $personId;
        }

        $list_tree = [];

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
            $this->create_tree($list_tree, $person, $url, $fathers, $mothers, $partners, $children);
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
                    if ($keyd == 'url') {
                        $oned = Route::_($linkbase.$this->lists[ 'app_id' ].'!'.$oned);
                        $oned = '<a href="'.$oned.'" target="_blank">'.Text::_('JT_TREE_MORE').'</a>';
                    }
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
        if (realpath($picture->file)) {
            $imagedata   = GetImageSize($picture->file);
        } else {
            $imagedata = [$picWidth,$picHeight]; // default size for remote images
        }
        $imageWidth  = $imagedata[0];
        $imageHeight = $imagedata[1];
        // if heigth is larger than set heigth, picture has to be shrunk
        if ($imageHeight > $picHeight) {
            $ratioH = $picHeight / $imageHeight;
        } else {
            $ratioH = 1;
        }
        // if width is larger than set width, picture has to be shrunk
        if ($imageWidth > $picWidth) {
            $ratioW = $picWidth / $imageWidth;
        } else {
            $ratioW = 1;
        }
        // pick the smallest ratio of the two
        if ($ratioH < $ratioW) {
            $ratio = $ratioH;
        } else {
            $ratio = $ratioW;
        }
        // new sizes are
        $showWidth  = $ratio * $imageWidth;
        $showHeigth = $ratio * $imageHeight;
        $html .= ' src="'.$picture->file.'" height="'.$showHeigth.'" width="'. $showWidth.'" ';
        return $html;
    }
}
