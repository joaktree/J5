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
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.modal');
?>

<script type="text/javascript">
	function jtsubmitbutton(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.getElementById('noteForm'))) {
			Joomla.submitform(task, document.getElementById('noteForm'));
		} else {
			jtrefnot(1);
			alert('<?php echo $this->escape(Text::_('JT_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<!--  set up counters and other necessities -->
<?php 
	$this->form->setValue('living', 'person', $this->lists['indLiving']); 
	$display = FormHelper::checkDisplay('person', $this->lists['indLiving'], 'NOTE'); 
?>

<script type="text/javascript">
	<?php echo FormHelper::getReferenceRowScript($this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getNoteRowScript(true, $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getGeneralRowScript(); ?>
</script>


<div id="jt-form"> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="noteForm" id="noteForm" class="form-validate">
<?php echo $this->form->getInput('type', null, 'notes'); ?>

<?php if (($this->lists['userAccess']) && (is_object($this->canDo)) && ($this->canDo->get('core.edit')) ) { ?> 
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
		<legend><?php echo Text::_('JT_EDITNOTES'); ?></legend>
		
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(1) ;?>							
		<!-- End save + cancel buttons -->		

		<!-- Person -->
		<?php echo $this->form->getInput('lineEnd', null, $this->lists['lineEnd']); ?>
		<?php echo $this->form->getInput('id', 'person', ((is_object($this->item)) ? $this->item->id : null)); ?>
		<?php echo $this->form->getInput('app_id', 'person', $this->lists['appId']); ?>
		<?php echo $this->form->getInput('living', 'person', $this->lists['indLiving']); ?>
		<?php echo $this->form->getInput('status', 'person', ((is_object($this->item)) ? 'loaded' : 'new')); ?>
		<!-- End: Person -->
		
		<?php if ($display) { ?>
			<!-- Person notes -->	
			<table style="width: 96%;">
				<!-- header for notes -->
				<thead>
					<tr>
						<th class="jt-content-th">
							<?php echo Text::_( 'JT_NOTES' ); ?>
						</th>						
						<th class="jt-content-th">
							<?php echo Text::_( 'JT_ACTIONS' ); ?>
						</th>						
					</tr>
				</thead>
				<!-- End: header for notes -->
				
				<!-- tabel body for notes -->
				<?php $tabNotId = 'tbnot_0'; ?>
				<tbody id="<?php echo $tabNotId; ?>">
					<!-- Add row for new note -->
					<tr class="jt-table-entry3" >
						<td style="padding: 2px 5px;">&nbsp;</td>
						<td style="padding: 2px 5px;">
							<div class="jt-edit">
								<a 	href="#"
									onclick="inject_notrow('<?php echo $tabNotId; ?>', 'rN_0', '<?php echo $this->lists['appId']; ?>', 'person', '0'); return false;"
									title="<?php echo Text::_('JTADD_DESC'); ?>" 
								>
									<?php echo Text::_('JTADD'); ?>
								</a>
							</div>
						</td>
					</tr>			
					<!-- End: Add row for new note -->
					
					<!-- List of existing notes -->
					<?php
					if (is_object($this->item)) { 
			  			$notes = $this->item->getNotes('person', null, null);
			  			if (count($notes)) {
							foreach ($notes as $note)	{
								$rowNotId = 'rN_0_n_'.$note->orderNumber;						 	
					?>
								<!-- Row for one existing note -->
								<?php echo FormHelper::getNoteRow(true, true, $this->form, $note, $rowNotId, $this->lists['appId'], $this->item) ;?>
								<!-- End: Row for one existing note -->
															
					<?php   
			  				}
			  			}
					}
					?>
					<!-- End: List of existing notes -->
				</tbody>	
				<!-- End: tabel body for notes -->
			</table>
			<!-- End: notes -->
		<?php } ?>
	
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(2) ;?>							
		<!-- End save + cancel buttons -->		
	</fieldset>

	<!-- keep counter values in the form -->
	<input type="hidden" id="refcounter" name="refcounter" 
		value="<?php echo ((is_object($this->item)) ? $this->item->getMaxReference() : '0'); ?>" 
	/>
	<input type="hidden" id="notcounter" name="notcounter" 
		value="<?php echo ((is_object($this->item)) ? $this->item->getMaxNote() : '0'); ?>" 
	/>

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