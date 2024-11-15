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
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HtmlHelper::_('bootstrap.framework', true);

$menu = Factory::getApplication()->getMenu()->getActive();
?>
 
<form action="<?php echo Route::_( 'index.php?option=com_joaktree&view=joaktreestart&treeId='.$this->lists['tree_id'] ); ?>" method="post" id="adminForm" name="adminForm">
<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<!-- <script type="text/javascript">
		<?php //echo $this->lists['script']; ?>
	</script> -->
	
	<div id="jt-content">
		<!-- introduction text -->
		<?php foreach ($this->articles as $article) { ?>
			<?php if ($article->showTitle) { ?>
				<div class="jt-h1"><?php echo $article->title; ?></div>
			<?php } ?>
			<div><?php echo $article->text; ?></div>
			<div>&nbsp;</div>
		<?php } ?>
	
		<?php if ($this->lists['columns']) { ?>
					
			<div id="jt-accordion">
				<!-- header -->
				<table>
				<thead>
					<!-- showing counts for persons and/or marriages -->
					<?php if (($this->treeinfo->indPersonCount) || ($this->treeinfo->indMarriageCount)) { ?>
						<tr>
							<th class="jt-content-th">
							<!-- showing counts for persons -->
							<?php if ($this->treeinfo->indPersonCount) { ?>
								<div class="jt-h3-th">
									<span class="jt-label">&nbsp;&nbsp;
									<?php echo Text::_('JT_NUMBEROF').'&nbsp;'.Text::_('JT_NUM_PERSON'); ?>
									&nbsp;&nbsp;</span>
									<?php  echo $this->lists['personCount']; ?>
								</div>								
							<?php } ?>
							
							<!-- second cell when necessary -->
							<?php if (($this->treeinfo->indPersonCount) && ($this->treeinfo->indMarriageCount)) { ?>
								</th>
								<th class="jt-content-th">	
							<?php } ?>
							
							<!-- showing counts for marriages -->
							<?php if ($this->treeinfo->indMarriageCount) { ?>
								<div class="jt-h3-th">
									<span class="jt-label">&nbsp;&nbsp;
									<?php echo Text::_('JT_NUMBEROF').'&nbsp;'.Text::_('JT_NUM_MARRIAGE'); ?>
									&nbsp;&nbsp;</span>
									<?php echo $this->lists['marriageCount']; ?>	
								</div>
							<?php } ?>
							</th>
						</tr>
					<?php } ?>
					
					<?php 
					 	$colspan = (($this->treeinfo->indPersonCount) && ($this->treeinfo->indMarriageCount)) ? 2 : 1;
					?>	 			
					<tr>
						<th colspan="<?php echo $colspan; ?>" class="jt-content-th">
						<div class="jt-h3-th">
							<span class="jt-index-left">
								&nbsp;&nbsp;<?php echo Text::_('JT_INDEX'); ?>&nbsp;&nbsp;&nbsp;
							</span>
							<span class="jt-index-right">
							<?php $i = 0; ?>
							<?php foreach ($this->lists['index'] as $indexkeys) { ?>
									<!--<span	class="jt-content-alpha" >
										<?php //echo array_shift($indexkeys).((count($indexkeys)) ? '-'.array_pop($indexkeys) : ''); ?>
									</span>&nbsp;&nbsp; --> 
									 <span btn_a_index="<?php echo $i; ?>-jt-cnt" class="jt-content-accordion" >
                                     <!-- <span btn_a="<?php //echo $i; ?>-start-alpha"	class="jt-content-accordion" > -->
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
				<div class="jt-index-size">
				<?php $i = 0; ?>
				<?php foreach ($this->lists['index'] as $indexkeys) { ?>

					<div class="content">
						<?php if ($i == $this->lists['indFilter']) { ?>
							<div id="<?php echo $i;?>-jt-cnt" class='jt-showr'>
								<?php 	
									$layout = $this->setLayout('');
									$this->display('names');
									$this->setLayout($layout);
								?>							
							</div>
						<?php } else { ?>
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
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
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
<input type="hidden" name="controller" value="list" />
</form>
<script type="text/javascript">
	<?php echo $this->lists['script']; ?>
</script>

