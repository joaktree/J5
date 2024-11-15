<?php
/**
 * Joomla! module Joaktree related items
 * file		JoaktreeHelper - helper.php
 *
 * @version	1.5.3
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Module linking articles to persons in Joaktree component
 *
 */
namespace Joaktree\Module\Relateditems\Site\Helper;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class RelateditemsHelper extends \StdClass
{
	public static function getArticleList()
	{
		$_db	= Factory::getContainer()->get(DatabaseInterface::class);
        $input = Factory::getApplication()->input;
        $_view = $input->get('view','','string');		
        $_option = $input->get('option','','string');	
        $temp = $input->get('id','','string');	
		$temp		= explode(':', $temp);
		$_id	= (int) $temp[0];
		
		$app		= Factory::getApplication();
		$languageFilter   = $app->getLanguageFilter();

        $nullDate	= $_db->getNullDate();
		$date		= Factory::getDate();
		$now  		= $date->toSql();
		
		$params     	= ComponentHelper::getParams('com_joaktree');
		$indArticleLink = (int) $params->get('indArticleLink', 9);
		$displayAccess = JoaktreeHelper::getDisplayAccess();
		$userAccess    = JoaktreeHelper::getUserAccess();
		$accessLevels  = JoaktreeHelper::getUserAccessLevels();
		
		$related	= array();
		$likes 		= array ();

		// situation: article is shown to user -> find other related articles + related persons
		if ($_option == 'com_content' && $_view == 'article' && $_id)
		{
			// select the meta keywords from the article and fill the "likes" parameter
			$query = $_db->getQuery(true);
			$query->select(' metakey ');
			$query->from(  ' #__content ');
			$query->where( ' id = '.(int) $_id.' ');
						
			$_db->setQuery($query);

			if ($metakey = trim($_db->loadResult()))
			{
				// explode the meta keys on a comma
				$keys = explode(',', $metakey);
				
				// assemble any non-blank word(s)
				foreach ($keys as $key)
				{
					$key = trim($key);
					if ($key) {
						// surround with commas so first and last items have surrounding commas
						$likes[] 	= ',' . $_db->escape($key) . ',';
					}
				}
			}
		}		
		
		// situation: persons is shown to user -> find related articles
		// and fill the "likes" parameter with parameters from the person
		if ($_option == 'com_joaktree' && $_view == 'joaktree')
		{
			$query = $_db->getQuery(true);
			
			// get tree id
			$treeId 	= JoaktreeHelper::getTreeId();
			
			// get application id
			$appId 	= JoaktreeHelper::getApplicationId();
			
			// get the person id
			$personId	= JoaktreeHelper::getPersonId();
						
			// check acces to person and tree is allowed
			$accessAllowed = JoaktreeHelper::getAccess();

			// retrieve name of person, if access is allowed
			if (($accessAllowed) and ($indArticleLink != 0))
			{
				$concat_string = ' CONCAT_WS( "," ';
				
				if (($indArticleLink == 1) or ($indArticleLink == 9)) {
					// 1: By ID; 9: All options
					$concat_string .= 
					 '                  , CONCAT_WS( '.$_db->quote('!').' '
					.'                             , jpn.app_id '
					.'                             , jpn.id '
					.'                             ) '
					.'                  , jpn.id ';
					
				}
				
				if (($indArticleLink == 2) or ($indArticleLink == 5) or ($indArticleLink == 9)) {
					// 2: By first name; 5: All names; 9: All options
					$concat_string .= 
					 '                  , jpn.firstName ';					
				}
					
				if (($indArticleLink == 3) or ($indArticleLink == 5) or ($indArticleLink == 9)) {
					// 3: By family name; 5: All names; 9: All options
					$concat_string .= 
					 '                  , CONCAT_WS( '.$_db->quote(' ').' '
					.'                             , jpn.namePreposition '
					.'                             , jpn.familyName '
					.'                             ) ';
				}
				
				if (($indArticleLink == 4) or ($indArticleLink == 5) or ($indArticleLink == 9)) {
					// 4: By first + family name; 5: All names; 9: All options
					$concat_string .= 
					 '                  , CONCAT_WS( '.$_db->quote(' ').' '
					.'                             , jpn.firstName '
					.'                             , jpn.namePreposition '
					.'                             , jpn.familyName '
					.'                             ) ';
				}
				
				$concat_string .= 
					 '                  ) AS metakey ';
					 
				$query->select($concat_string);
				$query->from(  ' #__joaktree_persons        jpn ');
				$query->innerJoin(' #__joaktree_admin_persons  jan '
								 .' ON (   jan.app_id    = jpn.app_id '
								 .'    AND jan.id        = jpn.id '
								 .'    AND jan.published = true '
								 .'    AND jan.page      = true '
			 		             // privacy filter
								 .'    AND (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' = 2 ) '
								 .'        OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    = 2 ) '
								 .'        ) '
								 .'    ) ');
				$query->where(' jpn.id = ' . $_db->quote($personId).' ');
					 
				$_db->setQuery($query);
				
				if ($metakey = trim($_db->loadResult()))
				{
					// explode the meta keys on a comma
					$keys = explode(',', $metakey);
					
					// assemble any non-blank word(s)
					foreach ($keys as $key)
					{
						$key = trim($key);
						if ($key) {
							// surround with commas so first and last items have surrounding commas
							$likes[] 	= ',' . $_db->escape($key) . ',';
						}
					}
				}
			}
		}
		
		// the "likes" parameter is filled - either from article or from person
		
		// process the "likes" - search in keyword of articles	
		if (count($likes))
		{
			// select other items based on the metakey field 'like' the keys found
			$query = $_db->getQuery(true);
			
			$query->select(' a.id ');
			$query->select(' a.title ');
			$query->select(' DATE_FORMAT(a.created, "%Y-%m-%d") AS created ');
			$query->select(' a.catid ');
			$query->select(' CASE WHEN CHAR_LENGTH(a.alias) '
						  .'      THEN CONCAT_WS(":", a.id, a.alias) '
						  .'      ELSE a.id '
						  .' END              AS slug ');
			$query->select(' a.language ');
			$query->from(  ' #__content       AS a ');
			
			// join with categories
			$query->select(' cc.access        AS cat_access ');
			$query->select(' cc.published     AS cat_state ');
			$query->select(' CASE WHEN CHAR_LENGTH(cc.alias) '
						  .'      THEN CONCAT_WS(":", cc.id, cc.alias) '
						  .'      ELSE cc.id '
						  .' END              AS catslug ');
			$query->leftJoin(' #__categories AS cc '
							.' ON (   cc.id        = a.catid '
							.'    AND cc.published = 1 '
							.'    AND cc.access    IN ' .$accessLevels.' '
							.'    ) ');
			
			$query->where( ' a.id             != '.(int) $_id .' ');
			$query->where( ' a.state           = 1 ');
			$query->where( ' a.access         IN ' .$accessLevels.' ');
			$query->where( ' ( CONCAT( "," '
						 .'          , REPLACE( a.metakey '
						 .'                   , ", " '
						 .'                   , "," '
						 .'                   ) '
						 .'          , "," '
						 .'          ) '
						 .'    LIKE "%'.implode('%" OR CONCAT( "," '
						 .'                                  , REPLACE( a.metakey '
						 .'                                           , ", " '
						 .'                                           , "," '
						 .'                                           ) '
						 .'                                  , "," '
						 .'                                  ) LIKE "%'
					                           , $likes 
					                           ).'%" '
					 	 .'  ) '); //remove single space after commas in keywords
			$query->where( ' (  a.publish_up  = '.$_db->Quote($nullDate).' '
						 .'  OR a.publish_up <= '.$_db->Quote($now).' '
						 .'  ) ');
			$query->where( ' (  a.publish_down  = '.$_db->Quote($nullDate).' '
						 .'  OR a.publish_down >= '.$_db->Quote($now).' '
						 .'  ) ');
						 
			// Filter by language
			if ($languageFilter) {
				$query->where('a.language in  ('.$_db->Quote(Factory::getApplication()->getLanguage()->getTag())
											.','.$_db->Quote('*')
											.') ');
			}
						 
			$query->order(' a.title ');
			
			
			$_db->setQuery($query);
			$temp = $_db->loadObjectList();

			if (count($temp))
			{
				foreach ($temp as $row)
				{
					if (	($row->cat_state == 1 || $row->cat_state == '') 
					    &&  (in_array($row->cat_access, $userAccess) || $row->cat_access == '') 
						)
					{
						$row->route = Route::_(RouteHelper::getArticleRoute($row->slug, $row->catslug));
						$row->robot = '';
						$related[] = $row;
					}
				}
			}
			unset ($temp);
		}
		
		return $related;
	}
	
	public static function getJoaktreeList()
	{
		$_db	= Factory::getContainer()->get(DatabaseInterface::class);
        $input = Factory::getApplication()->input;
        $_view = $input->get('view','','string');		
        $_option = $input->get('option','','string');	
        $temp = $input->get('id','','string');	
		$temp		= explode(':', $temp);
		$_id	= (int) $temp[0];
		
		$app		= Factory::getApplication();
		$languageFilter   = $app->getLanguageFilter();
        
		$related	= array();
		$jt_likes 	= array ();
		
		// we only searching for persons when currently one article is shown
		if ($_option == 'com_content' && $_view == 'article' && $_id) 
		{
			$params     	= ComponentHelper::getParams('com_joaktree');
			$indArticleLink = (int) $params->get('indArticleLink', 9);
			
			// we only searching for persons when Joaktree setting is set
			if ($indArticleLink != 0) 
			{
				$displayAccess = JoaktreeHelper::getDisplayAccess();
				$userAccess    = JoaktreeHelper::getUserAccess();
				$accessLevels  = JoaktreeHelper::getUserAccessLevels();
	
				// select the meta keywords from the item
				$query = $_db->getQuery(true);
				$query->select(' metakey ');
				$query->from(  ' #__content ');
				$query->where( ' id = '.(int) $_id.' ');
				$_db->setQuery($query);
	
				if ($metakey = trim($_db->loadResult()))
				{
					// explode the meta keys on a comma
					$keys = explode(',', $metakey);
					
					// assemble any non-blank word(s)
					foreach ($keys as $key)
					{
						$key = trim($key);
						if ($key) {
							// no commas
							$jt_likes[] 	= $_db->escape($key);
						}
					}
				}
			
				// process the "jt_likes" - search in joaktree tables	
				if (count($jt_likes))
				{
					// select from Joaktree tables
					$query->clear();
					
					$query->select(' jpn.app_id ');
					$query->select(' jpn.id ');
					$query->select(' CONCAT_WS( '.$_db->quote(' ').' '
								  .'          , jpn.firstName '
								  .'          , jpn.namePreposition '
								  .'          , jpn.familyName ' 
								  .'          )           AS title ');
					$query->select(' NULL                 AS created ');
					$query->select(' jan.default_tree_id  AS catid ');
					$query->select(' NULL                 AS cat_access ');
					$query->select(' NULL                 AS cat_state ');
					$query->from(  ' #__joaktree_persons        jpn ');
					
					$query->innerJoin(' #__joaktree_admin_persons  jan '
									 .' ON (   jan.app_id    = jpn.app_id '
									 .'    AND jan.id        = jpn.id '
									 .'    AND jan.published = 1 '
									 .'    AND jan.page      = 1 '
				            		 // privacy filter
									 .'    AND (  (jan.living = false AND '.$displayAccess['NAMEname']->notLiving.' = 2 ) '
									 .'        OR (jan.living = true  AND '.$displayAccess['NAMEname']->living.'    = 2 ) '
									 .'        ) '
									 .'    ) ');
					
					
					$query->innerJoin(' #__joaktree_trees jte '
									 .' ON (   jte.app_id    = jan.app_id '
									 .'    AND jte.id        = jan.default_tree_id '
									 .'    AND jte.published = true '
									 .'    AND jte.access    IN ' .$accessLevels.' '
									 .'    ) ');
									 							
					// section to set the where-clause	
					if ($indArticleLink == 9) {
						// 9: All options
						$query->where(' jpn.id = "'.implode( '" OR jpn.id = "'
						                               	   , $jt_likes 
						                               	   ).'" '
									 .' OR CONCAT_WS( '.$_db->quote('!').' '
									 .'             , jpn.app_id '
									 .'             , jpn.id '
									 .'             ) = "'.implode( '" OR CONCAT_WS( '.$_db->quote('!').' '
									 .'                                            , jpn.app_id '
									 .'                                            , jpn.id '
									 .'                                            ) = "'
						                   		                  , $jt_likes 
						                                   		  ).'" '
									 .' OR CONCAT_WS( '.$_db->quote(' ').' '
									 .'             , jpn.firstName '
									 .'             , jpn.namePreposition '
									 .'             , jpn.familyName '
									 .'             ) '
									 .'    LIKE "%'.implode( '%" OR CONCAT_WS( '.$_db->quote(' ').' '
									 .'                                      , jpn.firstName '
									 .'                                      , jpn.namePreposition '
									 .'                                      , jpn.familyName '
									 .'                                      ) LIKE "%'
						                             	   , $jt_likes
						                            	   ).'%" ');
					}
	
					if ($indArticleLink == 1) {
						// 1: By ID
						$query->where(' jpn.id = "'.implode( '" OR jpn.id = "'
						                                   , $jt_likes 
						                                   ).'" '
									 .' OR CONCAT_WS( '.$_db->quote('!').' '
									 .'             , jpn.app_id '
									 .'             , jpn.id '
									 .'             ) = "'.implode( '" OR CONCAT_WS( '.$_db->quote('!').' '
									 .'                                            , jpn.app_id '
									 .'                                            , jpn.id '
									 .'                                            ) = "'
						                                   		  , $jt_likes 
						                                   		  ).'" ');
					}
					
					if ($indArticleLink == 2) {
						// 2: By first name
						$query->where(' jpn.firstName '
									 .'   LIKE "%'.implode( '%" OR jpn.firstName LIKE "%'
						                            	  , $jt_likes
						                            	  ).'%" ');
					}
						
					if ($indArticleLink == 3) {
						// 3: By family name
						$query->where(' CONCAT_WS( '.$_db->quote(' ').' '
									 .'          , jpn.namePreposition '
									 .'          , jpn.familyName '
									 .'          ) '
									 .' LIKE "%'.implode( '%" OR CONCAT_WS( '.$_db->quote(' ').' '
									 .'                                   , jpn.namePreposition '
									 .'                                   , jpn.familyName '
									 .'                                   ) LIKE "%'
						                            	, $jt_likes
						                            	).'%" ');
					}
					
					if (($indArticleLink == 4) or ($indArticleLink == 5)) {
						// 4: By first + family name; 5: All names
						$query->where(' CONCAT_WS( '.$_db->quote(' ').' '
									 .'          , jpn.firstName '
									 .'          , jpn.namePreposition '
									 .'          , jpn.familyName '
									 .'          ) '
									 .' LIKE "%'.implode( '%" OR CONCAT_WS( '.$_db->quote(' ').' '
									 .'                                   , jpn.firstName '
									 .'                                   , jpn.namePreposition '
									 .'                                   , jpn.familyName '
									 .'                                   ) LIKE "%'
						                            	, $jt_likes
						                            	).'%" ');
					}
					
					// end section for setting where-clause -> continue with creating the query
					$query->order(' CONCAT_WS( '.$_db->quote(' ').' '
								 .'          , jpn.firstName '
								 .'          , jpn.namePreposition '
								 .'          , jpn.familyName '
								 .'          ) ');
						
					$_db->setQuery($query);
					$temp = $_db->loadObjectList();
					
					if (count($temp))
					{
						// get menuId & technology
						$menus		= JoaktreeHelper::getMenus('joaktree');
						$technology	= JoaktreeHelper::getTechnology();
						
						$linkBase = 'index.php?option=com_joaktree&view=joaktree&tech='.$technology.'';
						
						foreach ($temp as $row)
						{
							$menuItemId	= $menus[$row->catid];
							$row->route	= Route::_( $linkBase.'&Itemid='.$menuItemId.'&treeId='.$row->catid.'&personId='. $row->app_id.'!'.$row->id );
							$row->robot	= ($technology == 'a') ? '' : 'rel="noindex, nofollow"' ;
							$related[]	= $row;
						}
					}
					unset ($temp);
				}
			}
		}
		
		return $related;
	}
}
