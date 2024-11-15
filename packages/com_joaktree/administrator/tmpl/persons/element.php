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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HtmlHelper::_('formbehavior.chosen', 'select');

$sortFields = $this->getSortFields();

?>

<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $this->lists['order']; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form 
	action="<?php echo Route::_('index.php?option=com_joaktree&amp;view=persons&amp;layout=element&amp;task=element&amp;tmpl=component&amp;object=id'); ?>" 
	method="post" 
	id="adminForm" 
	name="adminForm"
>
	<div id="j-main-container">
		<!--  filter row -->
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<div class="btn-group pull-left">
					<button 
						type="button" 
						class="btn hasTooltip" 
						title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"
						onclick="this.form.submit();">
						<i class="icon-search"></i>
					</button>
					<div class="btn-group pull-left">
						&nbsp;
					</div>
				</div>
			</div>

			<div class="filter-search btn-group ">
				<div class="btn-group pull-left">
					<label for="search1" class="element-invisible"><?php echo Text::_('JTPERSONS_HEADING_FIRSTNAME');?></label>
					<input 
						type="text" 
						name="search1" 
						id="search1" 
						title="<?php echo Text::_('JTPERSONS_HEADING_FIRSTNAME');?>" 
						placeholder="<?php echo Text::_('JTPERSONS_HEADING_FIRSTNAME');?>" 
						value="<?php echo $this->escape($this->lists['search1']); ?>" 
					/>
				</div>
				<div class="btn-group pull-left">
					<button 
						type="button" 
						class="btn hasTooltip" 
						title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" 
						onclick="document.getElementById('search1').value='';this.form.submit();">
						<i class="icon-remove"></i>
					</button>
					&nbsp;
				</div>
			</div>
			
			<?php if ($this->lists['patronym']) { ?>
				<div class="filter-search btn-group >">
					<div class="btn-group pull-left">
						<label for="search2" class="element-invisible"><?php echo Text::_('JTPERSONS_HEADING_PATRONYM');?></label>
						<input 
							type="text" 
							name="search2" 
							id="search2" 
							title="<?php echo Text::_('JTPERSONS_HEADING_PATRONYM');?>" 
							placeholder="<?php echo Text::_('JTPERSONS_HEADING_PATRONYM');?>" 
							value="<?php echo $this->escape($this->lists['search2']); ?>" 
						/>
					</div>
					<div class="btn-group pull-left">
						<button 
							type="button" 
							class="btn hasTooltip" 
							title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" 
							onclick="document.getElementById('search2').value='';this.form.submit();">
							<i class="icon-remove"></i>
						</button>
						&nbsp;
					</div>
				</div>
			<?php } ?>
							
			<div class="filter-search btn-group ">
				<div class="btn-group pull-left">
					<label for="search3" class="element-invisible"><?php echo Text::_('JTPERSONS_HEADING_FAMNAME');?></label>
					<input 
						type="text" 
						name="search3" 
						id="search3" 
						title="<?php echo Text::_('JTPERSONS_HEADING_FAMNAME');?>" 
						placeholder="<?php echo Text::_('JTPERSONS_HEADING_FAMNAME');?>" 
						value="<?php echo $this->escape($this->lists['search3']); ?>" 
					/>
				</div>
				<div class="btn-group pull-left">
					<button 
						type="button" 
						class="btn hasTooltip" 
						title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" 
						onclick="document.getElementById('search3').value='';this.form.submit();">
						<i class="icon-remove"></i>
					</button>
					&nbsp;
				</div>
			</div>
		</div>
		
		<!--  show columns row row -->
		<div id="columns-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo Text::_('JFIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($this->lists['order_Dir'] == 'asc') echo 'selected="selected"'; ?>><?php echo Text::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($this->lists['order_Dir'] == 'desc') echo 'selected="selected"'; ?>><?php echo Text::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo Text::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JGLOBAL_SORT_BY');?></option>
					<?php echo HtmlHelper::_('select.options', $sortFields, 'value', 'text', $this->lists['order']);?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		
		<!--  table -->
		<div id="editcell">
		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo Text::_( 'JT_HEADING_NUMBER' ); ?>
					</th>
					<th width="2%" class="nowrap center">
						<?php echo HtmlHelper::_('grid.sort',  'JT_HEADING_ID', 'jpn.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>			
					<th class="nowrap">
						<?php echo HtmlHelper::_('grid.sort',  'JTPERSONS_HEADING_FIRSTNAME', 'jpn.firstName', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<?php if ($this->lists['patronym']) { ?>				
						<th class="nowrap">
							<?php echo HtmlHelper::_('grid.sort',  'JTPERSONS_HEADING_PATRONYM', 'jpn.patronym', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
					<?php } ?>						
					<th class="nowrap"> 
						<?php echo HtmlHelper::_('grid.sort',  'JTPERSONS_HEADING_FAMNAME', 'jpn.familyName', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>					
					<th class="nowrap">
						<?php echo Text::_( 'BIRT' ); ?>
					</th>
					<th class="nowrap">
						<?php echo Text::_( 'JTPERSONS_HEADING_APPTITLE' ); ?>
					</th>					
					<th class="nowrap"> 
						<?php echo Text::_( 'JTPERSONS_HEADING_DEFTREE' ); ?>
					</th>				
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td id="footer" colspan="<?php echo ($this->lists['patronym']) ? 8 : 7; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>

			<tbody>
				<?php foreach ($this->items as $i => $row) {					
					//$linkname  	= str_replace("'", "\&#39;", $row->firstName.' '.$row->familyName); // RRG 21/04/2017
					$linkname  	= str_replace("'", "\&#39;", htmlentities($row->firstName).' '.$row->familyName);
					$appTitle  	= str_replace("'", "\&#39;", $row->appTitle);
					$link 		= 'window.parent.jSelectPerson(\''.$row->id.'\', \''.$linkname.'\', \''.$row->app_id.'\', \''.$appTitle.'\', \''.$row->default_tree_id.'\');';
					$anker  	= 'style="cursor: pointer;" onclick="'.$link.'"';	
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="nowrap center hidden-phone">
							<?php echo $this->pagination->getRowOffset( $i ); ?>
						</td>
						<td class="small">
							<a <?php echo $anker ?>>
								<?php echo $row->id; ?>
							</a>
						</td>
						<td class="">
							<a <?php echo $anker ?>>
								<?php echo $row->firstName;?>
							</a>
						</td>
						<?php if ($this->lists['patronym']) { ?>
							<td class="small">
								<a <?php echo $anker ?>>
									<?php echo $row->patronym;?>
								</a>
							</td>
						<?php } ?>
						<td class="">
							<a <?php echo $anker ?>>
								<?php echo $row->familyName;?>
							</a>
						</td>
						<td class="center small">
							<?php echo $row->birthDate;?>
						</td>
						<td class="small">
							<?php echo $this->escape($row->appTitle);?>
						</td>
						<td class="small">
							<?php echo $this->escape($row->familyTree);?>
						</td>
					</tr>
				<?php } ?>
			</tbody>

		</table>
	</div>
		
	<input type="hidden" name="option" value="com_joaktree" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="persons" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HtmlHelper::_('form.token'); ?>
		
</form>
