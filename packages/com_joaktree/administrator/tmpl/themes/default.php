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

HTMLHelper::_('formbehavior.chosen', 'select');

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
	action="<?php echo Route::_('index.php?option=com_joaktree&view=themes'); ?>" 
	method="post" 
	id="adminForm" 
	name="adminForm"
>

<?php if(!empty( $this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php  $divClassSpan = 'span10'; ?>
<?php } else { ?>
	<?php  $divClassSpan = ''; ?>	
<?php } ?>
	<div id="j-main-container" class="<?php echo $divClassSpan; ?>">

		<!--  filter row -->
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="search" class="element-invisible"><?php echo Text::_('JT_LABEL_FILTER');?></label>
				<input 
					type="text" 
					name="search" 
					id="search"
					placeholder="<?php echo Text::_('JT_LABEL_FILTER');?>"  
					value="<?php echo $this->escape($this->lists['search']); ?>" 
				/>
			</div>
			<div class="btn-group pull-left">
				<button 
					type="button" 
					class="btn hasTooltip" 
					title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"
					onclick="this.form.submit();">
					<i class="icon-search"></i>
				</button>
				<button 
					type="button" 
					class="btn hasTooltip" 
					title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" 
					onclick="document.getElementById('search').value='';this.form.submit();">
					<i class="icon-remove"></i>
				</button>
			</div>
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
		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo Text::_( 'JT_HEADING_NUMBER' ); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>			
					<th class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JTTHEMES_HEADING_THEME', 'jtmp.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th width="5%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JTTHEMES_HEADING_DEFAULT', 'jtmp.home', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>					
					<th width="2%" class="nowrap center hidden-phone">
						<?php echo Text::_( 'JT_HEADING_ID' ); ?>
					</th>
				
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->items as $i => $row) {
					$click  = 'return Joomla.listItemTask(\'cb'.$i.'\', \'edit\')';
					//$click  = 'return listItemTask(\'cb'.$i.'\', \'edit\')';					
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="nowrap center hidden-phone">
							<?php echo $this->pagination->getRowOffset( $i ); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo HTMLHelper::_('grid.id',   $i, $row->id ); ?>
						</td>			
						<td class="nowrap hidden-phone">
							<?php if ($this->canDo->get('core.edit')) { ?>
								<a href="javascript:void(0);" onclick="<?php echo $click; ?>" title="<?php echo Text::_( 'JTTHEMES_TOOLTIP_EDIT' ); ?>">
									<?php echo $this->escape($row->name); ?>
								</a>
							<?php } else { ?>
									<?php echo $this->escape($row->name); ?>
							<?php } ?>
						</td>	
						<td class="center hidden-phone">
							<?php echo HTMLHelper::_('jgrid.isdefault', $row->home, $i, '', !$row->home);?>
						</td>
						<td class="center small hidden-phone">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	
		<input type="hidden" name="option" value="com_joaktree" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="controller" value="themes" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>
</form>
	