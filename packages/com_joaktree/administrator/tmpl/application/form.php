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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('bootstrap.tooltip');

HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('formbehavior.chosen', 'select');
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$linkUpload = 'index.php?option=com_joaktree&amp;view=upload&amp;tmpl=component&amp;appId='.$this->item->id;
$linkViewlog = 'index.php?option=com_joaktree&amp;view=viewlogs&amp;tmpl=component&amp;appId='.$this->item->id;
?>
<form 
	action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" 
	method="post" 
	name="adminForm" 
	id="application-form" 
	class="form-validate form-horizontal"
>
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php $titletab = (empty($this->item->id) ? "- ".Text::_('JTAPPS_TITLE_NEWNAME'). '&nbsp;-&nbsp;' : "- ".Text::sprintf('JTAPPS_TITLE_EDITNAME', ucfirst($this->item->title) . '&nbsp;'));
echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', $titletab); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('description'); ?>
                <?php echo $this->form->renderField('programName'); ?>
                <?php echo $this->form->renderField('id'); ?>
                <!-- Modal !-->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewlog"><?php echo Text::_('JTFIELD_LOGS_BUTTONDESC'); ?></button>
                <div class="modal fade modal-xl"  id="viewlog" tabindex="-1" aria-labelledby="upload" aria-hidden="true">
                    <div class="modal-dialog h-75">
                        <div class="modal-content h-100">
                             <div class="modal-header">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                             </div>
                             <div class="modal-body h-100">
                                <iframe id="iframeModalWindow" height="100%" src="<?php echo $linkViewlog; ?>" name="iframe_modal"></iframe>      
                             </div>
                        </div>
                    </div>
                </div>
                <!-- Fin de modal !-->
           	</div>
            <div class="col-lg-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php

        echo HTMLHelper::_('uitab.addTab', 'myTab', 'settings', Text::_('JTAPPS_TITLE_PARAMS')); ?>
            <fieldset id="fieldset-otherparams" class="options-form">
                <legend><?php echo Text::_('JTAPPS_TITLE_PARAMS'); ?></legend>
                <div>
                         <div class="control-group">
							<div class="controls">
                            <!-- Modal !-->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#upload"><?php echo Text::_('JTFIELD_UPLOAD_BUTTONDESC'); ?></button>
                            <div class="modal fade modal-xl"  id="upload" tabindex="-1" aria-labelledby="upload" aria-hidden="true">
                                <div class="modal-dialog h-50">
                                    <div class="modal-content h-100">
                                        <div class="modal-header">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body h-100">
                                            <iframe id="iframeModalWindow" height="100%" src="<?php echo $linkUpload; ?>" name="iframe_modal"></iframe>      
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- Fin de modal !-->
                            </div>
                            </div>
				<?php foreach($this->form->getFieldset('settings') as $field): ?>
						<?php if ($field->hidden) { ?>
                        <div class="control-group">
							<div class="controls">
							<?php echo $field->input; ?>
							</div>
                        </div>
						<?php } else {
						    echo $this->form->renderField($field->fieldname, $field->group);
						} ?>
				<?php endforeach; ?>
                </div>
            </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JTAPPS_PERMISSIONS')); ?>
						<fieldset class="panelform">
							<?php echo $this->form->getLabel('rules'); ?>
							<?php echo $this->form->getInput('rules'); ?>
						</fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="controller" value="applications" />
	<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>
	<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		console.log(document.getElementById("application-form"));
		if (task == 'application.cancel' ) {
			Joomla.submitform(task, document.getElementById("application-form"));
		} else if (document.formvalidator.isValid(document.getElementById("application-form"))) {
			console.log("enregistrement");
			Joomla.submitform(task, document.getElementById("application-form"));
		}else {
			alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>