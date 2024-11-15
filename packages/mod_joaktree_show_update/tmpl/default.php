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
 * Joomla! 5.x conversion by Conseilgouz
 *
 * Module showing last update of Joaktree Family
 * mod_joaktree_show_update
*/
// no direct access
defined('_JEXEC') or die('Restricted access'); 

use Joomla\CMS\Language\Text;
use Joaktree\Module\Showupdate\Site\Helper\ShowupdateHelper;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx',''));
$result = ShowupdateHelper::getUpdate();
if (!$result) return;
?>

<div class="jt-mod-update-container">
	<div class="jt-mod-update-title">
		<?php echo Text::_('JT_MODUPDATETITLE'); ?>
	</div>
	<div class="jt-mod-update-date">
		<?php echo $result; ?>
	</div>
</div>
