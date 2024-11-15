<?php 
/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\Router\Route;		//replace JRoute

// Load Bootstrap
HTMLHelper::_('bootstrap.framework', true);
HTMLHelper::_('bootstrap.modal', 'a.modal');
?>

<form action="<?php echo Route::_('index.php?option=com_joaktree&view=locations&treeId='.$this->lists['tree_id']); ?>" method="post" id="adminForm" name="adminForm">

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<script type="text/javascript">
		<?php //echo $this->lists['script'];?>
	</script>
	
	<div id="jt-content">
		<?php if ($this->lists['columns']) { ?>
					
			<div id="jt-accordion">
				<!-- header -->
				<table>
				<thead>
					<tr>
						<th class="jt-content-th">
						<div class="jt-h3-th">
							<span class="jt-index-left">
								&nbsp;&nbsp;<?php echo Text::_('JT_LOCATIONS'); ?>&nbsp;&nbsp;&nbsp;
							</span>
							<span class="jt-index-right">
							<?php $i = 0; ?>
							<?php foreach ($this->lists['index'] as $indexkeys) { ?>
									<span btn_a_index="<?php echo $i; ?>-jt-cnt" class="jt-content-accordion" >
										<?php echo array_shift($indexkeys).((count($indexkeys)) ? '-'.array_pop($indexkeys) : ''); ?>
									</span>&nbsp;&nbsp;
									<?php $i++; ?>
							<?php } ?>
							</span>
						</div>
						</th>
					</tr>
				</thead>
				</table>

				<h2>
				<span id="jt-map-title"><?php echo ($this->lists['indMap']) ? array_shift($this->lists['map']) : '&nbsp;'; ?></span>
				<?php if ($this->lists['interactiveMap']) { ?>	<!-- // RRG 13/08/2024 radius ne fonctionne pas-->
					<span style="float: right; font-size: 65%; padding-bottom: 10px;" title="<?php echo Text::_('JTMAP_DESC_DISTANCE'); ?>">
						<?php echo Text::_('JTMAP_DISTANCE').'&nbsp;'; ?>
						<?php echo $this->lists['distance']; ?>
					</span>
				<?php } ?> 
				</h2>
								
				<div id="jt-map-id" style="padding-bottom: 15px;">
					<?php if ($this->lists['indMap']) { ?>						
						<iframe 
							id="jt-map-frame"
							src="<?php echo array_shift($this->lists['map']); ?>" 
							height="250px" 
							style="border: 1px solid #dddddd;" >
						</iframe>
					<?php } else {
					    ?>
						<iframe 
							id="jt-map-frame"
							height="250px" 
							style="border: 1px solid #dddddd;display:none" >
						</iframe>

						<?php foreach ($this->articles as $article) { ?>
							<?php if ($article->showTitle) { ?>
								<div class="jt-h1"><?php echo $article->title; ?></div>
							<?php } ?>
							<div><?php echo $article->text; ?></div>
							<div>&nbsp;</div>
						<?php } ?>
					<?php } ?>
				</div>
				
				<div class="jt-index-size">
				<?php $i = 0; ?>
				<?php foreach ($this->lists['index'] as $indexkeys) { ?>
					<div class="content">
						<?php if ($i == $this->lists['indFilter']) { ?>
							<div id="<?php echo $i;?>-jt-cnt" class="jt-showr">
								<?php
					                $layout = $this->setLayout('');
						    $this->display('places');
						    $this->setLayout($layout);
						    ?>							
							</div>
						<?php } else {?>
							<div id="<?php echo $i;?>-jt-cnt" class="jt-ajax">
								<div class="jt-high-row jt-ajax-loader">&nbsp;</div>
							</div>						
						<?php } ?>
					</div>
					<?php $i++; ?>
				<?php } ?>
				</div>
			</div>
			
		<?php } ?>
	</div> <!-- jt-content -->

<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>

<div class="jt-update">
	<?php echo $this->lists[ 'lastUpdate' ]; ?>
</div>
<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="locations" />
<script type="text/javascript">
	<?php echo $this->lists['script']; ?>
</script>

</form>