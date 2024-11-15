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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('formbehavior.chosen', 'select');

if (isset($this->map->params) && $this->map->params['service'] == 'staticmap') {
    if ($this->map->getService()->getProvider()->getName() == 'OpenStreetMap') {
        $ascript = $this->map->getMapScript();
        echo $ascript;
    } else {
        ?>	 
	<div id="jt-content">
		<?php if ($this->lists['userAccess']) { ?> 	
			<img 
				id="<?php echo $this->lists[ 'mapHtmlId' ]; ?>" 
				src="<?php echo $this->mapview; ?>" 
				alt="<?php echo Text::_('JT_NOACCESS'); ?>"
			/>
			<?php echo $this->lists[ 'uicontrol' ]; ?>

		<?php } else { ?>
			<!-- user has NO access to information -->
			<div class="jt-content-th" >
				<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
			</div>
		<?php } ?>
				
		<div class="jt-stamp"><?php echo $this->lists[ 'CR' ]; ?></div>
		
	</div><!-- jt-content -->
<?php }
    } ?>

<?php if (isset($this->map->params) && $this->map->params['service'] == 'interactivemap') {
    if ($this->map->getService()->getProvider()->getName() == 'OpenStreetMap') {
        $ascript = $this->map->getMapScript();
        echo $ascript;
    } else {
        ?>
	<?php $width  = ($this->map->params['width']) ? 'width: '.(int) $this->map->params['width'].'px; ' : '';?>
	<?php $height = 'height: '.(int) $this->map->params['height'].'px; '; ?>
	<div style="<?php echo $width; ?> <?php echo $height; ?>">
		<iframe 
		    id="<?php echo $this->lists[ 'mapHtmlId' ]; ?>"
			src="<?php echo $this->lists[ 'href' ]; ?>" 
			height="<?php echo (int) $this->map->params['height'];?>px"
			style="border:1px solid #dddddd;"
		>
		</iframe>
	</div>	 
	<div><?php echo $this->lists[ 'uicontrol' ]; ?></div>
	<div class="jt-stamp"><?php echo $this->lists[ 'CR' ]; ?></div>
<?php }
    } ?>

<?php echo HTMLHelper::_('form.token'); ?>	
	