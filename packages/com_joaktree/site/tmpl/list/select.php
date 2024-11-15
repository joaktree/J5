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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

?>
<form action="<?php echo Route::_( 'index.php?option=com_joaktree&amp;view=list&amp;tmpl=component&amp;layout=select&amp;treeId='.$this->lists['tree_id'].'&amp;action=select'); ?>" method="post" id="adminForm" name="adminForm">
<?php echo HTMLHelper::_( 'form.token' ); ?>

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->

	<div id="jt-content">
		<table>
			<!-- header -->
			<thead>				
				<tr>
					<th class="jt-content-th" width="5" rowspan="2" align="center">
						<?php echo Text::_( 'JT_NUM' ); ?>
					</th>
					<th class="jt-content-th">
						<div class="jt-h3-list jt-content-tha">
							<?php echo HTMLHelper::_('grid.sort', Text::_('JT_FIRSTNAME'), 'jpn.firstName', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</div>
					</th>
					<?php if ($this->lists['patronym'] != 0) { ?>
					  <th class="jt-content-th">
						<div class="jt-h3-list jt-content-tha">
							<?php echo HTMLHelper::_('grid.sort',  Text::_('JT_PATRONYM'), 'jpn.patronym', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</div>
					  </th>
					<?php } ?>
					<th class="jt-content-th">
						<div class="jt-h3-list jt-content-tha">
							<?php echo HTMLHelper::_('grid.sort',  Text::_('JT_FAMILYNAME'), 'jpn.familyName', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</div>
					</th>
					<th class="jt-content-th" align="center">
						<div class="jt-h3-list">
							<?php echo Text::_( 'BIRT' ); ?>
						</div>
					</th>
					<th class="jt-content-th" align="center">
						<div class="jt-h3-list">
							<?php echo Text::_( 'DEAT' ); ?>
						</div>
					</th>
				</tr>
				<tr>
					<th class="jt-content-th">
						<input 
							type="text" 
							name="search1" 
							id="search1" 
							value="<?php echo $this->lists['search1'];?>" 
							class="text_area" 
							style="width: <?php echo $this->lists['searchWidth'];?>px;" 
							onchange="document.adminForm.submit();" 
						/>
						<!--<input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_( 'JT_SEARCH' ); ?>" title="<?php echo Text::_( 'JT_TO_SEARCH' ); ?>" /> -->
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_( 'JT_TO_SEARCH' ); ?>" />
						<!--<input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_( 'JT_RESET' ); ?>" title="<?php echo Text::_( 'JT_TO_RESET' ); ?>" />  -->
                        <input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_( 'JT_TO_RESET' ); ?>" />                                         
					</th>
					<?php if ($this->lists['patronym'] != 0) { ?>
					  <th class="jt-content-th">
						<input 
							type="text" 
							name="search2" 
							id="search2" 
							value="<?php echo $this->lists['search2'];?>" 
							class="text_area" 
							style="width: <?php echo $this->lists['searchWidth'];?>px;" 
							onchange="document.adminForm.submit();" 
						/>
						<!--<input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_( 'JT_SEARCH' ); ?>" title="<?php echo Text::_( 'JT_TO_SEARCH' ); ?>" /> -->
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_( 'JT_TO_SEARCH' ); ?>" />
						<!--<input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_( 'JT_RESET' ); ?>" title="<?php echo Text::_( 'JT_TO_RESET' ); ?>" />  -->
                        <input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_( 'JT_TO_RESET' ); ?>" />                                         
					  </th>
					<?php } ?>
					<th class="jt-content-th">
						<input 
							type="text" 
							name="search3" 
							id="search3" 
							value="<?php echo $this->lists['search3'];?>" 
							class="text_area" 
							style="width: <?php echo $this->lists['searchWidth'];?>px;" 
							onchange="document.adminForm.submit();" 
						/>
						<!--<input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_( 'JT_SEARCH' ); ?>" title="<?php echo Text::_( 'JT_TO_SEARCH' ); ?>" /> -->
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_( 'JT_TO_SEARCH' ); ?>" />
						<!--<input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_( 'JT_RESET' ); ?>" title="<?php echo Text::_( 'JT_TO_RESET' ); ?>" />  -->
                        <input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_( 'JT_TO_RESET' ); ?>" />          
					</th>
					<th class="jt-content-th" align="center">&nbsp;
						
					</th>
					<th class="jt-content-th" align="center">&nbsp;
						
					</th>
				</tr>
			</thead>
			<!-- footer -->
			<tfoot>
				<?php 
					if ($this->lists['patronym'] != 0) { 
						$colspanValue = 6;
					} else {
						$colspanValue = 5;
					}
				?>
				<tr align="center">
					<td colspan="<?php echo $colspanValue; ?>" >
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<!-- table body -->
			<tbody>
			<?php
			$k = 2;
			for ($i=0, $n=count( $this->personlist ); $i < $n; $i++)	{
				$row 		= $this->personlist[$i];
				$rowclass 	= 'jt-table-entry' . $k;			
				$name		=  $row->firstName.' '.$row->familyName;
				$function 	= 'window.parent.jtSelectPerson(\''.$row->app_id.'\', \''.$row->id.'\', \''.$name.'\')';
			?>
				<tr class="<?php echo $rowclass; ?>" onclick="if (window.parent) <?php echo $function; ?>;">
					<td align="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
					<td><?php echo $row->firstName; ?></td>
					<?php if ($this->lists['patronym'] != 0) { ?>
						<td><?php echo $row->patronym; ?></td>
					<?php } ?>
					<td><?php echo $row->familyName; ?></td>
					<td><?php echo $row->birthDate; ?></td>
					<td><?php echo $row->deathDate; ?></td>
				</tr>
				<?php
			       $k = 3 - $k;
			}
			?>
			</tbody>
		</table>
	</div> <!-- jt-content -->
	
<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
	</div>
<?php } ?>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="treeId" value="<?php echo $this->lists['tree_id']; ?>" />
<input type="hidden" name="view" value="list" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />

</form>