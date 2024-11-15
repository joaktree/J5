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
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.modal');

?>

<script type="text/javascript">
	function jtsubmitbutton(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.getElementById('reventForm'))) {
			Joomla.submitform(task, document.getElementById('reventForm'));
		} else {
			jtrefnot(1);
			alert('<?php echo $this->escape(Text::_('JT_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<!--  set up counters and other necessities -->
<?php 
	$evtCnt = 0; 
	$this->form->setValue('living', 'person', $this->lists['indLiving']); 
	$display = FormHelper::checkDisplay('relation', $this->lists['indLiving']); 
?>

<script type="text/javascript">
	<?php echo FormHelper::getNameEventRowScript('relationEvent', $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getReferenceRowScript($this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getNoteRowScript(false, $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getGeneralRowScript(); ?>
</script>

<div id="jt-form"> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="reventForm" id="reventForm" class="form-validate">
<?php echo $this->form->getInput('type', null, 'revents'); ?>

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
		<legend><?php echo Text::sprintf('JT_EDITPARTNEREVENTS_DESC', $this->relation->fullName); ?></legend>
		
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(1); ?>							
		<!-- End save + cancel buttons -->		

		<?php echo $this->form->getInput('lineEnd', null, $this->lists['lineEnd']); ?>
		<?php echo $this->form->getInput('id', 'person', ((is_object($this->item)) ? $this->item->id : null)); ?>
		<?php echo $this->form->getInput('app_id', 'person', $this->lists['appId']); ?>
		<?php echo $this->form->getInput('living', 'person', $this->lists['indLiving']); ?>
		<?php echo $this->form->getInput('status', 'person', ((is_object($this->item)) ? 'loaded' : 'new')); ?>
		<?php echo $this->form->getInput('id', 'person.relations', $this->relation->id); ?>
		
		<?php if ($display) { ?>		
			<!-- Events -->	
			<table style="width: 96%;">
				<!-- header for events -->
				<thead>
					<tr>
						<th class="jt-content-th">
							<?php echo Text::_( 'JT_EVENTS' ); ?>
						</th>
						<th class="jt-content-th">
							<?php echo Text::_( 'JT_ACTIONS' ); ?>
						</th>						
					</tr>
				</thead>
				<!-- End: header for events -->
				
				<!-- tabel body for events -->
				<?php $tabEvtId = 'ev_'; ?>
				<tbody id="<?php echo $tabEvtId; ?>">
					<!-- Add row for new event -->
					<tr class="jt-table-entry3" >
						<td style="padding: 2px 5px;">&nbsp;</td>
						<td style="padding: 2px 5px;">
							<div class="jt-edit">
								<a 	href="#"
									onclick="inject_namevtrow('<?php echo $tabEvtId; ?>', '<?php echo $this->lists['appId']; ?>'); return false;"
									title="<?php echo Text::_('JTADD_DESC'); ?>" 
								>
									<?php echo Text::_('JTADD'); ?>
								</a>
							</div>
						</td>
					</tr>			
					<!-- End: Add row for new event -->
					
					<!-- List of existing events -->
					<?php 
					if (is_object($this->item)) {
			  			$events = $this->item->getPartnerEvents($this->relation->id, $this->relation->living);
			  			if (count($events)) {
							$k = 4;
							for ($i=0, $n=count($events); $i < $n; $i++)	{
								$event 	= $events[$i];						 	
								$evtCnt = ($event->orderNumber > $evtCnt) ? $event->orderNumber : $evtCnt;
					?>
								<!-- Row for one existing event -->
								<?php echo FormHelper::getNameEventRow(true, 'relationEvent', $this->form, $this->item, $event, $this->lists['appId'], $this->relation->id) ;?>							
								<!-- End: Row for one existing event -->
															
					<?php   
								$k = 7 - $k;			
			  				}
			  			}
					}
					?>
					<!-- End: List of existing events -->
				</tbody>	
				<!-- End: tabel body for events -->
			</table>
			<!-- End: Events -->
		<?php } ?>
		
		<?php echo FormHelper::getButtons(2); ?>							
	
	</fieldset>
	
	<!-- keep counter values in the form -->
	<input type="hidden" id="namevtcounter" name="namevtcounter" value="<?php echo $evtCnt; ?>"	/>
	<input type="hidden" id="refcounter" name="refcounter" 
		value="<?php echo ((is_object($this->item)) ? $this->item->getMaxReference() : 0); ?>" 
	/>
	<input type="hidden" id="notcounter" name="notcounter" 
		value="<?php echo ((is_object($this->item)) ? $this->item->getMaxNote() : 0); ?>" 
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