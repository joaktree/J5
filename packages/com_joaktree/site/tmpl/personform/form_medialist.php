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
?>

<script type="text/javascript">
	function jtsubmitbutton(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid(document.getElementById('mediaForm'))) {
			Joomla.submitform(task, document.getElementById('mediaForm'));
		} else {
			alert('<?php echo $this->escape(Text::_('JT_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<?php 
	//  set up counters
	$picCnt = 0;
	// 	retrieve pictures
	$pictures 		 = $this->item->getPictures(true);
	// retrieve parameters
	$ds				= '/';
	$docsFromGedcom	= (int) $this->params->get('indDocuments', 0); 
	$pictureroot	= $this->params->get('Directory', 'images'.$ds.'joaktree');
?>

<script type="text/javascript">
	<?php echo FormHelper::getGeneralRowScript(); ?>
</script>

<div id="jt-form"> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="mediaForm" id="mediaForm" class="form-validate">
<?php echo $this->form->getInput('type', null, 'medialist'); ?>

<?php if (($this->lists['userAccess'])
			&& (is_object($this->item)) 
			&& (is_object($this->canDo)) && 
			(  $this->canDo->get('media.create')
			|| ($this->canDo->get('core.edit') && ($docsFromGedcom))
			)
		  ) { 
?> 
<!-- user has access to information -->
<div class="fltlft">
	<div class="jt-content-th" >
		<div class="jt-h3-th">
			<?php echo Text::_( 'JT_EDIT_RECORD' ).':&nbsp;'.$this->item->firstName.'&nbsp;'.$this->item->familyName; ?>
			<span style="float: right;">
				<?php echo (($this->lists['indLiving']) ? Text::_( 'JT_LIVING' ) : Text::_( 'JT_NOTLIVING' )); ?>
			</span>
		</div>
	</div>

	<fieldset class="joaktreeform">
		<legend><?php echo Text::_('JT_EDITPICTURES'); ?></legend>
		
		<!-- Save + cancel buttons -->
		<?php $but = array( 'save' 		=> (($docsFromGedcom) ? true : false)
						  , 'cancel' 	=> (($docsFromGedcom) ? true : false)
						  , 'check' 	=> false
						  , 'done' 		=> ((!$docsFromGedcom) ? true : false)
						  , 'add' 		=> (($this->canDo->get('media.create')) ? true : false)
						  ); ?>
		<?php echo FormHelper::getButtons(1, $but) ;?>							
		<!-- End save + cancel buttons -->	
			
		<!-- Person media -->
		<?php echo $this->form->getInput('lineEnd', null, $this->lists['lineEnd']); ?>
		<?php echo $this->form->getInput('id', 'person', $this->item->id); ?>
		<?php echo $this->form->getInput('app_id', 'person', $this->lists['appId']); ?>
		<?php echo $this->form->getInput('living', 'person', $this->lists['indLiving']); ?>
		<?php echo $this->form->getInput('status', 'person', 'loaded'); ?>
		
		<?php	
			if ((!$docsFromGedcom) && ($this->canDo->get('media.create'))) {
				$directory = (count($pictures)) ? $pictures[0]->directory: $pictureroot.$ds.$this->lists['appId'].'!'.$this->item->id;
				// Tell the user in which directory the pictures should be placed.
		?>		
				<div class="jt-high-row" style="margin-left: 10px;">
					<?php echo Text::sprintf('JT_PIC_DIRECTORY', $directory); ?>
				</div>
				<div class="jt-clearfix"></div>
		<?php 
			} 
		?>
		
		<!-- Pictures -->	
		<table style="width: 96%;">
			<!-- header for pictures -->
			<thead>
				<tr>
					<th class="jt-content-th">
						<label><?php echo Text::_('JT_PICTURE'); ?></label>
					</th>
					<th class="jt-content-th">
						<?php echo $this->form->getLabel('title', 'person.media'); ?>
					</th>
					<th class="jt-content-th">
						<?php echo $this->form->getLabel('path_file', 'person.media'); ?>
					</th>
					<?php if ($docsFromGedcom) { ?>
						<th class="jt-content-th">
							<label><?php echo Text::_( 'JT_ACTIONS' ); ?></label>
						</th>
					<?php } ?>					
				</tr>
			</thead>
			<!-- End: header for pictures -->
			
			<!-- tabel body for pictures -->
			<?php $tabPicId = 'pi_'; ?>
			<tbody id="<?php echo $tabPicId; ?>">
				
				<!-- List of existing pictures -->
				<?php 
				if(count($pictures)) {
					$k = 4;
					foreach ($pictures as $picture)	{
						$picCnt++;					 	
						$picture->orderNumber = $picCnt;
				?>
						<!-- Row for one existing picture -->
						<?php echo FormHelper::getPictureRow($this->form, $picture, $docsFromGedcom) ;?>							
						<!-- End: Row for one existing picture -->														
				<?php   
						$k = 7 - $k;			
	  				}
				}
				?>
				<!-- End: List of existing pictures -->
			</tbody>	
			<!-- End: tabel body for pictures -->
		</table>
		<!-- End: Person media -->
			
		<!-- Save + cancel buttons -->
		<?php echo FormHelper::getButtons(2, $but) ;?>							
		<!-- End save + cancel buttons -->		
	</fieldset>

	<!-- keep counter values in the form -->
	<input type="hidden" id="namevtcounter" name="namevtcounter" value="<?php echo $picCnt; ?>"	/>

	<input type="hidden" name="personId" value="<?php echo $this->lists['appId'].'!'.$this->item->id; ?>" />
	<input type="hidden" name="picture" id="picture" value="" />
	<input type="hidden" name="object" value="media" />
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