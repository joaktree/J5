<?php
/**
 * Module showing last update of Joaktree Family
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Module showing list of persons last viewed by user
 *
 */

namespace Joaktree\Module\Showupdate\Site\Helper;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

class ShowupdateHelper 
{
	public static function getUpdate() {
        
		$db 	= Factory::getContainer()->get(DatabaseInterface::class);
	
		$query = $db->getQuery(true);
		$query->select(' DATE_FORMAT( value, "%e %b %Y" ) ');
		$query->from('#__joaktree_registry_items');
		$query->where('regkey = "LAST_UPDATE_DATETIME"');
						
		$db->setQuery( $query );
		$result = $db->loadResult();
		if (!$result)  return false;
        
		$result = str_replace ('Jan', Text::_('January')  , $result ); 
		$result = str_replace ('Feb', Text::_('February') , $result ); 
		$result = str_replace ('Mar', Text::_('March')    , $result ); 
		$result = str_replace ('Apr', Text::_('April')    , $result ); 
		$result = str_replace ('May', Text::_('May')      , $result ); 
		$result = str_replace ('Jun', Text::_('June')     , $result ); 
		$result = str_replace ('Jul', Text::_('July')     , $result ); 
		$result = str_replace ('Aug', Text::_('August')   , $result ); 
		$result = str_replace ('Sep', Text::_('September'), $result ); 
		$result = str_replace ('Oct', Text::_('October')  , $result ); 
		$result = str_replace ('Nov', Text::_('November') , $result ); 
		$result = str_replace ('Dec', Text::_('December') , $result ); 
			
		return $result;
	}
}


?>