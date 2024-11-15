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
	action="<?php echo Route::_('index.php?option=com_joaktree&amp;view=jt_trees&amp;layout=element&amp;task=element&amp;tmpl=component&amp;object=id'); ?>" 
	method="post" 
	id="adminForm" 
	name="adminForm"
>
	<div id="j-main-container">
	
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
					<th class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JTFAMTREE_HEADING_TREE', 'jte.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="nowrap hidden-phone"> 
						<?php echo Text::_( 'JTFAMTREE_HEADING_TREEHOLDERID' ); ?>
					</th>
					<th class="nowrap hidden-phone"> 
						<?php echo Text::_( 'JTFAMTREE_HEADING_TREEHOLDER' ); ?>
					</th>				
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			
			<tbody>
				<?php foreach ($this->items as $i => $row) {
					//$linkname   = str_replace("'", "\&#39;", $row->firstName.' '.$row->familyName); // RRG 21/04/2017
					$linkname   = str_replace("'", "\&#39;", htmlentities($row->firstName).' '.$row->familyName);
					$appTitle	= str_replace("'", "\&#39;", $row->appTitle);
					$link		= 'window.parent.jSelectPerson(\''.$row->root_person_id.'\', \''.$linkname.'\', \''.$row->app_id.'\', \''.$appTitle.'\', \''.$row->id.'\');'; 
					$anker  	= 'style="cursor: pointer;" onclick="'.$link.'"';
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="nowrap center hidden-phone">
							<?php echo $this->pagination->getRowOffset( $i ); ?>
						</td>		
						<td class="nowrap hidden-phone">
							<a <?php echo $anker ?>>
								<?php echo $this->escape($row->name); ?>
							</a>
						</td>
						<td class="small hidden-phone">
							<a <?php echo $anker ?>>
								<?php echo $row->root_person_id; ?>
							</a>
						</td>
						<td class="small hidden-phone">
							<a <?php echo $anker ?>>
								<?php echo $row->firstName.' '.$row->familyName; ?>
							</a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		
		<input type="hidden" name="option" value="com_joaktree" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="controller" value="jt_trees" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>

</form>
