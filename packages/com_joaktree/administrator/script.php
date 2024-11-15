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
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;

class com_joaktreeInstallerScript
{
	private $min_joomla_version      = '4.0';
	private $min_php_version         = '8.0';
	private $name                    = 'Joaktree';
	private $exttype                 = 'component';
	private $extname                 = 'joaktree';
	private $previous_version        = '';
	private $dir           = null;
	private $lang = null;
	private $installerName = 'joaktreeinstaller';
	public function __construct()
	{
		$this->dir = __DIR__;
	}
    
    function uninstall($parent)
    {
		$db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
			->delete('#__extensions')
		    ->where($db->quoteName('element') . ' like "%joaktree%"');
		$db->setQuery($query);
		$result = $db->execute();
        $obsoleteFiles = [
            JPATH_SITE.'/plugins/content/joaktree',JPATH_SITE.'/plugins/editors-xtd/joaktreemap', 
            JPATH_SITE.'/plugins/editors-xtd/joaktreeperson',JPATH_SITE.'/plugins/finder/joaktree',
            JPATH_SITE.'/modules/mod_joaktree_lastpersonsviewed',JPATH_SITE.'/modules/mod_joaktree_related_items',
            JPATH_SITE.'/modules/mod_joaktree_show_update',JPATH_SITE.'/modules/mod_joaktree_todaymanyyearsago',
            JPATH_SITE.'/media/com_joaktree',
            JPATH_SITE.'/languages/en-GB/com_joaktree.ini',
            JPATH_SITE.'/languages/en-GB/mod_joaktree_lastpersonsviewed.ini',JPATH_SITE.'/languages/en-GB/mod_joaktree_lastpersonsviewed.sys.ini',
            JPATH_SITE.'/languages/en-GB/mod_joaktree_related_items.ini',JPATH_SITE.'/languages/en-GB/mod_joaktree_related_items.sys.ini',
            JPATH_SITE.'/languages/en-GB/mod_joaktree_show_update.ini',JPATH_SITE.'/languages/en-GB/mod_joaktree_show_update.sys.ini',
            JPATH_SITE. '/languages/en-GB/mod_joaktree_todaymanyyearsago.ini',JPATH_SITE.'/languages/en-GB/mod_joaktree_todaymanyyearsago.sys.ini',
            JPATH_SITE.'/languages/fr-FR/com_joaktree.ini',
            JPATH_SITE.'/languages/fr-FR/mod_joaktree_lastpersonsviewed.ini',JPATH_SITE.'/languages/fr-FR/mod_joaktree_lastpersonsviewed.sys.ini',
            JPATH_SITE.'/languages/fr-FR/mod_joaktree_related_items.ini',JPATH_SITE.'/languages/fr-FR/mod_joaktree_related_items.sys.ini',
            JPATH_SITE.'/languages/fr-FR/mod_joaktree_show_update.ini',JPATH_SITE.'/languages/fr-FR/mod_joaktree_show_update.sys.ini',
            JPATH_SITE.'/languages/fr-FR/mod_joaktree_todaymanyyearsago.ini',JPATH_SITE.'/languages/fr-FR/mod_joaktree_todaymanyyearsago.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/joaktreeinstaller.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/com_joaktree.ini',JPATH_ADMINISTRATOR.'/languages/en-GB/com_joaktree.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/com_joaktree.services.ini',JPATH_ADMINISTRATOR.'/languages/en-GB/com_joaktree.gedcom.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/plg_content_joaktree.ini',JPATH_ADMINISTRATOR.'/languages/en-GB/plg_content_joaktree.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/plg_editors-xtd_joaktreeperson.ini',JPATH_ADMINISTRATOR.'/languages/en-GB/plg_editors-xtd_joaktreeperson.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/plg_editors-xtd_joaktreemap.ini',JPATH_ADMINISTRATOR.'/languages/en-GB/plg_editors-xtd_joaktreemap.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/en-GB/plg_finder_joaktree.ini',JPATH_ADMINISTRATOR.'/languages/en-GB/plg_finder_joaktree.sys.ini',
            JPATH_ADMINISTRATOR. '/languages/fr-FR/joaktreeinstaller.ini',
            JPATH_ADMINISTRATOR.'/languages/fr-FR/com_joaktree.ini',JPATH_ADMINISTRATOR.'/languages/fr-FR/com_joaktree.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/fr-FR/com_joaktree.services.ini',JPATH_ADMINISTRATOR.'/languages/fr-FR/com_joaktree.gedcom.ini',
            JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_content_joaktree.ini',JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_content_joaktree.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_editors-xtd_joaktreeperson.ini',JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_editors-xtd_joaktreeperson.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_editors-xtd_joaktreemap.ini',JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_editors-xtd_joaktreemap.sys.ini',
            JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_finder_joaktree.ini',JPATH_ADMINISTRATOR.'/languages/fr-FR/plg_finder_joaktree.sys.ini',
            ];
            $this->delete($obsoleteFiles);
            
		Factory::getApplication()->enqueueMessage('Joaktree package uninstalled.', 'notice');
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