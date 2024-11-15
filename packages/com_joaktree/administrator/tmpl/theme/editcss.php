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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		//if (task == 'theme.cancel' || document.formvalidator.isValid(document.id('theme-form'))) {
		if (task == 'theme.cancel' || document.formvalidator.isValid(document.getElementById('theme-form'))) {
			Joomla.submitform(task, document.getElementById('theme-form'));
		} else {
			alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form 
	action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" 
	method="post" 
	name="adminForm" 
	id="theme-form" 
	class="form-validate form-horizontal"
>
<div class="span10 form-horizontal">
	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#details" data-toggle="tab">
				<?php echo Text::sprintf('JTTHEME_TITLE_EDITCSS', ucfirst($this->item->name)); ?>
				</a>
			</li>
		</ul>
		
		<!-- content starts here -->
		<div class="tab-content">
			<div class="tab-pane active" id="details">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('source'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('source'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('sourcepath'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('sourcepath'); ?>
					</div>
				</div>
			</div>
		</div>

	</fieldset>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="controller" value="themes" />
	<input type="hidden" name="caller" value="editcss" />
	<?php echo HTMLHelper::_('form.token'); ?>

</div>
</form>
