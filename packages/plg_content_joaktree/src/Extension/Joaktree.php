<?php
/**
 * Joomla! plugin Joaktree content
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */

namespace Joaktree\Plugin\Content\Joaktree\Extension;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joaktree\Component\Joaktree\Site\Helper\Person;
use Joaktree\Component\Joaktree\Site\Helper\Map;

final class Joaktree extends CMSPlugin
{
    use DatabaseAwareTrait;

    public static function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        $canProceed = strpos($context, 'com_content');
        if ($canProceed === false) {
            return;
        }

        $jtlang = Factory::getLanguage();
        $jtlang->load('com_joaktree');
        $jtlang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);

        $regex_one		= '/({joaktree\s*)(.*?)(})/si';
        $regex_all		= '/{joaktree\s*.*?}/si';
        $matchResult 	= array();
        $count_matches	= preg_match_all($regex_all, $article->text, $matchResult, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

        if ($count_matches) {
            $linkBase = 'index.php?option=com_joaktree';
            $matches = array_shift($matchResult);
            $iMatch = 0;

            foreach ($matches as $match) {
                $iMatch++;
                $replaceText = '';
                $matchString = array_shift($match);

                preg_match($regex_one, $matchString, $joaktree_index_string);
                $joaktree_index = explode("|", $joaktree_index_string[2]);

                $view 		= array_shift($joaktree_index);
                $gedcom		= array_shift($joaktree_index);
                $gedcomType	= ((string)(int)$gedcom == $gedcom) ? 'int' : 'str';
                $id			= array_shift($joaktree_index);
                $idType		= ((string)(int)$id == $id) ? 'int' : 'str';

                $displayType = '';
                if (count($joaktree_index)) {
                    $displayType =  array_shift($joaktree_index);
                }
                $displayType = ($displayType == 'extended') ? $displayType : 'short';

                $displayText = '';
                if (count($joaktree_index)) {
                    $displayText =  array_shift($joaktree_index);
                }

                // get the link
                switch ($view) {
                    case "map": $map = self::getMap($gedcom, $gedcomType, $id, $idType);
                        if (!$map) {
                            return false;
                        }

                        $dispid = 'jt-map-id-'.$iMatch;
                        if ($map->params['service'] == 'staticmap') {
                            if ($map->getService()->getProvider()->getName() == 'OpenStreetMap') {
                                $ascript = $map->getMapScript();
                                $replaceText .=  $ascript;
                            } else {
                                $href 	    = $map->getMapView();
                                $replaceText .= '<img id="'.$dispid.'" src="'.$href.'" alt="no information"/>';
                            }
                        }
                        if ($map->params['service'] == 'interactivemap') {
                            if ($map->getService()->getProvider()->getName() == 'OpenStreetMap') {
                                $ascript = $map->getMapScript();
                                $replaceText .=  $ascript;
                            } else {
                                $width1 = (($map->params['width']) ? 'width: '.(int) $map->params['width'].'px;' : '');
                                $width2 = (($map->params['width']) ? 'width="'.(int) $map->params['width'].'px"' : '');
                                $height = (($map->params['height']) ? (int) $map->params['height'] : 450) + 40;
                                $href   = 'index.php?option=com_joaktree'
                                     .'&view=interactivemap'
                                     .'&tmpl=component'
                                     .'&format=raw'
                                     .'&mapId='.$map->params['id'];

                                $replaceText .= '<div style="'.$width1.' height: '.$height.'px; " >';
                                $replaceText .= '<iframe id="'.$dispid.'" src="'.$href.'" height="'.$height.'px" '.$width2.' style="border:1px solid #dddddd;">';
                                $replaceText .= '</iframe></div>';
                            }
                        }

                        if ($map->params['ui_control']) {
                            $replaceText .= $map->getUIControl($dispid);
                        }

                        break;

                    case "person": // continue
                    default: $person = self::getPerson($gedcom, $gedcomType, $id);
                        if (!$person) {
                            return false;
                        }
                        $dispid = 'jt-pers-id-'.$iMatch;

                        $href =  Route::_(
                            $linkBase
                                  .'&view='.(($view == 'person') ? 'joaktree' : $view)
                                  .'&Itemid='.$person->menuItemId
                                  .'&treeId='.$person->tree_id
                                  .'&personId='.$person->app_id.'!'.$person->id
                        );
                        $displayText = ($displayText == '') ? $person->fullName : $displayText;
                        $replaceText .= '<a id="'.$dispid.'" href="'.$href.'">'.$displayText.'</a>';

                        if ($displayType == 'extended') {
                            $birth  	= '';
                            $death  	= '';
                            $indBirth = false;
                            $indDeath = false;

                            if ($person->birthDate) {
                                $birth .= Text::_('BIRT').'&nbsp;';
                                $birth .= $person->birthDate;
                                $indBirth = true;
                            }

                            if ($person->deathDate) {
                                $death .= Text::_('DEAT').'&nbsp;';
                                $death .= $person->deathDate;
                                $indDeath = true;
                            }

                            $replaceText .= ($indBirth || $indDeath) ? '&nbsp;(' : '';
                            $replaceText .= ($indBirth) ? $birth : '';
                            $replaceText .= ($indBirth && $indDeath) ? '&nbsp;-&nbsp;' : '';
                            $replaceText .= ($indDeath) ? $death : '';
                            $replaceText .= ($indBirth || $indDeath) ? ')' : '';
                        }
                        break;
                }

                $article->text = preg_replace($regex_all, $replaceText, $article->text, 1);
            }
        }
        return true;
    }

    private static function getPerson($gedcom, $gedcomType, $personId)
    {
        $id			= array();

        $id[ 'app_id' ] = self::getAppId($gedcom, $gedcomType);
        $id[ 'person_id' ] = $personId;
        if (!$id[ 'app_id' ]) {
            return false;
        }

        $app = Factory::getApplication('site');
        $app->getInput()->set('personId', $id[ 'app_id' ].'!'.$id[ 'person_id' ]);

        $person = new Person($id, 'ancestor'); // retrieve dates
        //$person = new Person($id);           // no dates
        return $person;
    }

    private static function getMap($gedcom, $gedcomType, $id, $idType)
    {
        $appId   = self::getAppId($gedcom, $gedcomType);
        $mapId   = self::getTreeId($id, $idType);
        if (!$appId) {
            return false;
        }
        if (!$mapId) {
            return false;
        }

        $app = Factory::getApplication('site');
        $app->getInput()->set('mapId', $mapId);

        $id = array();
        $id['map'] = $mapId;
        $map = new Map($id);
        return $map;
    }

    private static function getAppId($gedcom, $gedcomType)
    {
        $db 		= Factory::getContainer()->get(DatabaseInterface::class);
        $query 		= $db->getQuery(true);

        if ($gedcomType == 'int') {
            return $gedcom;
        } else {
            $query->select(' id ');
            $query->from(' #__joaktree_applications ');
            $query->where(' LOWER(title) = LOWER(:gedcom) ');
            $query->bind(':gedcom',$gedcom,\Joomla\Database\ParameterType::STRING);
            $db->setQuery($query);
            $result = $db->loadResult();
            return ($result) ? $result : false;
        }
    }

    private static function getTreeId($id, $idType)
    {
        $db 		= Factory::getContainer()->get(DatabaseInterface::class);
        $query 		= $db->getQuery(true);

        if ($idType == 'int') {
            return $id;
        } else {
            $query->select(' id ');
            $query->from(' #__joaktree_trees ');
            $query->where(' LOWER(name) = LOWER(:name) ');
            $query->bind(':name',$id,\Joomla\Database\ParameterType::STRING);
            $db->setQuery($query);
            $result = $db->loadResult();
            return ($result) ? $result : false;
        }
    }
}
