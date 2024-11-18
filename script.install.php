<?php
/**
 * Joomla! component Joaktree
 * file		script.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud 
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class PlgSystemJoaktreeInstallerInstallerScript
{
    private $min_joomla_version     = '4.0.0';
    private $min_php_version        = '8.0';
    private $name                   = 'Joaktree';
    private $dir                    = null;
    private $lang                   = null;
    private $previous_version        = "";
    private $installerName          = 'joaktreeinstaller';
    private $extname                 = 'joaktree';
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->lang = Factory::getApplication()->getLanguage();
        $this->lang->load('joaktreeinstaller');
    }
    public function uninstall($parent)
    {
        // Initialize the database
        $db 			= Factory::getContainer()->get(DatabaseInterface::class);
        $update_queries = array();
        $application 	= Factory::getApplication();

        // Do not drop tables, because they contain user settings
        // $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_admin_persons ';
        // $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_applications ';
        // $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_display_settings ';
        // $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_themes ';
        // $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_trees ';

        // Drop the following tables
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_citations ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_citations ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_documents ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_gedcom_objectlines ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_gedcom_objects ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_locations ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_logremovals ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_logs ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_maps ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_notes ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_persons ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_person_documents ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_person_events ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_person_names ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_person_notes ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_registry_items ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_relations ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_relation_events ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_relation_notes ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_repositories ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_sources ';
        $update_queries[] = 'DROP TABLE IF EXISTS #__joaktree_tree_persons ';

        // Perform all queries - we don't care if it fails
        foreach ($update_queries as $query) {
            $db->setquery($query);
            //$db->query();
            $db->execute();
        }
        Factory::getApplication()->enqueueMessage('NOTE: Five database tables were NOT removed to allow for upgrades', 'notice') ;

        return true;
    }
    public function preflight($route, $installer)
    {
        // To prevent installer from running twice if installing multiple extensions
        if (! file_exists($this->dir . '/' . $this->installerName . '.xml')) {
            return true;
        }
        if (! $this->passMinimumJoomlaVersion()) {
            $this->uninstallInstaller();
            return false;
        }

        if (! $this->passMinimumPHPVersion()) {
            $this->uninstallInstaller();
            return false;
        }
        $this->previous_version = null;

        if (file_exists(JPATH_ADMINISTRATOR . '/components/com_joaktree/joaktree.xml')) {
            $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_joaktree/joaktree.xml');
            $this->previous_version = $xml->version;
        }
        // To prevent XML not found error
        $this->createExtensionRoot();

        return true;
    }
    private function createExtensionRoot()
    {
        $destination = JPATH_PLUGINS . '/system/' . $this->installerName;

        Folder::create($destination);

        File::copy(
            $this->dir . '/' . $this->installerName . '.xml',
            $destination . '/' . $this->installerName . '.xml'
        );
    }

    public function postflight($route, $installer)
    {
        $this->lang->load($this->extname);

        if (! in_array($route, ['install', 'update'])) {
            return true;
        }

        // To prevent installer from running twice if installing multiple extensions
        if (! file_exists($this->dir . '/' . $this->installerName . '.xml')) {
            return true;
        }

        // Then install the rest of the packages
        if (! $this->installPackages()) {
            // Uninstall this installer
            $this->uninstallInstaller();

            return false;
        }
        $this->postInstall();
        Factory::getApplication()->enqueueMessage(Text::_('PLG_JOAKTREE_INSTALL_END'), 'notice');

        // Uninstall this installer
        $this->uninstallInstaller();

        return true;
    }
    private function postInstall()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // MYSQL 8 : ALTER IGNORE deprecated
		$sql = "SHOW COLUMNS FROM #__joaktree_trees";
		$db->setQuery($sql);
		$cols = @$db->loadObjectList("Field");

		if (!array_key_exists("catid", $cols)) {
            $sql = "ALTER TABLE #__joaktree_trees ADD catid int(11) NULL AFTER  root_person_id ";
			$db->setQuery($sql);
			$db->execute();
        }
		$obsoleteFiles = [
        // from 1.5
			JPATH_ADMINISTRATOR."/components/com_joaktree/conrollers",
			JPATH_ADMINISTRATOR."/components/com_joaktree/help",
			JPATH_ADMINISTRATOR."/components/com_joaktree/helpers",
			JPATH_ADMINISTRATOR."/components/com_joaktree/models",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/_notes",            
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/fields",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/geocode",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/images",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/interactivemap",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/provider",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/staticmap",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/exception.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/geocode.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/interactivemap.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/service.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/staticmap.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/tables",
            JPATH_ADMINISTRATOR."/components/com_joaktree/services/views",
            JPATH_ADMINISTRATOR."/components/com_joaktree/controller.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/joaktree.php", 
            JPATH_SITE."/components/com_joaktree/controllers", 
            JPATH_SITE."/components/com_joaktree/helper", 
            JPATH_SITE."/components/com_joaktree/models", 
            JPATH_SITE."/components/com_joaktree/views", 
            JPATH_SITE."/components/com_joaktree/themes/_notes", 
            JPATH_SITE."/components/com_joaktree/themes/Blue/_notes", 
            JPATH_SITE."/components/com_joaktree/themes/Green/_notes", 
            JPATH_SITE."/components/com_joaktree/themes/Jaune/_notes", 
            JPATH_SITE."/components/com_joaktree/themes/Joaktree/_notes", 
            JPATH_SITE."/components/com_joaktree/themes/Red/_notes", 
            JPATH_SITE."/modules/mod_joaktree_lastpersonsviewed/mod_joaktree_lastpersonsviewed.php", 
            JPATH_SITE."/modules/mod_joaktree_lastpersonsviewed/helper.php",
            JPATH_SITE."/modules/mod_joaktree_related_items/mod_joaktree_related_items.php", 
            JPATH_SITE."/modules/mod_joaktree_related_items/helper.php",
            JPATH_SITE."/modules/mod_joaktree_show_update/mod_joaktree_show_update.php", 
            JPATH_SITE."/modules/mod_joaktree_show_update/helper.php",
            JPATH_SITE."/modules/mod_joaktree_todaymanyyearsago/mod_joaktree_todaymanyyearsago.php", 
            JPATH_SITE."/modules/mod_joaktree_todaymanyyearsago/helper.php",
            JPATH_SITE."/plugins/editors-xtd/joaktree_link",
            JPATH_SITE."/plugins/editors-xtd/joaktree_map",
            JPATH_SITE."/plugins/search/joaktree",
        // from 2.0.0
			JPATH_ADMINISTRATOR."/components/com_joaktree/src/Mapservice/Staticmap/Mapquest.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/src/Mapservice/Interactivemap/Mapquest.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/src/Mapservice/Geocode/Yahoo.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/src/Mapservice/Provider/Yahoo.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/src/Mapservice/Provider/Mapquest.php",
            JPATH_ADMINISTRATOR."/components/com_joaktree/src/Mapservice/Geocode/Geocode.php",
			JPATH_ADMINISTRATOR."/components/com_joaktree/assets", // assets to media
            JPATH_SITE."/components/com_joaktree/assets", // assets to media
            JPATH_SITE."/components/com_joaktree/tmpl/start", // duplicate with joaktreestart
            JPATH_SITE."/components/com_joaktree/src/View/Start", // duplicate with joaktreestart
         // 2.0.2 : rename editors-xtd/joaktree to editors-xtd/joaktreeperson   
            JPATH_SITE."/plugins/editors-xtd/joaktree",
            JPATH_SITE."/media/com_joaktree/js/admin-article-joaktree.js",
			JPATH_ADMINISTRATOR."/language/fr-FR/plg_editors-xtd_joaktree.sys.ini",
			JPATH_ADMINISTRATOR."/language/fr-FR/plg_editors-xtd_joaktree.ini",
			JPATH_ADMINISTRATOR."/language/en-GB/plg_editors-xtd_joaktree.sys.ini",
			JPATH_ADMINISTRATOR."/language/en-GB/plg_editors-xtd_joaktree.ini",
			JPATH_ADMINISTRATOR."/components/com_joaktree/src/Table/JMFPKTable.php",
		];
		$this->delete($obsoleteFiles);
        // remove 1.5 obsolete plugins
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('joaktree_link'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('joaktree_map'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('joaktree'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('search'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        // 2.0.2 : rename editors-xtd/joaktree to editors-xtd/joaktreeperson
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('joaktree'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();

        // activate all joaktree plugins except finder
        $conditions = array(
            $db->qn('type') . ' = ' . $db->q('plugin'),
            $db->qn('element') . ' LIKE ' . $db->quote('joaktree%'),
            $db->qn('folder') . ' NOT LIKE ' . $db->quote('finder') 
        );
        $fields = array($db->qn('enabled') . ' = 1');

        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (\RuntimeException $e) {
        }

    }

    // Check if Joomla version passes minimum requirement
    private function passMinimumJoomlaVersion()
    {
        $j = new Version();
        $version = $j->getShortVersion();
        if (version_compare($version, $this->min_joomla_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf(
                    'NOT_COMPATIBLE_UPDATE',
                    '<strong>' . JVERSION . '</strong>',
                    '<strong>' . $this->min_joomla_version . '</strong>'
                ),
                'error'
            );

            return false;
        }

        return true;
    }

    // Check if PHP version passes minimum requirement
    private function passMinimumPHPVersion()
    {

        if (version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf(
                    'NOT_COMPATIBLE_PHP',
                    '<strong>' . PHP_VERSION . '</strong>',
                    '<strong>' . $this->min_php_version . '</strong>'
                ),
                'error'
            );

            return false;
        }

        return true;
    }
    private function installPackages()
    {
        $packages = Folder::folders($this->dir . '/packages');

        foreach ($packages as $package) {
            if (! $this->installPackage($package)) {
                return false;
            }
        }
        // enable plugins
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $conditions = array(
            $db->qn('type') . ' = ' . $db->q('plugin'),
            $db->qn('element') . ' = ' . $db->quote('joaktree')
        );
        $fields = array($db->qn('enabled') . ' = 1');

        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (RuntimeException $e) {
            Log::add('unable to enable Plugins Joaktree', Log::ERROR, 'jerror');
        }

        return true;
    }
    private function installPackage($package)
    {
        $tmpInstaller = new Installer();
        $installed = $tmpInstaller->install($this->dir . '/packages/' . $package);
        return $installed;
    }

    private function uninstallInstaller()
    {
        if (! is_dir(JPATH_PLUGINS . '/system/' . $this->installerName)) {
            return;
        }
        $this->delete([
            JPATH_PLUGINS . '/system/' . $this->installerName . '/language',
            JPATH_PLUGINS . '/system/' . $this->installerName,
        ]);
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote($this->installerName))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        Factory::getCache()->clean('_system');
    }

    public function delete($files = [])
    {
        foreach ($files as $file) {
            if (is_dir($file)) {
                Folder::delete($file);
            }

            if (is_file($file)) {
                File::delete($file);
            }
        }
    }
}
