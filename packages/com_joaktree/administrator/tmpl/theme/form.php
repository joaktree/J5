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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip');

HTMLHelper::_('behavior.formvalidator');
// The chosen selector doesn't work properly with the map-icons selection box.
// It is disabled here.
//JHtml::_('formbehavior.chosen', 'select');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		//if (task == 'theme.cancel' || document.formvalidator.isValid(document.id('theme-form'))) {
		if (task == 'themes.cancel' || document.formvalidator.isValid(document.getElementById('theme-form'))) {
			Joomla.submitform(task, document.getElementById('theme-form'));
		} else {
			alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form 
	action="<?php echo Route::_('index.php?option=com_joaktree&view=themes'); ?>" 
	method="post" 
	name="adminForm" 
	id="theme-form" 
	class="form-validate form-horizontal"
>
<div class="span10 form-horizontal">
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php $titletab = (empty($this->item->id) ? Text::_('JTTHEME_TITLE_NEWNAME') : Text::sprintf('JTTHEME_TITLE_EDITNAME', ucfirst($this->item->name)));
		echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', $titletab); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php if (empty($this->item->id)) {
					echo $this->form->renderField("newname"); 
                    echo $this->form->renderField("theme"); 
				} else {
					echo $this->form->renderField("name");
				}?>
                <?php echo $this->form->renderField('id');   ?>
           	</div>
            <div class="col-lg-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>


         <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'params', Text::_('JTTHEME_TITLE_PARAMS')); ?>
            <fieldset id="fieldset-otherparams" class="options-form">
                <legend><?php echo Text::_('JTTHEME_TITLE_PARAMS'); ?></legend>
                <div>
				<?php foreach($this->form->getFieldset('settings') as $field):
						    echo $this->form->renderField($field->fieldname, $field->group);
				endforeach; ?>
                </div>
            </fieldset>        
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
	<fieldset>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="controller" value="themes" />
	<input type="hidden" name="caller" value="form" />
	<?php echo HTMLHelper::_('form.token'); ?>

</div>
</form>