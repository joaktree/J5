<?php
/**
 * Joomla! component Joaktree
 * file		table: joaktree_display_settings.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */
namespace Joaktree\Component\Joaktree\Administrator\Table;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;		//replace JTable
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class DisplaysettingsTable extends Table implements VersionableTableInterface
{
	var $id 			= null;
	var $code			= null;
	var $level 			= null;
	var $ordering		= null;
	var $published		= null;
	var $access 		= null;
	var $accessLiving	= null;
	var $altLiving		= null;

	function __construct(DatabaseDriver $db) {
        $this->typeAlias = 'com_joaktree.display_settings';
		parent::__construct('#__joaktree_display_settings', 'id', $db);
	}
    /**
     * Get the type alias for the table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
?>