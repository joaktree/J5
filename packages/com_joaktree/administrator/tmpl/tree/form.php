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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// are these needed
HtmlHelper::_('bootstrap.tooltip');
HtmlHelper::_('bootstrap.modal','.modal', []);
HtmlHelper::_('behavior.formvalidator');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$name 		= 'personId';
$linkPerson = 'index.php?option=com_joaktree&amp;view=persons&amp;layout=element&amp;task=element&amp;tmpl=component&amp;object='.$name;
$clrPerson  = 'window.parent.jClearPerson();';

?>

<form 
	action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" 
	method="post" 
	name="adminForm" 
	id="tree-form" 
	class="form-validate form-horizontal"
>
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php $titletab = (empty($this->item->id) ? Text::_('JTTREE_TITLE_NEWNAME') : Text::sprintf('JTTREE_TITLE_EDITNAME', ucfirst($this->item->name)));
echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', $titletab); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField("name") ; ?>
                <?php echo $this->form->renderField('id');   ?>
                <?php echo $this->form->renderField('app_id'); ?>
                <?php echo $this->form->renderField('holds'); ?>
                <?php //echo $this->form->renderFieldset('app_id');?>
                <?php //echo $this->form->renderField('holds');?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('personName'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('personName', null, (is_object($this->item)) ? $this->item->rootPersonName : null); ?>
						<div class="btn btn-small">
                            <!-- modal !-->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#selectperson"><?php echo Text::_('JTFIELD_PERSON_BUTTONDESC_PERSON'); ?></button>
                            <div class="modal fade modal-xl"  id="selectperson" tabindex="-1" aria-labelledby="selectperson" aria-hidden="true">
                                <div class="modal-dialog h-75">
                                    <div class="modal-content h-100">
                                        <div class="modal-header">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body h-100">
                                            <iframe id="iframeModalWindow" height="100%" src="<?php echo $linkPerson; ?>" name="iframe_modal"></iframe>      
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <!-- fin de modal !-->
							<!--- a class="modal-button"
                            title="<?php echo Text::_('JTFIELD_PERSON_BUTTONDESC_PERSON'); ?>"  href="<?php echo $linkPerson; ?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}" >
								<?php echo Text::_('JTFIELD_PERSON_BUTTON_PERSON'); ?>
							</a!--->
						</div>
						<div class="btn btn-small">
							<a title="<?php echo Text::_('JTTREE_TOOLTIP_CLEAR'); ?>"  onclick="<?php echo $clrPerson; ?>" >
								<?php echo Text::_('JTTREE_LABEL_CLEAR'); ?>
							</a>
						</div>	
						
					</div>
					<div class="control-label">
						<?php echo $this->form->getLabel('root_person_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('root_person_id'); ?>
					</div>

				</div>     
                <?php //echo $this->form->renderField('personname');?>
                <?php //echo $this->form->renderField('root_person_id');?>
                <?php echo $this->form->renderField('theme_id'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('catid'); ?>
                <?php echo $this->form->renderField('indGendex'); ?>
                <?php echo $this->form->renderField('indPersonCount'); ?>
                <?php echo $this->form->renderField('indMarriageCount'); ?>
                <?php echo $this->form->renderField('robots'); ?>
           	</div>
            <div class="col-lg-3">
                <?php //echo LayoutHelper::render('joomla.edit.global', $this);?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>


                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JTAPPS_PERMISSIONS')); ?>
						<fieldset class="panelform">
							<?php echo $this->form->getLabel('rules'); ?>
							<?php echo $this->form->getInput('rules'); ?>
						</fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cid[]" value="<?php echo(!empty($this->item->id) ? $this->item->id : null); ?>" />
	<input type="hidden" name="controller" value="trees" />
	<?php echo HtmlHelper::_('form.token'); ?>
	
</div>
</form>
