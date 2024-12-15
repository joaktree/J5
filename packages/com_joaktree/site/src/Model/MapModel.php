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

namespace Joaktree\Component\Joaktree\Site\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Map;

class MapModel extends BaseDatabaseModel
{
    protected $map;

    public function __construct()
    {
        $id = array();
        $id['map']		= Map::getMapId(true);
        $id['location']	= Map::getLocationId(true);
        $id['distance']	= Map::getDistance(true);
        $id['person']	= JoaktreeHelper::getPersonId(false, true);
        $id['tree']		= JoaktreeHelper::getTreeId(false, true);
        $id['app']		= JoaktreeHelper::getApplicationId(false, true);

        $this->map 	= new Map($id);
        parent::__construct();
    }


    public function getMap()
    {
        return $this->map;
    }

    public function getMapView()
    {
        return $this->map->getMapView();
    }

    public function getUIControl($mapHtmlId)
    {
        return $this->map->getUIControl($mapHtmlId);
    }
}
