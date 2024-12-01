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

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

$this->params 		= JoaktreeHelper::getJTParams();
$services = $this->params->get('services');
if (!is_object($services)) {
    $services = json_decode($this->params->get('services'));
}
$format = "raw";
if ($services->interactivemap == "Openstreetmap") {
    $format = "html";
}

?>
<!-- ?php echo $this->map->getStyleDeclaration(); ? -->

<?php if ($this->lists['userAccess']) { ?> 	
	<!-- A reference to a map is found -->
	<!-- toolkit -->
    <?php if ($services->interactivemap == "Openstreetmap") { // OpenstreetMap
        echo $this->script;
    } else { ?>
	<?php if ($this->toolkit) { ?>
		<script src="<?php echo $this->toolkit; ?>" type="text/javascript"></script>
	<?php } ?>
	
	<script type="text/javascript">
		<?php echo $this->script; ?>
	</script>

	<div id="map_canvas" style="width:100%; height:100%"></div>	 
<?php }
    } else { ?>
	<!-- No reference is found - so user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>

<?php echo HTMLHelper::_('form.token'); ?>	
	