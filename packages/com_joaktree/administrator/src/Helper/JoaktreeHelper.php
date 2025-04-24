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

namespace Joaktree\Component\Joaktree\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

class JoaktreeHelper
{
    public static function getIdlength()
    {
        // ID length = 20
        return 20;
    }

    public static function addSubmenu($vName)
    {

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_CONTROLPANEL'),
            'index.php?option=com_joaktree',
            $vName == 'default'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_APPLICATIONS'),
            'index.php?option=com_joaktree&view=applications',
            $vName == 'applications'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_FAMILYTREES'),
            'index.php?option=com_joaktree&view=trees',
            $vName == 'trees'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_PERSONS'),
            'index.php?option=com_joaktree&view=persons',
            $vName == 'persons'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_MAPS'),
            'index.php?option=com_joaktree&view=maps',
            $vName == 'maps'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_PERSON_NAMEDISPLAY'),
            'index.php?option=com_joaktree&view=settings&layout=personname',
            $vName == 'personname'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_PERSON_EVENTDISPLAY'),
            'index.php?option=com_joaktree&view=settings&layout=personevent',
            $vName == 'personevent'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_RELATION_EVENTDISPLAY'),
            'index.php?option=com_joaktree&view=settings&layout=relationevent',
            $vName == 'relationevent'
        );

        Sidebar::addEntry(
            Text::_('JT_SUBMENU_THEMES'),
            'index.php?option=com_joaktree&view=themes',
            $vName == 'themes'
        );
    }

    /*
    ** function for retrieving version number from config.xml
    */
    public static function getJoaktreeVersion()
    {
        // get the folder and xml-files
        $folder = JPATH_ADMINISTRATOR .'/components/com_joaktree';

        if (is_dir($folder)) {
            $xmlFilesInDir = Folder::files($folder, '.xml$');
        } else {
            $folder = JPATH_SITE .'/components/com_joaktree';
            if (is_dir($folder)) {
                $xmlFilesInDir = Folder::files($folder, '.xml$');
            } else {
                $xmlFilesInDir = null;
            }
        }

        // loop through the xml-files
        //$xml_items ='';
        $xml_items[] = array(); // RRG 21-01-2017 PHP 7.1
        if (count($xmlFilesInDir)) {
            foreach ($xmlFilesInDir as $xmlfile) {
                if ($data = Installer::parseXMLInstallFile($folder.'/'.$xmlfile)) {
                    foreach ($data as $key => $value) {
                        $xml_items[$key] = $value;
                    }
                }
            }
        }

        // return the found version
        if (isset($xml_items['version']) && $xml_items['version'] != '') {
            return $xml_items['version'];
        } else {
            return '';
        }
    }

    public static function jsfile()
    {

        return 'media/com_joaktree/js/joaktree_admin.js';
    }

    public static function joaktreecss()
    {
        $ds = '/';
        return 'media/com_joaktree/css/joaktree_admin.css';
    }

    public static function getActions($asset = 'com_joaktree')
    {
        $user	= Factory::getApplication()->getIdentity();
        $result	= new Registry();

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $asset));
        }

        // special treatement for media - we take the authorisation from media manager
        $result->set('media.create', $user->authorise('core.create', 'com_media'));

        return $result;
    }

    public static function getApplications()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getQuery(true);

        // retrieve the names
        $query->select(' id AS value ');
        $query->select(' title AS text ');
        $query->from(' #__joaktree_applications ');

        $db->setQuery($query);
        $applications = $db->loadObjectList();

        return $applications;
    }

    public static function getTrees()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getQuery(true);

        // retrieve the names
        $query->select(' id AS value ');
        $query->select(' name AS text ');
        $query->from(' #__joaktree_trees ');
        // $query->where($db->quotename('published').' = 1');
        $db->setQuery($query);
        $trees = $db->loadObjectList();

        return $trees;
    }
    public function getThemes()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getQuery(true);

        // retrieve the names
        $query->select(' id AS value ');
        $query->select(' name AS text ');
        $query->from(' #__joaktree_themes ');

        $db->setQuery($query);
        $themes = $db->loadObjectList();

        return $themes;
    }

    public static function getThemeName($id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getQuery(true);
        $name = '';

        // retrieve the name
        $query->select(' name ');
        $query->from(' #__joaktree_themes ');
        $query->where(' id   = :id');
        $query->bind(':id', $id, \Joomla\Database\ParameterType::INTEGER);
        $db->setQuery($query);
        $name = $db->loadResult();

        return $name;
    }

    public static function getJTParams($app_id)
    {
        static $_params;
        static $localAppId;

        if ((!isset($_params)) || ($localAppId != $app_id)) {
            // Load the parameters.
            $_params = ComponentHelper::getParams('com_joaktree') ;

            if (!empty($app_id)) {
                // retrieve the parameters of the application source
                $appParams  = self::getApplicationParams($app_id);

                // merge all parameters
                $_params->merge($appParams);

                //	set the local app id
                $localAppId = $app_id;
            }
        }

        return $_params;
    }

    public static function getApplicationParams($app_id)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getQuery(true);

        // retrieve the app parameters
        $query->select(' japp.params ');
        $query->from(' #__joaktree_applications  japp ');
        $query->where(' japp.id =  :appid');
        $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);

        $db->setQuery($query);
        $appSource = $db->loadObject();

        $registry = new Registry();

        if (is_object($appSource)) {
            // load parameters into registry object
            $registry->loadString($appSource->params, 'JSON');
            unset($appSource->params);
        }

        return $registry;
    }

    private static function _getConcatenatedName($attribs)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        return $query->concatenate($attribs, ' ');
    }

    public static function getConcatenatedFamilyName()
    {
        static $concatTxt;

        if (empty($concatTxt)) {
            $attribs = array();
            $attribs[] = 'jpn.namePreposition';
            $attribs[] = 'jpn.familyName';
            $concatTxt = self::_getConcatenatedName($attribs);
        }

        return ' '.$concatTxt.' ';
    }

    public static function getConcatenatedFullName()
    {
        static $concatTxt;

        if (empty($concatTxt)) {
            $attribs = array();
            $attribs[] = 'jpn.firstName';
            $attribs[] = 'jpn.namePreposition';
            $attribs[] = 'jpn.familyName';
            $concatTxt = self::_getConcatenatedName($attribs);
        }

        return ' '.$concatTxt.' ';
    }

    public static function addLog($msg, $cat = 'joaktreeged')
    {
        Log::addLogger(array('text_file' => $cat.'.log.php'), Log::INFO, array($cat));
        Log::add($msg, Log::INFO, $cat);
    }
}
