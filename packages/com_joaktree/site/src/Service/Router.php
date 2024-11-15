<?php
/**
 * Joomla! component Joaktree
 * file		front end router - router.php
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

namespace Joaktree\Component\Joaktree\Site\Service;

\defined('_JEXEC') or die;
use Joomla\CMS\Component\Router\RouterBase;

class Router extends RouterBase
{
    /**
     * Build the route for the com_banners component
     *
     * @param   array  $query  An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   3.3
     */
    public function build(&$query)
    {
        /* view: joaktreestart: index-treeId
             joaktreelist : list-treeId / (n-search3) / (p-search4)
             joaktree     : jt-tech-treeId / (personId)
             locations    : locations-tech-treeId
        */
        $segments	= array();
        $view 		= null;

        if (isset($query['view'])) {
            $view = $query['view'];
            unset($query['view']);

            if (isset($query['appId'])) {
                $appId = $query['appId'];
                unset($query['appId']);
            } else {
                $appId = 0;
            }

            if (isset($query['treeId'])) {
                $treeId = $query['treeId'];
                unset($query['treeId']);
            } else {
                $treeId = 0;
            }

            if (isset($query['sourceId'])) {
                $sourceId = $query['sourceId'];
                unset($query['sourceId']);
            } else {
                $sourceId = 0;
            }

            if (isset($query['repoId'])) {
                $repoId = $query['repoId'];
                unset($query['repoId']);
            } else {
                $repoId = 0;
            }

            if (isset($query['mapId'])) {
                $mapId = $query['mapId'];
                unset($query['mapId']);
            } else {
                $mapId = 0;
            }

            if (isset($query['locId'])) {
                $locId = $query['locId'];
                unset($query['locId']);
            } else {
                $locId = 0;
            }

            if (isset($query['distance'])) {
                $distance = $query['distance'];
                unset($query['distance']);
            }

            if (isset($query['tech'])) {
                $tech = $query['tech'];
                unset($query['tech']);
            } else {
                // default is AJAX for joaktree view
                $tech = 'a';
            }

            if (isset($query['action'])) {
                $action = $query['action'];
                unset($query['action']);
            } else {
                $action = 0;
            }

            if (isset($query['filter'])) {
                $filter = $query['filter'];
                unset($query['filter']);
            } else {
                $filter = 0;
            }

            if (isset($query['tmpl'])) {
                $tmpl = $query['tmpl'];
                unset($query['tmpl']);
            } else {
                $tmpl = 0;
            }

            if (isset($query['layout'])) {
                $layout = $query['layout'];
                unset($query['layout']);
            } else {
                $layout = 0;
            }

            // setting segments
            if ($view == 'joaktree') {
                if ($action) {
                    $segments[] = 'jt-'.$layout.'-'.$tech.'-'.$treeId.'-'.$action;
                } else {
                    $segments[] = 'jt-'.$layout.'-'.$tech.'-'.$treeId;
                }
            } elseif ($view == 'personform') {
                if ($action) {
                    if ($tmpl) {
                        $segments[] = 'pf-'.$layout.'-'.$tech.'-'.$treeId.'-'.$action.'-'.$tmpl;
                    } else {
                        $segments[] = 'pf-'.$layout.'-'.$tech.'-'.$treeId.'-'.$action;
                    }
                } else {
                    $segments[] = 'pf-'.$layout.'-'.$tech.'-'.$treeId;
                }
            } elseif ($view == 'locations') {
                $segments[] = 'locations-'.$layout.'-'.$tech.'-'.$treeId.'-'.$filter;
            } elseif ($view == 'list') {
                if ($action) {
                    if ($tmpl) {
                        $segments[] =  'list-'.$layout.'-'.$tech.'-'.$treeId.'-'.$action.'-'.$tmpl;
                    } else {
                        $segments[] =  'list-'.$layout.'-'.$tech.'-'.$treeId.'-'.$action;
                    }
                } else {
                    $segments[] =  'list-'.$layout.'-'.$tech.'-'.$treeId;
                }
            } elseif ($view == 'joaktreestart') {
                $segments[] = 'index-'.$layout.'-'.$treeId.'-'.$filter;
            } elseif ($view == 'ancestors') {
                $segments[] = 'ancestors-'.$layout.'-'.$treeId;
            } elseif ($view == 'descendants') {
                $segments[] = 'descendants-'.$layout.'-'.$treeId;
            } elseif ($view == 'sources') {
                $segments[] = 'sources-'.$layout.'-'.$appId;
            } elseif ($view == 'source') {
                $segments[] = 'source-'.$layout.'-'.$appId.'-'.$sourceId;
            } elseif ($view == 'repositories') {
                $segments[] = 'repositories-'.$layout.'-'.$appId;
            } elseif ($view == 'repository') {
                $segments[] = 'repository-'.$layout.'-'.$appId.'-'.$repoId;
            } elseif ($view == 'changehistory') {
                $segments[] = 'ch-'.$layout.'-'.$tech.'-'.$treeId;
            } elseif ($view == 'map') {
                if (isset($distance)) {
                    $segments[] = 'map-'.$layout.'-'.$mapId.'-'.$locId.'-'.$treeId.'-'.$distance;
                } else {
                    $segments[] =  'map-'.$layout.'-'.$mapId.'-'.$locId.'-'.$treeId;
                }
            } elseif ($view == 'interactivemap') {
                if (isset($distance)) {
                    $segments[] = 'dmp-'.$layout.'-'.$mapId.'-'.$tmpl.'-'.$locId.'-'.$treeId.'-'.$distance;
                } else {
                    $segments[] = 'dmp-'.$layout.'-'.$mapId.'-'.$tmpl.'-'.$locId.'-'.$treeId;
                }
            }

            if (($view == 'source')
               || ($view == 'sources')
               || ($view == 'repository')
               || ($view == 'repositories')
               || ($view == 'changehistory')
            ) {
                $segments[] = $action.'!'.$tmpl;
            }

            if (isset($query['retId'])) {
                $retId = $query['retId'];
                unset($query['retId']);

                if (($view == 'source')
                   || ($view == 'sources')
                   || ($view == 'repository')
                   || ($view == 'repositories')
                   || ($view == 'changehistory')
                ) {
                    $segments[] = $retId;
                }
            }

            if (($view == 'joaktree')
               || ($view == 'personform')
               || ($view == 'ancestors')
               || ($view == 'descendants')
               || ($view == 'map')
               || ($view == 'interactivemap')
            ) {
                if (isset($query['personId'])) {
                    $segments[] = $query['personId'];
                    unset($query['personId']);
                }
            }

            if ($view == 'personform') {
                if (isset($query['relationId'])) {
                    $segments[] = $query['relationId'];
                    unset($query['relationId']);
                }
            }

            if ($view == 'personform') {
                if (isset($query['picture'])) {
                    $segments[] = $query['picture'];
                    unset($query['picture']);
                }
            }

            // searches for joaktreelist
            if ($view == 'list') {
                if (isset($query['search1'])) {
                    $segments[] = 'f-'.str_replace('-', '!', $query['search1']);
                    unset($query['search1']);
                }
                if (isset($query['search2'])) {
                    $segments[] = 's-'.str_replace('-', '!', $query['search2']);
                    unset($query['search3']);
                }
                if (isset($query['search3'])) {
                    $segments[] = 'n-'.str_replace('-', '!', $query['search3']);
                    unset($query['search3']);
                }
                if (isset($query['search4'])) {
                    $segments[] = 'p-'.$query['search4'];
                    unset($query['search4']);
                }
            }
        }
        foreach ($segments as &$segment) {
            $segment = str_replace(':', '-', $segment);
        }
        return $segments;
    }
    /**
     * Parse the segments of a URL.
     *
     * @param   array  $segments  The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     *
     * @since   3.3
     */
    public function parse(&$segments)
    {
        $vars 	= array();

        foreach ($segments as &$segment) {
            $segment = preg_replace('/-/', ':', $segment, 1);
        }
        unset($segment);
        // fetch first segment and pull it apart
        $elements    = explode(':', $segments[0]);
        $count = 1; // count processed segments
        $subelements = [];
        if (isset($elements[1])) {
            $subelements = explode('-', $elements[1]);
        }
        //Handle View and Identifier
        switch ($elements[0]) {
            case 'index':
                $vars['view'] = 'joaktreestart';
                if ($subelements[0]) {
                    $vars['layout']   = $subelements[0];
                }
                $vars['treeId']   = $subelements[1];
                if ($subelements[2]) {
                    $vars['filter']   = $subelements[2];
                }
                break;

            case 'list':
                $vars['view']   = 'list';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                if (isset($subelements[1])) {
                    $vars['tech']   = $subelements[1];
                }
                if (isset($subelements[2])) {
                    $vars['treeId'] = $subelements[2];
                }
                if (isset($subelements[3])) {
                    $vars['action'] = $subelements[3];
                }
                if (isset($subelements[4])) {
                    $vars['tmpl'] = $subelements[4];
                }

                for ($j = 1; $j < count($segments); $j++) {
                    $tmp = explode(':', $segments[$j]);
                    $count++;
                    switch ($tmp[0]) {
                        case 'f':	$vars['search1']  = str_replace('!', '-', $tmp[1]);
                            break;
                        case 's':	$vars['search2']  = str_replace('!', '-', $tmp[1]);
                            break;
                        case 'n':	$vars['search3']  = str_replace('!', '-', $tmp[1]);
                            break;
                        case 'p':	$vars['search4']  = $tmp[1];
                            break;
                    }

                }
                break;

            case 'jt':
                $vars['view']   = 'joaktree';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                if (isset($subelements[1])) {
                    $vars['tech']   = $subelements[1];
                }
                if (isset($subelements[2])) {
                    $vars['treeId'] = $subelements[2];
                }
                if (isset($subelements[3])) {
                    $vars['action'] = $subelements[3];
                }
                if (isset($segments[1])) {
                    $count++;
                    $vars['personId'] = $segments[1];
                }
                break;

            case 'pf':
                $vars['view']   = 'personform';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                if (isset($subelements[1])) {
                    $vars['tech']   = $subelements[1];
                }
                if (isset($subelements[2])) {
                    $vars['treeId'] = $subelements[2];
                }
                if (isset($subelements[3])) {
                    $vars['action'] = $subelements[3];
                }
                if (isset($subelements[4])) {
                    $vars['tmpl'] = $subelements[4];
                }

                if (isset($segments[1])) {
                    $vars['personId'] = $segments[1];
                    $count++;
                }
                if (isset($segments[2])) {
                    $count++;
                    if (strlen($segments[2]) > 20) {
                        $vars['picture'] = $segments[2];
                    } else {
                        $vars['relationId'] = $segments[2];
                    }
                }
                break;

            case 'ch':
                $vars['view']   = 'changehistory';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['technology']   = $subelements[1];
                $vars['treeId'] = $subelements[2];

                if (isset($segments[1])) {
                    $count++;
                    $tmp = explode('!', $segments[1]);
                    $vars['action']   = $tmp[0];
                    if ($tmp[1] == 'component') {
                        $vars['tmpl']     = $tmp[1];
                    }
                }
                if (isset($segments[2])) {
                    $count++;
                    $vars['retId']    = $segments[2];
                }
                break;

            case 'locations':
                $vars['view']   = 'locations';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['tech']   = $subelements[1];
                $vars['treeId'] = $subelements[2];
                if ($subelements[3]) {
                    $vars['filter'] = $subelements[3];
                }
                break;

            case 'ancestors':
                $vars['view'] = 'ancestors';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['treeId']   = $subelements[1];
                if (isset($segments[1])) {
                    $count++;
                    $vars['personId'] = $segments[1];
                }
                break;

            case 'descendants':
                $vars['view'] = 'descendants';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['treeId']   = $subelements[1];
                if (isset($segments[1])) {
                    $count++;
                    $vars['personId'] = $segments[1];
                }
                break;

            case 'sources':
                $vars['view']     = 'sources';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['appId']    = $subelements[1];
                if (isset($segments[1])) {
                    $count++;
                    $tmp = explode('!', $segments[1]);
                    $vars['action']   = $tmp[0];
                    if ($tmp[1] == 'component') {
                        $vars['tmpl']     = $tmp[1];
                    }
                }
                if (isset($segments[2])) {
                    $count++;
                    $vars['retId']    = $segments[2];
                }
                break;

            case 'source':
                $vars['view'] 	  = 'source';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['appId']    = $subelements[1];
                $vars['sourceId'] = $subelements[2];
                if (isset($segments[1])) {
                    $count++;
                    $tmp = explode('!', $segments[1]);
                    $vars['action']   = $tmp[0];
                    if ($tmp[1] == 'component') {
                        $vars['tmpl']     = $tmp[1];
                    }
                }
                if (isset($segments[2])) {
                    $count++;
                    $vars['retId']    = $segments[2];
                }
                break;

            case 'repositories':
                $vars['view']     = 'repositories';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['appId']    = $subelements[1];
                if (isset($segments[1])) {
                    $count++;
                    $tmp = explode('!', $segments[1]);
                    $vars['action']   = $tmp[0];
                    if ($tmp[1] == 'component') {
                        $vars['tmpl']     = $tmp[1];
                    }
                }
                if (isset($segments[2])) {
                    $count++;
                    $vars['retId']    = $segments[2];
                }
                break;

            case 'repository':
                $vars['view'] 	  = 'repository';
                if ($subelements[0]) {
                    $vars['layout'] = $subelements[0];
                }
                $vars['appId']    = $subelements[1];
                $vars['repoId']   = $subelements[2];
                if (isset($segments[1])) {
                    $count++;
                    $tmp = explode('!', $segments[1]);
                    $vars['action']   = $tmp[0];
                    if ($tmp[1] == 'component') {
                        $vars['tmpl']     = $tmp[1];
                    }
                }
                if (isset($segments[2])) {
                    $count++;
                    $vars['retId']    = $segments[2];
                }
                break;
            case 'map':
                $vars['view'] 	  = 'map';
                if ($subelements[0]) {
                    $vars['layout']  = $subelements[0];
                }
                if ($subelements[1]) {
                    $vars['mapId']   = $subelements[1];
                }
                if ($subelements[2]) {
                    $vars['locId']   = $subelements[2];
                }
                if ($subelements[3]) {
                    $vars['treeId']  = $subelements[3];
                }
                if (isset($subelements[4])) {
                    $vars['distance']  = $subelements[4];
                }
                if (isset($segments[1])) {
                    $count++;
                    $vars['personId'] = $segments[1];
                }
                break;
            case 'dmp':
                $vars['view'] 	  = 'interactivemap';
                if ($subelements[0]) {
                    $vars['layout']  = $subelements[0];
                }
                if ($subelements[1]) {
                    $vars['mapId']   = $subelements[1];
                }
                if ($subelements[2]) {
                    $vars['tmpl']    = $subelements[2];
                }
                if ($subelements[3]) {
                    $vars['locId']   = $subelements[3];
                }
                if ($subelements[4]) {
                    $vars['treeId']  = $subelements[4];
                }
                if (isset($subelements[5])) {
                    $vars['distance']  = $subelements[5];
                }
                if (isset($segments[1])) {
                    $count++;
                    $vars['personId'] = $segments[1];
                }
                break;
        }
        // remove processed segments
        for ($i = 1; $i <= $count;$i++) {
            array_shift($segments);
        }
        return $vars;

    }

}
