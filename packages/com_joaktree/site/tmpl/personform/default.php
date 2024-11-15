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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.modal');
?>
<script type="text/javascript">
	function jtsubmitbutton(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.getElementById('peventForm'))) {
			Joomla.submitform(task, document.getElementById('peventForm'));
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
$display = FormHelper::checkDisplay('person', $this->lists['indLiving']);
?>

<script type="text/javascript">
	<?php echo FormHelper::getNameEventRowScript('personEvent', $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getReferenceRowScript($this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getNoteRowScript(false, $this->form, $this->lists['appId']); ?>
	<?php echo FormHelper::getGeneralRowScript(); ?>
</script>
<script>
	function jtModalSelectPerson() {
		var sexValue = document.getElementById('jform_person_sex').value;
		var nameValue = document.getElementById('jform_person_firstName').value;

		if (window.parent) {
			window.parent.inject_relrow('re_', sexValue, nameValue);
			// window.parent.SqueezeBox.close();
		} 
		
	}


</script>


<div id="jt-form"> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="peventForm" id="peventForm" class="form-validate">
<?php
    switch ($this->lists['action']) {
        case "addparent": 	// continue
        case "addpartner": 	// continue
        case "addchild":	$formType = $this->lists['action'];
            break;
        default:			$formType = (!is_object($this->item)) ? 'newperson' : 'pevents';
            break;
    }
echo $this->form->getInput('type', null, $formType);
?>

<?php if (($this->lists['userAccess']) && (is_object($this->canDo)) &&
            (
                (
                    $this->canDo->get('core.create')
               && (
                   ($formType == 'addparent')
                  || ($formType == 'addpartner')
                  || ($formType == 'addchild')
                  || ($formType == 'newperson')
               )
                )
                || (
                    $this->canDo->get('core.edit')
               && ($formType == 'pevents')
                )
            )
) {
    ?> 
<!-- user has access to information -->
<div class="fltlft">
	<div class="jt-content-th" >
		<div class="jt-h3-th">
			<?php if (!is_object($this->item)) {
			    switch ($this->lists['action']) {
			        case "addparent":
			            $text = Text::sprintf('JT_NEW_PARENT', $this->relation->fullName);
			            $labelFamily = Text::_('JT_LABEL_FAMILY1');
			            $descrFamily = Text::sprintf('JT_DESC_FAMILY1', $this->relation->fullName);
			            break;
			        case "addpartner":
			            $text = Text::sprintf('JT_NEW_PARTNER', $this->relation->fullName);
			            $labelFamily = Text::_('JT_LABEL_FAMILY2');
			            $descrFamily = Text::sprintf('JT_DESC_FAMILY2', $this->relation->fullName);
			            break;
			        case "addchild":
			            $text = Text::sprintf('JT_NEW_CHILD', $this->relation->fullName);
			            $labelFamily = Text::_('JT_LABEL_FAMILY3');
			            $descrFamily = Text::sprintf('JT_DESC_FAMILY3', $this->relation->fullName);
			            break;
			        default:
			            $text = Text::_('JT_NEW_PERSON');
			            break;
			    }
			    echo $text;

			} else {
			    echo Text::_('JT_EDIT_RECORD').':&nbsp;'.$this->item->firstName.'&nbsp;'.$this->item->familyName;
			    ?>
					<span style="float: right;">
						<?php echo(($this->lists['indLiving']) ? Text::_('JT_LIVING') : Text::_('JT_NOTLIVING')); ?>
					</span>
			<?php
			}
    ?>
		</div>
	</div>

	<!-- Hidden fields -->
	<?php echo $this->form->getInput('lineEnd', null, $this->lists['lineEnd']); ?>
	<?php echo $this->form->getInput('id', 'person', ((is_object($this->item)) ? $this->item->id : null)); ?>		
	<?php echo $this->form->getInput('app_id', 'person', $this->lists['appId']); ?>
	<?php echo $this->form->getInput('living', 'person', $this->lists['indLiving']); ?>
	<?php echo $this->form->getInput('status', 'person', ((is_object($this->item)) ? 'loaded' : 'new')); ?>		
	<!-- End: Hidden fields -->		

	<?php if (!is_object($this->item)) { ?>
		<fieldset class="joaktreeform">
			<legend><?php echo Text::_('JT_EDITAPP'); ?></legend>
			
			<!-- Save + cancel buttons -->
			<?php $but = array( 'save' 		=> true
                     , 'cancel'	=> true
                     , 'check'		=> (is_object($this->item) ? false : true)
                     , 'done'		=> false
                     , 'add'		=> false
                     );
	    $indParent1 = (isset($this->lists['action']) && ($this->lists['action'] == 'addparent')) ? true : false ;
	    ?>
			<?php echo FormHelper::getButtons(1, $but, $indParent1); ?>							
			<!-- End save + cancel buttons -->		

			<ul class="joaktreeformlist">
				<li>
					<?php //echo $this->form->getLabel('appName', 'person');?>
					<?php echo $this->form->getInput('appName', 'person', $this->lists['appName']); ?>			
				</li>
				<li>
					<?php echo $this->form->getLabel('default_tree_id', 'person'); ?>
					<?php echo $this->form->getInput('default_tree_id', 'person', $this->lists['treeId']); ?>			
				</li>			
			</ul>
		</fieldset>

		<fieldset class="joaktreeform">
			<legend><?php echo Text::_('JT_EDITNAMES'); ?></legend>
			
			<!-- Person name : only shown when it is a newly added person -->
			<ul class="joaktreeformlist">
				<li>
					<?php echo $this->form->getLabel('firstName', 'person'); ?>
					<?php echo $this->form->getInput('firstName', 'person', null); ?>			
				</li>
				<?php if ($this->lists['indPatronym']) {?>
					<li>
					<?php echo $this->form->getLabel('patronym', 'person'); ?>
					<?php echo $this->form->getInput('patronym', 'person', null); ?>			
					</li>
				<?php } ?>
				<?php if ($this->lists['indNamePreposition']) {?>
					<li>
					<?php echo $this->form->getLabel('namePreposition', 'person'); ?>
					<?php echo $this->form->getInput('namePreposition', 'person', null); ?>			
					</li>
				<?php } ?>
				<li>
					<?php echo $this->form->getLabel('rawFamilyName', 'person'); ?>
					<?php echo $this->form->getInput('rawFamilyName', 'person', null); ?>			
				</li>
				<li>
					<?php echo $this->form->getLabel('prefix', 'person'); ?>
					<?php echo $this->form->getInput('prefix', 'person', null); ?>			
				</li>
				<li>
					<?php echo $this->form->getLabel('suffix', 'person'); ?>
					<?php echo $this->form->getInput('suffix', 'person', null); ?>			
				</li>
				<li>
					<?php echo $this->form->getLabel('sex', 'person'); ?>
					<?php echo $this->form->getInput('sex', 'person', null); ?>			
				</li>
				<li>
					<?php echo $this->form->getLabel('livingnew', 'person'); ?>
					<?php echo $this->form->getInput('livingnew', 'person', $this->lists['indLiving']); ?>			
				</li>
				<li>&nbsp;</li>
			</ul>
			<!-- End: Person name -->			
		</fieldset>
		
		<?php if (!empty($this->relation->id)) {	 ?>
			<fieldset class="joaktreeform">
				<legend><?php echo Text::_('JT_EDITRELATION'); ?></legend>
				
				<ul class="joaktreeformlist">
					<!-- The relation, type, and family -->
					<?php echo $this->form->getInput('action', null, $this->lists['action']); ?>
					<?php echo $this->form->getInput('id', 'person.relations', $this->relation->id); ?>
					<?php if (($this->lists['action'] == 'addparent')
	                     || ($this->lists['action'] == 'addchild')
	                     || ($this->lists['action'] == 'addpartner')
					) { ?>
						<li>
							<label 
								title="<?php echo $labelFamily.'::'.$descrFamily; ?>" 
								class="hasTip" 
								for="jform_person_relations_family" 
								id="jform_person_relations_family-lbl" 
								style="aria-invalid: false;"
							>
								<?php echo $labelFamily; ?>
							</label>
						
							<?php echo $this->form->getInput('family', 'person.relations'); ?>			
						</li>
						<?php if (($this->lists['action'] == 'addparent')
					    || ($this->lists['action'] == 'addchild')
						) { ?>
							<li>
							<?php echo $this->form->getLabel('relationtype', 'person.relations'); ?>
							<?php echo $this->form->getInput('relationtype', 'person.relations', null); ?>			
							</li>
						<?php } ?>
						<?php if ($this->lists['action'] == 'addpartner') { ?>
							<li>
							<?php echo $this->form->getLabel('partnertype', 'person.relations'); ?>
							<?php echo $this->form->getInput('partnertype', 'person.relations', null); ?>			
							</li>
						<?php } ?>
					<?php } ?>
				</ul>			
			</fieldset>
		<?php } ?>		
	<?php } ?>

	<fieldset class="joaktreeform">
		<legend><?php echo Text::_('JT_EDITEVENTS'); ?></legend>

		<?php if (is_object($this->item)) { ?>	
			<!-- Save + cancel buttons -->
			<?php $but = array( 'save' 		=> true
						      , 'cancel'	=> true
						      , 'check'		=> (is_object($this->item) ? false : true)
						      , 'done'		=> false
						      , 'add'		=> false
						      );
		    $indParent1 = (isset($this->lists['action']) && ($this->lists['action'] == 'addparent')) ? true : false ;
		    ?>
			<?php echo FormHelper::getButtons(1, $but, $indParent1); ?>							
			<!-- End save + cancel buttons -->
		<?php } ?>		

		<?php if ($display) { ?>
			<!-- Events -->	
			<table style="width: 96%;">
				<!-- header for events -->
				<thead>
					<tr>
						<th class="jt-content-th">
							<?php echo Text::_('JT_EVENTS'); ?>
						</th>
						<th class="jt-content-th">
							<?php echo Text::_('JT_ACTIONS'); ?>
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
		                $events = $this->item->getPersonEvents();
		                if (count($events)) {
		                    $k = 4;
		                    for ($i = 0, $n = count($events); $i < $n; $i++) {
		                        $event 	= $events[$i];
		                        $evtCnt = ($event->orderNumber > $evtCnt) ? $event->orderNumber : $evtCnt;
		                        ?>
								<!-- Row for one existing event -->
								<?php echo FormHelper::getNameEventRow(true, 'personEvent', $this->form, $this->item, $event, $this->lists['appId'], null) ;?>							
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
		
		<?php echo FormHelper::getButtons(2, $but, $indParent1); ?>							
	</fieldset>
		
	<!-- keep counter values in the form -->
	<input type="hidden" id="namevtcounter" name="namevtcounter" value="<?php echo $evtCnt; ?>"	/>
	<input type="hidden" id="refcounter" name="refcounter" 
		value="<?php echo((is_object($this->item)) ? $this->item->getMaxReference() : 0); ?>" 
	/>
	<input type="hidden" id="notcounter" name="notcounter" 
		value="<?php echo((is_object($this->item)) ? $this->item->getMaxNote() : 0); ?>" 
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
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>

<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

</form>
</div>