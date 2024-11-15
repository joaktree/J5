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

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.modal');
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;
?>

<script type="text/javascript">
	function jtsubmitbutton(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.getElementById('childForm'))) {
			Joomla.submitform(task, document.getElementById('childForm'));
		} else {
			jtrefnot(1);
			alert('<?php echo $this->escape(Text::_('JT_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<script type="text/javascript">
	<?php echo FormHelper::getGeneralRowScript(); ?>
</script>


<div id="jt-form"> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="childForm" id="childForm" class="form-validate">
<?php echo $this->form->getInput('type', null, 'children'); ?>

<?php if ( ($this->lists['userAccess']) && (is_object($this->canDo)) && ($this->canDo->get('core.edit')) ) { ?> 
<!-- user has access to information -->
<div class="fltlft">
	<div class="jt-content-th" >
		<div class="jt-h3-th">
			<?php if (!is_object($this->item)) {
					echo Text::_( 'JT_NEW_PERSON' );
				  } else {
				  	echo Text::_( 'JT_EDIT_RECORD' ).':&nbsp;'.$this->item->firstName.'&nbsp;'.$this->item->familyName;
			?>
					<span style="float: right;">
						<?php echo (($this->lists['indLiving']) ? Text::_( 'JT_LIVING' ) : Text::_( 'JT_NOTLIVING' )); ?>
					</span>
			<?php 
				  }
			 ?>
		</div>
	</div>

	<fieldset class="joaktreeform">
		<legend><?php echo Text::_('JT_EDITCHILDREN'); ?></legend>
		
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(1) ;?>							
		<!-- End save + cancel buttons -->		

		<?php echo $this->form->getInput('lineEnd', null, $this->lists['lineEnd']); ?>
		<?php echo $this->form->getInput('id', 'person', ((is_object($this->item)) ? $this->item->id : null)); ?>
		<?php echo $this->form->getInput('app_id', 'person', $this->lists['appId']); ?>
		<?php echo $this->form->getInput('living', 'person', $this->lists['indLiving']); ?>
		<?php echo $this->form->getInput('status', 'person', 'relation' ); ?>
		
		<!-- Relations -->	
		<!-- List of existing relations -->
		<?php
		if (is_object($this->item)) { 
			$children	= $this->item->getChildren();
			$partners	= $this->item->getPartners();
								
  			if (count($children)) {
  				$orderNumber = 0;
  				
				// check for children with only one parent
				$counter_1 = 0;
				for ($i=0, $n=count( $children ); $i < $n; $i++) {
					if ( $children[$i]->secondParent_id == null ) {
						$counter_1++;
					}
				}
  				
				if ($counter_1 > 0) {
					// children with only one parent
		?>
					<div class="jt-h3" style="margin: 20px 0 0 15px;">
						<?php echo Text::_('JT_CHILDREN'); ?>
					</div>
					<table style="width: 96%; margin-top: 10px;">
						<!-- header for relations -->
						<thead>
							<tr>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_LABEL_NAME' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_BORN' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_DIED' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_LABEL_RELATIONTYPE' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_ACTIONS' ); ?>
								</th>						
							</tr>
						</thead>
						<!-- End: header for relations -->
						<!-- tabel body for relations -->
						<?php $tabRelId = 're_'; ?>			
						<tbody id="<?php echo $tabRelId; ?>">
		<?php 							
						// loop through the children and filter on the one parent children
						$k = 4;
						for ($i=0, $n=count( $children ); $i < $n; $i++) {
							if ( $children[$i]->secondParent_id == null ) {
								$child = $children[$i];
								$child->orderNumber = $orderNumber++;													 	
								// Row for one existing relation
								echo FormHelper::getRelationRow($child, 'children', $this->form);							
								//End: Row for one existing relation
								$k = 7 - $k;
							}
						}
		?>
						</tbody><!-- End: List of children with 1 parent -->	
					</table><!-- End: tabel body for relations -->
		<?php 
				}
				
				// check for situation of only one spouse, and no children out of wedlock
				if ( ( $counter_1 == 0 ) and ( count($partners) == 1 ) ) {
					// display children from one partner
		?>
					<div class="jt-h3" style="margin: 20px 0 0 15px;">
						<?php echo Text::_('JT_CHILDREN_WITH').' '.$partners[0]->fullName; ?>
					</div>
					<table style="width: 96%; margin-top: 10px;">
						<!-- header for relations -->
						<thead>
							<tr>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_LABEL_NAME' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_BORN' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_DIED' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_LABEL_RELATIONTYPE' ); ?>
								</th>
								<th class="jt-content-th">
									<?php echo Text::_( 'JT_ACTIONS' ); ?>
								</th>						
							</tr>
						</thead>
						<!-- End: header for relations -->
						<!-- tabel body for relations -->
						<?php $tabRelId = 're_'; ?>			
						<tbody id="<?php echo $tabRelId; ?>">
		<?php 
						// loop through the children without filtering
						$k = 4;
						for ($i=0, $n=count( $children ); $i < $n; $i++) {
							$child = $children[$i];
							$child->orderNumber = $orderNumber++;													 	
							// Row for one existing relation
							echo FormHelper::getRelationRow($child, 'children', $this->form);							
							//End: Row for one existing relation
							$k = 7 - $k;
						}
		?>
						</tbody><!-- End: List of children -->	
					</table><!-- End: tabel body for relations -->
		<?php 
				} else {
					// loop through the partners of person
					// loop through the partners of person
					for ($j=0, $m=count( $partners ); $j < $m; $j++) {
						// count children for this partner
						$counter = 0;
						for ($i=0, $n=count( $children ); $i < $n; $i++) {
							if ( $children[$i]->secondParent_id == $partners[$j]->id ) {
								$counter++;
							}
						}
						
						if ($counter > 0) {
							// children with this partner
		?>
							<div class="jt-h3" style="margin: 20px 0 0 15px;">
								<?php echo Text::_('JT_CHILDREN_WITH').' '.$partners[$j]->fullName; ?>
							</div>
							<table style="width: 96%; margin-top: 10px;">
								<!-- header for relations -->
								<thead>
									<tr>
										<th class="jt-content-th">
											<?php echo Text::_( 'JT_LABEL_NAME' ); ?>
										</th>
										<th class="jt-content-th">
											<?php echo Text::_( 'JT_BORN' ); ?>
										</th>
										<th class="jt-content-th">
											<?php echo Text::_( 'JT_DIED' ); ?>
										</th>
										<th class="jt-content-th">
											<?php echo Text::_( 'JT_LABEL_RELATIONTYPE' ); ?>
										</th>
										<th class="jt-content-th">
											<?php echo Text::_( 'JT_ACTIONS' ); ?>
										</th>						
									</tr>
								</thead>
								<!-- End: header for relations -->
								<!-- tabel body for relations -->
								<?php $tabRelId = 're'.$j.'_'; ?>			
								<tbody id="<?php echo $tabRelId; ?>">
		<?php 
								// loop through the children and filter on the correct parent 
								$k = 4;
								for ($i=0, $n=count( $children ); $i < $n; $i++) {
									if ( $children[$i]->secondParent_id == $partners[$j]->id ) {
										$child = $children[$i];
										$child->orderNumber = $orderNumber++;													 	
										// Row for one existing relation
										echo FormHelper::getRelationRow($child, 'children', $this->form);							
										//End: Row for one existing relation
										$k = 7 - $k;
									}
								}
		?>
								</tbody><!-- End: List of children -->	
							</table><!-- End: tabel body for relations -->
		<?php 
						} 	// end counter > 0 (children for specific partner)
					}		// end looping through partners
				}			// end else-branch
  			}				// end check: total number of children > 0 
		}					// end check: person is known
		?>		
		<!-- End: Relations -->
	
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(2) ;?>							
		<!-- End save + cancel buttons -->		
	</fieldset>

	<input type="hidden" name="treeId" value="<?php echo $this->lists['treeId']; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="personform" />
	<?php echo HTMLHelper::_('form.token'); ?>

</div>

<div class="clr"></div>

<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
	</div>
<?php } ?>

<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

</form>
</div>