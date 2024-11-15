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
 * Module showing last update of Joaktree Family
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joaktree\Module\Lastpersonsviewed\Site\Helper\LastpersonsviewedHelper;

$numberInList 	= $params->get('numberInList', 3);

$personlist = LastpersonsviewedHelper::getList($numberInList);

if (!is_array($personlist)) { ?>
	<p><?php echo Text::_('MOD_JTLPV_NOCOOKIES'); ?></p> 

<?php } elseif (count($personlist)) { ?>
	<ol>
	<?php foreach ($personlist as $person) {	?>
		<li><a href="<?php echo $person->route; ?>" <?php echo $person->robot; ?>>
			<?php echo $person->fullName; ?>
		</a></li>
	<?php } ?>
	</ol>
<?php } else { ?>
	<p><?php echo Text::_('MOD_JTLPV_NOPERSONS'); ?></p> 
<?php } ?>


