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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Map;

/**
 * HTML View class for the Joaktree component
 */
class HtmlView extends BaseHtmlView
{
    protected $Html = array();
    public function display($tpl = null)
    {
        $lang 	= Factory::getApplication()->getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $model = $this->getModel();

        // Load the parameters.
        $model			= $this->getModel();
        $this->params	= JoaktreeHelper::getJTParams();
        $document		= Factory::getApplication()->getDocument();
        if ($this->params->get('siteedit', 1)) {
            $canDo		 	= JoaktreeHelper::getActions();
        } else {
            $canDo		 	= null;
        }
        // Find the value for tech
        $technology		= $model->getTechnology();
        // set up style sheets and javascript files
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss());
        HTMLHelper::stylesheet(JoaktreeHelper::joaktreecss($this->params->get('theme')));
        // Set up shadowbox
        HTMLHelper::stylesheet(JoaktreeHelper::shadowboxcss());
        $document->addScript(JoaktreeHelper::shadowboxjs());

        // Set up modal behavior
        HTMLHelper::_('bootstrap.modal', 'a.modal');
        if ($technology != 'b') {
            // javascript template - no ajax
            // default template includes ajax
            HTMLHelper::stylesheet(JoaktreeHelper::briaskcss());
            $document->addScript(JoaktreeHelper::joaktreejs('mod_briaskISS.js'));
            $document->addScript(JoaktreeHelper::joaktreejs('toggle.js'));
        }
        if (($technology != 'b') and ($technology != 'j')) {
            // default template includes ajax
            $document->addScript(JoaktreeHelper::joaktreejs('jtajax.js'));
        }
        // Access
        $lists['userAccess'] 	= $model->getAccess();
        $lists['technology'] 	= $technology;
        $edit					= $model->getAction();
        $lists['edit'] 			= ($edit == 'edit') ? true : false;

        // Person
        $this->person			= $model->getPerson();
        $model->setCookie();
        $Html[ 'lineage' ]	= $this->showLineage();
        $lists['showAncestors']   = (int) $this->params->get('ancestorchart', 0);
        $lists['showDescendants'] = (int) $this->params->get('descendantchart', 0);
        $lists['numberArticles']  = $this->person->getArticleCount();
        // Pictures
        $Html[ 'pictures' ]		= $this->showPictures();
        $lists[ 'nextDelay']	= round(((int) $this->params->get('nextDelay', 0)) / 1000, 3);
        $lists[ 'transDelay']	= round(((int) $this->params->get('transDelay', 0)) / 1000, 3);
        // Static map
        if ($this->person->map == 1) {
            $id = array();
            $id['map']		= Map::getMapId(true);
            $id['location']	= Map::getLocationId(true);
            $id['distance']	= Map::getDistance(true);
            $id['person']	= JoaktreeHelper::getPersonId(false, true);
            $id['tree']		= JoaktreeHelper::getTreeId(false, true);
            $id['app']		= JoaktreeHelper::getApplicationId(false, true);
            $this->map 	= new Map($id);

            $Html[ 'staticmap' ] 	= $this->person->getStaticMap();
            $lists['indStaticMap'] = ($Html[ 'staticmap' ]) ? true : false;
        }
        // Interactive map
        if ($this->person->map == 2) {
            $id = array();
            $id['map']		= Map::getMapId(true);
            $id['location']	= Map::getLocationId(true);
            $id['distance']	= Map::getDistance(true);
            $id['person']	= JoaktreeHelper::getPersonId(false, true);
            $id['tree']		= JoaktreeHelper::getTreeId(false, true);
            $id['app']		= JoaktreeHelper::getApplicationId(false, true);
            $this->map 	= new Map($id);

            $Html[ 'interactivemap' ] 	= $this->person->getInteractiveMap();
            $lists['indInteractiveMap'] = ($Html[ 'interactivemap' ]) ? true : false;
            $lists[ 'pxHeightMap']	= (int) $this->params->get('pxHeight', 0);
        }
        // last update
        $lists[ 'showUpdate ']	= $this->params->get('show_update');
        if ($lists[ 'showUpdate '] != 'N') {
            $lists[ 'lastUpdate' ]	= JoaktreeHelper::lastUpdateDateTimePerson($this->person->lastUpdateDate);
            $lists[ 'showchange' ]	= (int) $this->params->get('indLogging', 0);
        }
        // copyright
        $lists[ 'CR' ]			= JoaktreeHelper::getJoaktreeCR();
        $retObject				= new \stdClass();
        $retObject->object		= 'prsn';
        $retObject->app_id		= $this->person->app_id;
        $retObject->object_id	= $this->person->id;
        $lists[ 'retId' ]		= base64_encode(json_encode($retObject));
        // tab behavior
        if ((int) $this->params->get('indTabBehavior') == 1) {
            $lists[ 'action' ]	= 'onClick';
        } else {
            $lists[ 'action' ]	= 'onMouseOver';
        }
        $this->Html = $Html;
        $this->canDo = $canDo;
        $this->lists = $lists;
        if ($lists['userAccess']) {
            // set title, meta title
            $title = $this->person->firstName.' '.$this->person->familyName;
            $document->setTitle($title);
            $document->setMetadata('title', $title);
            // set additional meta tags
            if ($this->params->get('menu-meta_description')) {
                $document->setDescription($this->params->get('menu-meta_description'));
            }
            if ($this->params->get('menu-meta_keywords')) {
                $document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
            }
            // robots
            if ($this->person->robots > 0) {
                $document->setMetadata('robots', JoaktreeHelper::stringRobots($this->person->robots));
            } elseif ($this->params->get('robots')) {
                $document->setMetadata('robots', $this->params->get('robots'));
            }
        }
        parent::display($tpl);
    }
    private function showLineage()
    {
        $html = '';
        // Find the value for tech
        $model = $this->getModel();

        $technology		= $model->getTechnology();
        $linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$technology;
        $robot	  = ($technology == 'a') ? '' : 'rel="noindex, nofollow"';
        $lineageArray		= $this->person->getLineage();
        if (is_array($lineageArray)) { //and isset($lineageArray[0])) {
            $html .= '<div class="jt-small"><span>';
            $i = 0;
            $n = count($lineageArray);
            foreach ($lineageArray as $lineage) {
                $link = Route::_(
                    $linkBase
                        .'&Itemid='.$lineage['menuItemId']
                        .'&treeId='.$lineage['tree_id']
                        .'&personId='.$lineage['app_id'].'!'.$lineage['person_id']
                );
                $name = $lineage[ 'firstName' ];
                if ($lineage[ 'familyName' ] != null) {
                    $name .= ' ' . $lineage[ 'familyName' ];
                }
                if (($i == 0) and ($i != ($n - 1))) {
                    // first name and not the last name
                    $html .= '<a href="'.$link.'" '.$robot.' >';
                    $html .= $name;
                    $html .= '</a>';
                } elseif (($i == 0) and ($i == ($n - 1))) {
                    // first name and the last name
                    $html .= $name;
                } elseif (($i != 0) and ($i != ($n - 1))) {
                    // not the first name and not the last name
                    $html .= '<a class="jt-icon-lineage" href="'.$link.'" '.$robot.' >';
                    $html .= '&nbsp;&nbsp;' . $name;
                    $html .= '</a>';
                } elseif (($i != 0) and ($i == ($n - 1))) {
                    // not the first name but the last name
                    $html .= '<span class="jt-icon-lineage">';
                    $html .= '&nbsp;&nbsp;' . $name;
                    $html .= '</span>';
                }
                $i++;
            }
            $html .= '</span></div>';
        }
        return $html;
    }
    private function showPictures()
    {
        $html = '';
        if (($this->person->firstName != Text::_('JT_ALTERNATIVE')) and ($this->person->firstName != null)) {
            $pictures = array();
            $picArray = $this->person->getPictures(false);
            if (count($picArray) != 0) {
                // there are pictures in the array
                $pictures[ 'Sequence']	= (int) $this->params->get('Sequence', 0);
                $pictures[ 'pxHeight']	= (int) $this->params->get('pxHeight', 0);
                $pictures[ 'pxWidth']	= (int) $this->params->get('pxWidth', 0);
                $pictures[ 'indTitle']	= (int) $this->params->get('indTitle', 0);
                $pictures[ 'Title']		= $this->params->get('TitleSlideshow');
                if (($pictures[ 'Sequence'] == 1) or ($pictures[ 'Sequence'] == 2)) {
                    // sequence <> 3: Briask slideshow is asked for
                    $pictures[ 'nextDelay']		= (int) $this->params->get('nextDelay', 0);
                    $pictures[ 'transDelay']	= (int) $this->params->get('transDelay', 0);
                    if (count($picArray) > 1) {
                        // There are more than 1 pictures in array -> slide show can be created
                        $html .= '<noscript><div>ImageSlideShow requires Javascript</div></noscript>';
                        // when height or width is empty or zero, picture will have its own size
                        if (($pictures[ 'pxWidth' ] == 0) or ($pictures[ 'pxHeight' ] == 0)) {
                            $html .= '<ul id="briask-iss99" class="briask-iss">';
                        } else {
                            $html .= '<ul id="briask-iss99" class="briask-iss" style="width:'. $pictures[ 'pxWidth' ].'px;height:'.$pictures[ 'pxHeight' ].'px">';
                        }
                        foreach ($picArray as $picture) {
                            $pictureName = (empty($picture->title)) ? $pictures[ 'Title'] : $picture->title;
                            $picHtml = $this->getPictureHtml($picture, $pictures[ 'pxHeight' ], $pictures[ 'pxWidth' ]);
                            if ($pictures[ 'indTitle'] != 0) {
                                $html .= '<li><img style="float: right;" '.$picHtml.' title="'.$pictureName.'" alt="'.$pictureName.'" /></li>';
                            } else {
                                $html .= '<li><img style="float: right;" '.$picHtml.' alt="'.$pictureName.'" /></li>';
                            }
                        }
                        $html .= '</ul>';
                        $html .= '<script type="text/javascript">';
                        $html .= 'var briaskPics99 = [0]; ';
                        $html .= 'var briaskInstance99 = new briaskISS(99,'.$pictures[ 'Sequence' ].','.$pictures[ 'nextDelay' ].','.$pictures[ 'transDelay' ].', briaskPics99);';
                        $html .= '</script>';
                    } elseif (count($picArray) == 1) {
                        // just one picture in array -> no need for slide show
                        $picture = $picArray[0];
                        $pictureName = (empty($picture->title)) ? $pictures[ 'Title'] : $picture->title;
                        // when height or width is empty or zero, picture will have its own size
                        if (($pictures[ 'pxWidth' ] == 0) or ($pictures[ 'pxHeight' ] == 0)) {
                            $html .= '<img style="float: right;" src="'.$picture->file.'" alt="'.$pictureName.'" />';
                        } else {
                            $picHtml = $this->getPictureHtml($picture, $pictures[ 'pxHeight' ], $pictures[ 'pxWidth' ]);
                            $html .= '<img style="float: right;" '.$picHtml.' alt="'.$pictureName.'" />';
                        }
                        if ($pictures[ 'indTitle'] != 0) {
                            $html .= '<div class="jt-clearfix jt-high-title jt-picture-title">' . $pictureName . '</div>';
                        }
                    }
                } else {
                    // shadowbox
                    $firstPic = true;
                    foreach ($picArray as $picture) {
                        $pictureName = (empty($picture->title)) ? $pictures[ 'Title'] : $picture->title;
                        if ($pictures[ 'indTitle'] != 0) {
                            // create the name to be shown
                            $html .= '<a href="'.$picture->file.'" title="'.$pictureName.'" rel="shadowbox[Joaktree]">';
                        } else {
                            $html .= '<a href="'.$picture->file.'" rel="shadowbox[Joaktree]">';
                        }
                        if ($firstPic) {
                            $html .= '<span title="'.Text::_('JT_PICTURE_SHADOWBOX_TOOLTIP').'">';
                            if (($pictures[ 'pxWidth' ] == 0) or ($pictures[ 'pxHeight' ] == 0)) {
                                $html .= '<img style="float: right;" src="'.$picture->file.'" alt="'.$pictureName.'" />';
                            } else {
                                $picHtml = $this->getPictureHtml($picture, $pictures[ 'pxHeight' ], $pictures[ 'pxWidth' ]);
                                $html .= '<img style="float: right;" '.$picHtml.' alt="'.$pictureName.'" />';
                            }
                            $html .= '</span>';
                        }
                        $html .= '</a>';
                        if (($firstPic) and ($pictures[ 'indTitle'] != 0)) {
                            $html .= '<div title="'.Text::_('JT_PICTURE_SHADOWBOX_TOOLTIP').'" class="jt-clearfix jt-high-title jt-picture-title">' . $pictureName . '</div>';
                        }
                        if ($pictures[ 'Sequence'] == 4) {
                            // For 4: only 1 picture is shown - no slide show
                            break;
                        }
                        if ($firstPic) {
                            $firstPic = false;
                        }
                    }
                }
            }
        }
        return $html;
    }
    private function getPictureHtml(&$picture, $picHeight, $picWidth)
    {
        $html = '';
        // retrieve size of picture
        $imagedata   = GetImageSize($picture->file);
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
