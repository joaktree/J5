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
use Joomla\CMS\Router\Route;		//replace JRoute
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.modal');
?>

<script type="text/javascript">
	function jtsubmitbutton(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.getElementById('nameForm'))) {
			Joomla.submitform(task, document.getElementById('nameForm'));
		} else {
			jtrefnot(1);
			alert('<?php echo $this->escape(Text::_('JT_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<!--  set up counters and other necessities -->
<?php 
	$namCnt = 0; 
	$this->form->setValue('living', 'person', $this->lists['indLiving']); 
	$display = FormHelper::checkDisplay('name', $this->lists['indLiving']); 
?>

<script type="text/javascript">
	<?php echo FormHelper::getNameEventRowScript('personName', $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getReferenceRowScript($this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getNoteRowScript(false, $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getGeneralRowScript(); ?>
</script>


<div id="jt-form"> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="nameForm" id="nameForm" class="form-validate">
<?php echo $this->form->getInput('type', null, 'names'); ?>

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
		<legend><?php echo Text::_('JT_EDITNAMES'); ?></legend>
		
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(1) ;?>							
		<!-- End save + cancel buttons -->		

		<!-- Person name -->
		<?php echo $this->form->getInput('lineEnd', null, $this->lists['lineEnd']); ?>
		<?php echo $this->form->getInput('id', 'person', ((is_object($this->item)) ? $this->item->id : null)); ?>
		<?php echo $this->form->getInput('app_id', 'person', $this->lists['appId']); ?>
		<?php echo $this->form->getInput('living', 'person', $this->lists['indLiving']); ?>
		<?php echo $this->form->getInput('status', 'person', ((is_object($this->item)) ? 'loaded' : 'new')); ?>

		<ul class="joaktreeformlist">
			<li>
				<?php echo $this->form->getLabel('firstName', 'person'); ?>
				<?php echo $this->form->getInput('firstName', 'person', ((is_object($this->item)) 
																		? htmlspecialchars_decode($this->item->firstName, ENT_QUOTES) 
																		: null
																		)) ; ?>			
			</li>
			<?php if ($this->lists['indPatronym']) {?>
				<li>
				<?php echo $this->form->getLabel('patronym', 'person'); ?>
				<?php echo $this->form->getInput('patronym', 'person', ((is_object($this->item) && $this->item->patronym) 
																		? htmlspecialchars_decode($this->item->patronym, ENT_QUOTES) 
																		: null
																		)); ?>			
				</li>
			<?php } ?>
			<?php if ($this->lists['indNamePreposition']) {?>
				<li>
				<?php echo $this->form->getLabel('namePreposition', 'person'); ?>
				<?php echo $this->form->getInput('namePreposition', 'person', ((is_object($this->item)) 
																				? htmlspecialchars_decode($this->item->namePreposition, ENT_QUOTES) 
																				: null
																				)); ?>			
				</li>
			<?php } ?>
			<li>
				<?php echo $this->form->getLabel('rawFamilyName', 'person'); ?>
				<?php echo $this->form->getInput('rawFamilyName', 'person', ((is_object($this->item)) 
																			? htmlspecialchars_decode($this->item->rawFamilyName, ENT_QUOTES) 
																			: null
																			)); ?>			
			</li>
			<li>
				<?php echo $this->form->getLabel('prefix', 'person'); ?>
				<?php if (!is_null($this->item->prefix)) echo $this->form->getInput('prefix', 'person', ((is_object($this->item)) ? htmlspecialchars_decode($this->item->prefix, ENT_QUOTES) 
																  : null
																	 )); ?>			
			</li>
			<li>
				<?php echo $this->form->getLabel('suffix', 'person'); ?>
				<?php if (!is_null($this->item->suffix)) echo $this->form->getInput('suffix', 'person', ((is_object($this->item)) ? htmlspecialchars_decode($this->item->suffix, ENT_QUOTES) 
																	 : null
																	 )); ?>			
			</li>
			<li>
				<?php echo $this->form->getLabel('sex', 'person'); ?>
				<?php echo $this->form->getInput('sex', 'person', ((is_object($this->item)) ? $this->item->sex : null)); ?>			
			</li>
			<?php if (!is_object($this->item)) { ?>
				<li>
				<?php echo $this->form->getLabel('livingnew', 'person'); ?>
				<?php echo $this->form->getInput('livingnew', 'person', $this->lists['indLiving']); ?>			
				</li>
			<?php } ?>		
			<li>&nbsp;</li>

		</ul>
		<!-- End: Person name -->
		
		<?php if ($display) { ?>
			<!-- Additional names -->	
			<table style="width: 96%;">
				<!-- header for additional names -->
				<thead>
					<tr>
						<th colspan="3" class="jt-content-th">
							<?php echo Text::_( 'JT_ADDITIONALNAMES' ); ?>
						</th>
					</tr>
					<tr>
						<th class="jt-content-th">
							<?php echo $this->form->getLabel('code', 'person.names'); ?>
						</th>
						<th class="jt-content-th">
							<?php echo $this->form->getLabel('value', 'person.names'); ?>
						</th>
						<th class="jt-content-th">
							<?php echo Text::_( 'JT_ACTIONS' ); ?>
						</th>						
					</tr>
				</thead>
				<!-- End: header for additional names -->
				
				<!-- tabel body for additional names -->
				<?php $tabNamId = 'nm_'; ?>
				<tbody id="<?php echo $tabNamId; ?>">
					<!-- Add row for new additional name -->
					<tr class="jt-table-entry3" >
						<td colspan="2" style="padding: 2px 5px;">&nbsp;</td>
						<td style="padding: 2px 5px;">
							<div class="jt-edit">
								<a 	href="#"
									onclick="inject_namevtrow('<?php echo $tabNamId; ?>', '<?php echo $this->lists['appId']; ?>'); return false;"
									title="<?php echo Text::_('JTADD_DESC'); ?>" 
								>
									<?php echo Text::_('JTADD'); ?>
								</a>
							</div>
						</td>
					</tr>			
					<!-- End: Add row for new additional name -->
					
					<!-- List of existing additional names -->
					<?php 
					if(is_object($this->item)) {
			  			$names = $this->item->getPersonNames();
			  			if (count($names)) {
							$k = 4;
							for ($i=0, $n=count($names); $i < $n; $i++)	{
								$name 			= $names[$i];
								$namCnt = ($name->orderNumber > $namCnt) ? $name->orderNumber : $namCnt;
					?>
								<!-- Row for one existing additional name -->
								<?php echo FormHelper::getNameEventRow(true, 'personName', $this->form, $this->item, $name, $this->lists['appId'], null) ;?>							
								<!-- End: Row for one existing additional name -->
															
					<?php   
								$k = 7 - $k;			
			  				}
			  			}
					}
					?>
					<!-- End: List of existing additional names -->
				</tbody>	
				<!-- End: tabel body for additional names -->
			</table>
			<!-- End: Additional names -->
		<?php } ?>
	
		<!-- Save + cancel buttons -->
		<div class="jt-clearfix"></div>
		<?php echo FormHelper::getButtons(2) ;?>							
		<!-- End save + cancel buttons -->		
	</fieldset>

	<!-- keep counter values in the form -->
	<input type="hidden" id="namevtcounter" name="namevtcounter" value="<?php echo $namCnt; ?>" />
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