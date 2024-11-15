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
		$obsloteFolders = [
            '/plugins/content/joaktree','/plugins/editors-xtd/joaktreemap', 
            '/plugins/editors-xtd/joaktreeperson','/plugins/finder/joaktree',
            '/modules/mod_joaktree_lastpersonsviewed','/modules/mod_joaktree_related_items',
            '/modules/mod_joaktree_show_update','/modules/mod_joaktree_todaymanyyearsago',
            '/media/com_joaktree',
            '/languages/en-GB/com_joaktree.ini',
            '/languages/en-GB/mod_joaktree_lastpersonsviewed.ini','/languages/en-GB/mod_joaktree_lastpersonsviewed.sys.ini',
            '/languages/en-GB/mod_joaktree_related_items.ini','/languages/en-GB/mod_joaktree_related_items.sys.ini',
            '/languages/en-GB/mod_joaktree_show_update.ini','/languages/en-GB/mod_joaktree_show_update.sys.ini',
            '/languages/en-GB/mod_joaktree_todaymanyyearsago.ini','/languages/en-GB/mod_joaktree_todaymanyyearsago.sys.ini',
            '/languages/fr-FR/com_joaktree.ini',
            '/languages/fr-FR/mod_joaktree_lastpersonsviewed.ini','/languages/fr-FR/mod_joaktree_lastpersonsviewed.sys.ini',
            '/languages/fr-FR/mod_joaktree_related_items.ini','/languages/fr-FR/mod_joaktree_related_items.sys.ini',
            '/languages/fr-FR/mod_joaktree_show_update.ini','/languages/fr-FR/mod_joaktree_show_update.sys.ini',
            '/languages/fr-FR/mod_joaktree_todaymanyyearsago.ini','/languages/fr-FR/mod_joaktree_todaymanyyearsago.sys.ini',
            '/administrator/languages/en-GB/joaktreeinstaller.ini',
            '/administrator/languages/en-GB/com_joaktree.ini','/administrator/languages/en-GB/com_joaktree.sys.ini',
            '/administrator/languages/en-GB/com_joaktree.services.ini','/administrator/languages/en-GB/com_joaktree.gedcom.ini',
            '/administrator/languages/en-GB/plg_content_joaktree.ini','/administrator/languages/en-GB/plg_content_joaktree.sys.ini',
            '/administrator/languages/en-GB/plg_editors-xtd_joaktreeperson.ini','/administrator/languages/en-GB/plg_editors-xtd_joaktreeperson.sys.ini',
            '/administrator/languages/en-GB/plg_editors-xtd_joaktreemap.ini','/administrator/languages/en-GB/plg_editors-xtd_joaktreemap.sys.ini',
            '/administrator/languages/en-GB/plg_finder_joaktree.ini','/administrator/languages/en-GB/plg_finder_joaktree.sys.ini',
            '/administrator/languages/fr-FR/joaktreeinstaller.ini',
            '/administrator/languages/fr-FR/com_joaktree.ini','/administrator/languages/fr-FR/com_joaktree.sys.ini',
            '/administrator/languages/fr-FR/com_joaktree.services.ini','/administrator/languages/fr-FR/com_joaktree.gedcom.ini',
            '/administrator/languages/fr-FR/plg_content_joaktree.ini','/administrator/languages/fr-FR/plg_content_joaktree.sys.ini',
            '/administrator/languages/fr-FR/plg_editors-xtd_joaktreeperson.ini','/administrator/languages/fr-FR/plg_editors-xtd_joaktreeperson.sys.ini',
            '/administrator/languages/fr-FR/plg_editors-xtd_joaktreemap.ini','/administrator/languages/fr-FR/plg_editors-xtd_joaktreemap.sys.ini',
            '/administrator/languages/fr-FR/plg_finder_joaktree.ini','/administrator/languages/fr-FR/plg_finder_joaktree.sys.ini',
            ];
		// Remove plugins' files.
		foreach ($obsloteFolders as $folder)
		{
			$f = JPATH_SITE . $folder;

			if (!@file_exists($f) || !is_dir($f) || is_link($f))
			{
				continue;
			}

			Folder::delete($f);
		}
		
		Factory::getApplication()->enqueueMessage('Joaktree package uninstalled', 'notice');
    }
}