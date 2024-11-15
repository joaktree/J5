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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;


// are these needed

HtmlHelper::_('bootstrap.modal','.modal', []);
HTMLHelper::_('bootstrap.tooltip');

HTMLHelper::_('behavior.formvalidator');
        HTMLHelper::_('behavior.core');
        HTMLHelper::_('jquery.framework');
        HTMLHelper::_('formbehavior.chosen');
        HTMLHelper::_('script', 'legacy/ajax-chosen.min.js', ['version' => 'auto', 'relative' => true]);

$linkPerson = 'index.php?option=com_joaktree&amp;view=persons&amp;layout=element&amp;task=element&amp;tmpl=component&amp;object=personId';
$clrPerson  = 'window.parent.jClearPerson();'; 	

        $lang 	= Factory::getLanguage();
        $lang->load('com_joaktree.gedcom', JPATH_ADMINISTRATOR);


?>
<form 
	action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" 
	method="post" 
	name="adminForm" 
	id="location-form" 
	class="form-validate form-horizontal"
>
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>
        <?php $titletab = (empty($this->item->id) ? "- ".Text::_('JTMAP_TITLE_NEWNAME'). '&nbsp;-&nbsp;' :  "- ".Text::sprintf('JTMAP_TITLE_EDITNAME', ucfirst($this->item->name) . '&nbsp;'));
		echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', $titletab); ?>
        <div class="row">
            <div class="col-lg-9">
               <?php echo $this->form->renderField('name'); ?>
                <?php echo $this->form->renderField('service'); ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('selection'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('selection'); ?>
					</div>
				</div>                
				<?php //echo ($this->item->selection); ?>
 				<?php switch ($this->item->selection) {
					    case "person"	  : $classPerson     = 'jt-show';
					    					$classTree 	     = 'jt-hide';
					    					$classLocation   = 'jt-hide';
					    					break;
					    case "location"	  : $classPerson     = 'jt-hide';
					    					$classTree 	     = 'jt-show';
					    					$classLocation   = 'jt-show';
					    					break;
					    case "tree"		  : 
						default			  : $classPerson     = 'jt-show';
											$classTree 	     = 'jt-show';
					    					$classLocation   = 'jt-hide';
											break; 
					  }
				?>               
                <div id="tree" class="control-group <?php echo $classTree; ?>"
                <?php //echo $this->form->renderField('tree'); ?>
                </div>
<div id="tree" class="control-group <?php echo $classTree; ?>">
					<div class="control-label">
						<?php echo $this->form->getLabel('tree'); ?>
					</div>
					<div class="controls">
						<?php 
						echo $this->form->getInput('tree', null, (is_object($this->item)) ? $this->item->tree_id : null); ?>
					</div>
				</div> 
                <div class="col-lg-9">
                <?php echo $this->form->renderField('descendants'); ?>
                <?php echo $this->form->renderField('familyName'); ?>
                </div>
                				<!--  person  -->
				<div id="person1" class="control-group <?php echo $classPerson; ?>"
                <?php echo $this->form->renderField('personName'); ?>
					<div class="control-label">
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('root_person_id', null, (is_object($this->item)) ? $this->item->person_id : null); ?>
						<?php echo $this->form->getInput('app_id'); ?>
                        <!-- Modal !-->
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
                        <!-- Fin de modal !-->
						<div class="btn btn-small" ;">
							<!--a class='' title="<?php echo Text::_('JTFIELD_PERSON_BUTTONDESC_PERSON'); ?>"  href="<?php echo $linkPerson; ?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}" >
                            <?php echo Text::_('JTFIELD_PERSON_BUTTON_PERSON'); ?>
							</a!-->
						</div>
						<div class="btn btn-small">
							<a title="<?php echo Text::_(''); ?>"  onclick="<?php echo $clrPerson; ?>" >
								<?php echo Text::_('JGLOBAL_SELECTION_NONE'); ?>
							</a>
						</div>	
					</div>
				</div>
				<div class="col-lg-9">
				<div id="relations" class="control-group <?php echo $classPerson; ?>">
					<div class="control-label">
						<?php echo $this->form->getLabel('person_relations'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('person_relations'); ?>
					</div>
				</div>
				<!--  End person  -->	
                 <?php echo $this->form->renderField('app_id'); ?>
                 <?php echo $this->form->renderField('period_start'); ?>
                 <?php echo $this->form->renderField('period_end'); ?>
                 <?php echo $this->form->renderField('events'); ?>
                <?php echo $this->form->renderField('id'); ?>
           	</div>
            <div class="col-lg-3">
                <?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
		</div></div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'settings', Text::_('JTMAP_TITLE_PARAMS')); ?>
            <fieldset id="fieldset-otherparams" class="options-form">
                <legend><?php echo Text::_('JTMAP_TITLE_PARAMS'); ?></legend>
                <div>
                <?php echo $this->form->renderField('params'); ?>
				<?php foreach($this->form->getFieldset('settings') as $field): 
						    echo $this->form->renderField($field->fieldname, $field->group);
				 endforeach; ?>
                </div>
            </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'map-advsettings', Text::_('JTMAP_TITLE_ADVPARAMS')); ?>
            <fieldset id="fieldset-otherparams" class="options-form">
                <legend><?php echo Text::_('JTMAP_TITLE_ADVPARAMS'); ?></legend>
                <div>
				<?php foreach($this->form->getFieldset('adv-settings') as $field):
						    echo $this->form->renderField($field->fieldname, $field->group);
				 endforeach; ?>
                </div>
            </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
<input type="hidden" name="task" value="" />
<input type="hidden" name="cid[]" value="<?php echo (!empty($this->item->id) ? $this->item->id : null); ?>" />
<input type="hidden" name="controller" value="maps" />
<?php echo HtmlHelper::_('form.token'); ?>
</form>

