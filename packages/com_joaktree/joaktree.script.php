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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Installer\Installer;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\Database\DatabaseInterface;

/* 
 * An optional script file (PHP code that is run before, during and/or after installation, 
 * uninstallation and upgrading) can be defined using a <scriptfile> element. 
 * This file should contain a class named "<element_name>IntallerScript" where <element_name> 
 * is the name of your extension (e.g. com_componentname, mod_modulename, etc.). 
 * Plugins requires to state the group (e.g. plgsystempluginname). 
 * 
 * The structure of the class is as follows:
 */

class com_joaktreeInstallerScript
{
        /**
         * Called on installation
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         */
        public function install($installer){
			// New installation
			//$version = (string) Installer::getInstance()->getManifest()->version;
			//$version = '2.0.0'; //JInstallerAdapter::getManifest()->version;
			//$version = $parent->get('manifest')->version;
			$version = (string) Installer::getInstance()->getManifest()->version;
			
			// Initialize the database
			$db 			= Factory::getContainer()->get(DatabaseInterface::class);
        	$update_queries = array();
			$application 	= Factory::getApplication();
			
			// Table joaktree_tree_persons
			// end: joaktree_tree_persons
        	
			// Perform all queries
			foreach( $update_queries as $query ) {
			    $db->setquery( $query );
			    $db->execute();
			}

			Factory::getApplication()->enqueueMessage( 'Database installation script is finished for version '.$version, 'notice' ) ;			
        }
 
        /**
         * Called on update
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         */
        public function update($installer) {
        	// upgrade
			//$new_version = (string) Installer::getInstance()->getManifest()->version;
        	//$new_version = $parent->getManifest()->version; //JInstallerAdapter::getManifest()->version;
			$new_version = (string) Installer::getInstance()->getManifest()->version;
			// Initialize the database
			$db 			= Factory::getContainer()->get(DatabaseInterface::class);
        	$update_queries = array();
			$application 	= Factory::getApplication();
			
			// current version in database
			$query 			= $db->getquery(true);
			$query->select(' value ');
			$query->from(  ' #__joaktree_registry_items ');
			$query->where( ' regkey = '.$db->quote('VERSION').' ');			
			$db->setquery($query);			
			$old_version = $db->loadResult();
			
			if (empty($old_version)) {
				$old_version = 'unknown';
				$update_queries[] = 'INSERT IGNORE INTO #__joaktree_registry_items (regkey, value) VALUES ("VERSION", "'.$new_version.'" )';
			} else {
				$update_queries[] = 'UPDATE #__joaktree_registry_items SET value = "'.$new_version.'" WHERE regkey = "VERSION" ';
			}
			
			

				// Table joaktree_persons
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_persons '
				   .'CHANGE indNote indNote varchar(1) NOT NULL DEFAULT 0';	   
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_persons '
				   .'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';	   
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_persons '
				   .'CHANGE access   access int(11) unsigned NOT NULL default 0';	   
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_persons '
				   .'CHANGE living   living tinyint(1) NOT NULL default 0';	   
				// end: joaktree_persons				
				// Table joaktree_person_names
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_person_names '
				   .'CHANGE indNote indNote varchar(1) NOT NULL DEFAULT 0';	   
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_person_names '
				   .'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';	   
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_person_names '
				   .'CHANGE living   living tinyint(1) NOT NULL default 0';	   
				// end: joaktree_persons	
							
				// Table joaktree_trees
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_trees '
				   .'ADD catid           int(11)                   NULL '
				   .'AFTER  root_person_id ';	   
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_trees '
				   .'CHANGE asset_id asset_id int(10) UNSIGNED NOT NULL DEFAULT 0 ';	   
				// end: joaktree_trees
				
				// Table joaktree_locations
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_locations '
				.'CHANGE value value varchar(300) NOT NULL';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_locations '
				.'CHANGE resultValue resultValue varchar(300) NULL';
				// end joaktree_locations	
													
				// Table joaktree_person_events
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_person_events '
				.'CHANGE indNote indNote varchar(1) NOT NULL DEFAULT 0';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_person_events '
				.'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_person_events '
				.'CHANGE location location varchar(300) NULL';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_person_events '
				.'CHANGE value value varchar(300) NULL';
				// end joaktree_person_events	
				
				// Table joaktree_person_notes								
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_person_notes '
				   .'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';	   
				// end joaktree_person_notes	
				
				// Table joaktree_relations
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_relations '
				.'CHANGE indNote indNote varchar(1) NOT NULL DEFAULT 0';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_relations '
				.'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';
				// end joaktree_relation
				
				// Table joaktree_relation_events
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_relation_events '
				.'CHANGE location location varchar(100) NULL';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_relation_events '
				.'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';
				// end joaktree_relation_events
								
				// Table joaktree_relation_notes
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_relation_events '
				.'CHANGE indCitation indCitation tinyint(1) UNSIGNED DEFAULT 0';
				// end joaktree_relation_events
				
				
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_admin_persons '
				 .' CHANGE page   page varchar(1) NOT NULL default 0';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_admin_persons '
				 .' CHANGE access   access int(11) unsigned NOT NULL default 0';
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_admin_persons '
				 .' CHANGE living   living tinyint(1) NOT NULL default 0';
				 
				$update_queries[] = 
					'ALTER IGNORE TABLE '
				   .'#__joaktree_applications '
				   .'CHANGE asset_id asset_id int(10) UNSIGNED NOT NULL DEFAULT 0 ';	   
				// end joaktree_person_events	

                //  joaktree sources
				$update_queries[] = 
				'ALTER IGNORE TABLE '
				.'#__joaktree_sources '
				.'CHANGE title title varchar(500) default  NULL';
				// end joaktree_sources	
                
			// Perform all queries
			foreach( $update_queries as $query ) {
				try {
					$db->setquery( $query );
					$result = $db->execute();
				} catch(Exception $e) {
					//
				}
			} 
			
			Factory::getApplication()->enqueueMessage( 'Database update script is finished for version '.$new_version, 'notice' ) ;
        }
 
        /**
         * Called on uninstallation
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         */
        public function uninstall($installer) {
			// Un-installation
			
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
			foreach( $update_queries as $query ) {
			    $db->setquery( $query );
			    //$db->query();
				$db->execute();
			}
			//$retval=false;
			// Set a simple message
			Factory::getApplication()->enqueueMessage( Text::_( 'NOTE: Five database tables were NOT removed to allow for upgrades' ), 'notice' ) ;

            return $retval;
        }
}
