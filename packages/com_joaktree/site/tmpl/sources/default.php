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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('bootstrap.modal', 'a.modal');
?>
<form action="<?php echo Route::_($this->lists['link']); ?>" method="post" id="adminForm" name="adminForm">
<?php echo HTMLHelper::_('form.token'); ?>

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<?php
    if ((is_object($this->canDo)) &&
            (
                $this->canDo->get('core.create')
            || $this->canDo->get('core.edit')
            || $this->canDo->get('core.delete')
            )
    ) {
        $colspan = 4;
        $indActions = true;
    } else {
        $colspan = 3;
        $indActions = false;
    }

    ?>


	<div id="jt-content">
		<table>
			<!-- header -->
			<thead>		
				<tr>
					<th colspan="<?php echo $colspan; ?>" class="jt-content-th">
						<div class="jt-h3-th" style="float: left;">
							<?php echo Text::_('JT_SOURCES'); ?>
						</div>
						<div style="float: right; display:inline-block;">
							<input type="text" name="search1" id="search1" value="<?php echo $this->lists['search1'];?>" class="text_area" size="30" onchange="document.joaktreeForm.submit();" />
							<!--<input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_('JT_SEARCH'); ?>" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" /> -->
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" />
						<!--<input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_('JT_RESET'); ?>" title="<?php echo Text::_('JT_TO_RESET'); ?>" />  -->
                        <input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_('JT_TO_RESET'); ?>" />          
						</div>
						<div class="clearfix"></div>                               
					</th>				
				</tr>		
				<tr>
					<th class="jt-content-th" width="5" align="center">
						<?php echo Text::_('JT_NUM'); ?>
					</th>
					<th class="jt-content-th" >
						<div class="jt-h3-list">
							<?php echo Text::_('JT_LABEL_NAME'); ?>
						</div>				
					</th>
					<th class="jt-content-th">
						<div class="jt-h3-list">
							<?php echo Text::_('JT_AUTHOR'); ?>
						</div>				
					</th>
					<?php if ($indActions) {?>
						<th class="jt-content-th">
							<div class="jt-h3-list">
								<?php echo Text::_('JT_ACTIONS'); ?>
							</div>				
						</th>
					<?php } ?>
				</tr>
			</thead>
			<!-- footer -->
			<tfoot>
				<tr align="center">
					<td colspan="<?php echo $colspan; ?>" >
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<!-- table body -->
			<tbody>
			
			<?php
            if ((is_object($this->canDo)) && ($this->canDo->get('core.create'))) {
                ?>
				<tr class="jt-table-entry1" >
					<td style="padding: 2px 5px;">&nbsp;</td>
					<td colspan="2" style="padding: 2px 5px;">&nbsp;</td>
					<td style="padding: 2px 5px;">
						<div class="jt-edit">
							<span style="display: none;">
								<input 
									type="checkbox" 
									id="add" 
									name="cid[]" 
									value="new" 
									onclick="isChecked(this.checked);" 
								/>
							</span>
							<a 	href="#"
								onclick="return Joomla.listItemTask('add', 'edit');"
								title="<?php echo Text::_('JTADD_DESC'); ?>" 
							>
								<?php echo Text::_('JTADD'); ?>
							</a>
						</div>
					</td>
				</tr>			
			<?php
            }
    ?>
			
			<!-- Show newly added item -->
			<?php if ($this->lists['status'] == 'new') { ?>
				<tr class="jt-table-entry1"
					id="addsrc"	
					<?php if ($this->lists['action'] != 'select') { ?>		
						onMouseOver="ShowPopup('addsrc', 'addsrcdet', 0, 38);return false;"
						onMouseOut="HidePopup( 'addsrcdet' );return false;"
					<?php } ?>
				>
					<td align="center">-</td>
					<td class="jt-just-changed">
						<?php echo $this->newItem->title; ?>
						<?php if ($this->lists['action'] != 'select') { ?>
							<!-- specification for the popup -->
							<div id="addsrcdet" class="jt-hide" style="position: absolute; z-index: 50;">
								<div class="jt-note" style="padding-bottom: 10px;">
									<?php if (!empty($this->newItem->publication)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_PUBLICATION'); ?></div>
										<div>
											<?php echo $this->newItem->publication; ?>
										</div>
									<?php } ?>
									<?php if (!empty($this->newItem->information)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_INFORMATION'); ?></div>
										<div>
											<?php echo $this->newItem->information; ?>
										</div>
									<?php } ?>
									<?php if (!empty($this->newItem->repository)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_REPOSITORY'); ?></div>
										<div>
											<?php echo $this->newItem->repository; ?>
										</div>													
									<?php } ?>
									<?php if (!empty($this->newItem->website)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_WEBSITE'); ?></div>
										<div>
											<a 	href="<?php echo $this->newItem->website; ?>" 
												target="_repo<?php echo $i; ?>"
											>
												<?php echo $this->newItem->website; ?>
											</a>
										</div>													
									<?php } ?>
									<?php if (empty($this->newItem->publication)
                                     && empty($this->newItem->information)
                                     && empty($this->newItem->repository)
                                     && empty($this->newItem->website)) {
									    ?>
										<div class="jt-h3"><?php echo Text::_('JT_NODATA'); ?></div>
									<?php } ?>
									<div>&nbsp;</div>
								</div>
							</div>
							<!-- End: specification for the popup -->
						<?php } ?>			
					</td>
					<td class="jt-just-changed"><?php echo $this->newItem->author; ?></td>
					<?php if ($indActions) { ?>
					<td>
						<span style="display: none;">
							<input 
								type="checkbox" 
								id="newitem" 
								name="cid[]" 
								value="<?php echo $this->newItem->id; ?>" 
								onclick="isChecked(this.checked);" 
							/>
						</span>
						<?php if ($this->lists['action'] == 'select') { ?>
							<?php
                                $function =  'window.parent.jtSelectSource' //(idid, titleid, id, title)
									            .'( \'src_'.$this->lists['counter'].'_id\''
									            .', \'src_'.$this->lists['counter'].'_name\''
									            .', \''.$this->newItem->id.'\''
									            .', \''.htmlspecialchars($this->newItem->title).'\''
									            .')';
						    ?>					
							<span class="jt-edit">
								<a 	href="#"
									onclick="if (window.parent) <?php echo $function; ?>;"
									title="<?php echo Text::_('JTSELECT_DESC'); ?>" 
								>
									<?php echo Text::_('JTSELECT'); ?>
								</a>
							</span>	
						<?php } else { ?>
						
							<?php if ((is_object($this->canDo)) && ($this->canDo->get('core.edit'))) {?>
								<span class="jt-edit">
									<a 	href="#"
										onclick="return Joomla.listItemTask('newitem', 'edit');"
										title="<?php echo Text::_('JT_EDIT_DESC'); ?>" 
									>
										<?php echo Text::_('JT_EDIT'); ?>
									</a>
								</span>
							<?php } ?>
							<?php if ((is_object($this->canDo)) && ($this->canDo->get('core.delete'))) {?>
								&nbsp;|&nbsp;
								<?php if ($this->newItem->indCitation) { ?>
									<span class="jt-edit-nolink" title="<?php echo Text::_('JT_NODELETE_DESC'); ?>">
										<?php echo Text::_('JT_DELETE'); ?>							
									</span>
								<?php } else { ?>
									<span class="jt-edit">
										<a 	href="#"
											onclick="return Joomla.listItemTask('newitem', 'delete');"
											title="<?php echo Text::_('JT_DELETE_DESC'); ?>" 
										>
											<?php echo Text::_('JT_DELETE'); ?>
										</a>
									</span>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</td>
					<?php } ?>
					
				</tr>
			<?php } ?>			
			<!-- End: Show newly added item -->
			
			
			<?php
            $k = 2;
    for ($i = 0, $n = count($this->items); $i < $n; $i++) {
        $row 		= $this->items[$i];
        $checked 	= HTMLHelper::_('grid.id', $i, $row->id);
        $rowclass 	= 'jt-table-entry' . $k;
        $showclass  = ($row->id == $this->lists['source_id']) ? 'jt-just-changed' : '';
        ?>
				<tr class="<?php echo $rowclass; ?>" 
					id="src<?php echo $i; ?>"	
					<?php if ($this->lists['action'] != 'select') { ?>				
						onMouseOver="ShowPopup('src<?php echo $i; ?>', 'srcdet<?php echo $i; ?>', 0, 38);return false;"
						onMouseOut="HidePopup( 'srcdet<?php echo $i; ?>' );return false;"
					<?php } ?>
				>
					<td align="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
					<td class="<?php echo $showclass; ?>">
                        <?php 
                    if ($this->lists['action'] != 'select') {
                        echo $row->title; 
                    } else {
                        $atitle = $row->title ?  htmlspecialchars($row->title) : '';
                        $function =  'window.parent.jtSelectSource' //(idid, titleid, id, title)
                                    .'( \'src_'.$this->lists['counter'].'_id\''
                                    .', \'src_'.$this->lists['counter'].'_name\''
                                    .', \''.$row->id.'\''
                                    .', \''.$atitle.'\''
                                    .')';?>
                                <a 	href="#"
									onclick="if (window.parent) <?php echo $function; ?>;"
									title="<?php echo Text::_('JTSELECT_DESC'); ?>" 
								>                                            
                                <?php echo $row->title;  ?>
                                </a>
                        <?php }?>
						<?php if ($this->lists['action'] != 'select') { ?>
							<!-- specification for the popup -->
							<div id="srcdet<?php echo $i; ?>" class="jt-hide" style="position: absolute; z-index: 50;">
								<div class="jt-note" style="padding-bottom: 10px;">
									<?php if (!empty($row->publication)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_PUBLICATION'); ?></div>
										<div>
											<?php echo $row->publication; ?>
										</div>
									<?php } ?>
									<?php if (!empty($row->information)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_INFORMATION'); ?></div>
										<div>
											<?php echo $row->information; ?>
										</div>
									<?php } ?>
									<?php if (!empty($row->repository)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_REPOSITORY'); ?></div>
										<div>
											<?php echo $row->repository; ?>
										</div>													
									<?php } ?>
									<?php if (!empty($row->abbr)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_ABBR'); ?></div>
										<div>
											<?php echo $row->abbr; ?>
										</div>													
									<?php } ?>
									<?php if (!empty($row->media)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_MEDIA'); ?></div>
										<div>
											<?php echo $row->media; ?>
										</div>													
									<?php } ?>
									<?php if (!empty($row->note)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_NOTE'); ?></div>
										<div>
											<?php $note =  str_replace('&#10;&#13;', '<br>', $row->note);
									    $note =  str_replace(PHP_EOL, '<br>', $note);
									    echo $note;?>
										</div>													
									<?php } ?>
									<?php if (!empty($row->www)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_WEBSITE'); ?></div>
										<div>
											<?php echo $row->www; ?>
										</div>													
									<?php } ?>
									<?php if (!empty($row->website)) { ?>
										<div class="jt-h3"><?php echo Text::_('JT_WEBSITE'); ?></div>
										<div>
											<a 	href="<?php
									          $str = "";
									    if (!str_starts_with($row->website, 'http://') &&
									        !str_starts_with($row->website, 'https://')) {
									        $str = "https://";
									    }
									    echo $str.$row->website; ?>" 
												target="_repo<?php echo $i; ?>"
											>
												<?php echo $row->website; ?>
											</a>
										</div>													
									<?php } ?>
									<?php if (empty($row->publication)
                                     && empty($row->information)
                                     && empty($row->repository)
                                     && empty($row->abbr)
                                     && empty($row->media)
                                     && empty($row->note)
                                     && empty($row->www)
                                     && empty($row->website)) {
									    ?>
										<div class="jt-h3"><?php echo Text::_('JT_NODATA'); ?></div>
									<?php } ?>
									<div>&nbsp;</div>
								</div>
							</div>
							<!-- End: specification for the popup -->
						<?php } ?>							
					</td>
					<td class="<?php echo $showclass; ?>"><?php echo $row->author; ?></td>
					
					<?php if ($indActions) { ?>
					<td>
						<span style="display: none;"><?php echo $checked; ?></span>
						<?php if ($this->lists['action'] == 'select') { ?>
							<span class="jt-edit">
								<a 	href="#"
									onclick="if (window.parent) <?php echo $function; ?>;"
									title="<?php echo Text::_('JTSELECT_DESC'); ?>" 
								>
									<?php echo Text::_('JTSELECT'); ?>
								</a>
							</span>	
						<?php } else { ?>
	
							<?php if ((is_object($this->canDo)) && ($this->canDo->get('core.edit'))) {?>
								<span class="jt-edit">
									<a 	href="#"
										onclick="return Joomla.listItemTask('cb<?php echo $i; ?>', 'edit');"
										title="<?php echo Text::_('JT_EDIT_DESC'); ?>" 
									>
										<?php echo Text::_('JT_EDIT'); ?>
									</a>
								</span>
							<?php } ?>
							<?php if ((is_object($this->canDo)) && ($this->canDo->get('core.delete'))) {?>
								&nbsp;|&nbsp;
								<?php if ($row->indCitation) { ?>
									<span class="jt-edit-nolink" title="<?php echo Text::_('JT_NODELETE_DESC'); ?>">
										<?php echo Text::_('JT_DELETE'); ?>							
									</span>
								<?php } else { ?>
									<span class="jt-edit">
										<a 	href="#"
											onclick="return Joomla.listItemTask('cb<?php echo $i; ?>', 'delete');"
											title="<?php echo Text::_('JT_DELETE_DESC'); ?>" 
										>
											<?php echo Text::_('JT_DELETE'); ?>
										</a>
									</span>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</td>
					<?php } ?>		
				</tr>
				<?php
                   $k = 3 - $k;
    }
    ?>
			</tbody>
		</table>
	</div> <!-- jt-content -->
	
	<div class="jt-clearfix jt-update">
	<?php
        if ($this->lists[ 'showchange' ] == 1) {
            $link =  Route::_(
                'index.php?&option=com_joaktree'
                    .(($this->lists['technology'] != 'b') ? '&tmpl=component' : '')
                    .'&view=changehistory'
                    .'&retId='.$this->lists[ 'retId' ]
            );
            $properties = ($this->lists['technology'] != 'b')
                ? 'class="modal"  rel="{handler: \'iframe\', size: {x: 875, y: 460}, onClose: function() {}}"'
                : 'rel="noindex, nofollow"';
            ?>
			<a href="<?php echo $link; ?>" <?php echo $properties; ?>>
				<?php echo Text::_('JT_CHANGEHISTORY'); ?>
			</a>
	<?php  } ?>
	</div>

<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>

<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="source" />

</form>