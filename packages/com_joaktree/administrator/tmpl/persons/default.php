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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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
	action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" 
	method="post" 
	id="adminForm" 
	name="adminForm"
>

<?php
    $shownColumns = 8;
if ($this->columns['app']) {
    $classApp  = 'jt-show-app' ;
    $shownColumns++;
} else {
    $classApp  = 'jt-hide-app' ;
}
if ($this->columns['pat']) {
    $classPat  = 'jt-show-pat' ;
    $shownColumns++;
} else {
    $classPat  = 'jt-hide-pat' ;
}
if ($this->columns['per']) {
    $classPer  = 'jt-show-per' ;
    $shownColumns++;
} else {
    $classPer  = 'jt-hide-per' ;
}
if ($this->columns['tree']) {
    $classTree = 'jt-show-tree';
    $shownColumns++;
} else {
    $classTree = 'jt-hide-tree';
}
if ($this->columns['rob']) {
    $classRob  = 'jt-show-rob' ;
    $shownColumns++;
} else {
    $classRob  = 'jt-hide-rob' ;
}
//if ($this->columns['map']) {
// always show map choices    
    $classMap  = 'jt-show-map' ;
    $shownColumns++;
//} else {
//    $classMap  = 'jt-hide-map' ;
//}
?>

<?php if (!empty($this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php  $divClassSpan = 'span10'; ?>
<?php } else { ?>
	<?php  $divClassSpan = ''; ?>	
<?php } ?>

	<div id="j-main-container" class="<?php echo $divClassSpan; ?>">

		<!--  filter row -->
		<div id="filter-bar" class="btn-toolbar" style="float:left">
			<div class="filter-search btn-group" >
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
                        onkeypress="checkenter(event);"
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
                            onkeypress="checkenter(event);"
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
                        onkeypress="checkenter(event);"
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
			<div class="btn-group pull-left" style="display:none">
				<button 
					type="button" 
					class="btn hasTooltip" 
					onclick="javascript:jt_toggle('th','app');jt_toggle('td','app');">
					<?php echo Text::_('JTPERSONS_HEADING_APPTITLE'); ?>
				</button>
				<?php if ($this->lists['patronym']) { ?>
					<button 
						type="button" 
						class="btn hasTooltip" 
						onclick="javascript:jt_toggle('th','pat');jt_toggle('td','pat');">
						<?php echo Text::_('JTPERSONS_HEADING_PATRONYM'); ?>
					</button>
				<?php } ?>
				<button 
					type="button" 
					class="btn hasTooltip" 
					onclick="javascript:jt_toggle('th','per');jt_toggle('td','per');">
					<?php echo Text::_('JTPERSONS_HEADING_PERIOD'); ?>
				</button>
				<button 
					type="button" 
					class="btn hasTooltip" 
					onclick="javascript:jt_toggle('th','tree');jt_toggle('td','tree');">
					<?php echo Text::_('JTPERSONS_HEADING_DEFTREE'); ?>
				</button>
				<button 
					type="button" 
					class="btn hasTooltip" 
					onclick="javascript:jt_toggle('th','map');jt_toggle('td','map');">
					<?php echo Text::_('JT_HEADING_MAP'); ?>
				</button>
				<button 
					type="button" 
					class="btn hasTooltip" 
					onclick="javascript:jt_toggle('th','rob');jt_toggle('td','rob');">
					<?php echo Text::_('JFIELD_METADATA_ROBOTS_LABEL'); ?>
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
					<option value="asc" <?php if ($this->lists['order_Dir'] == 'asc') {
					    echo 'selected="selected"';
					} ?>><?php echo Text::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($this->lists['order_Dir'] == 'desc') {
					    echo 'selected="selected"';
					} ?>><?php echo Text::_('JGLOBAL_ORDER_DESCENDING');?></option>
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
						<?php echo Text::_('JT_HEADING_NUMBER'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>					
					<th class="nowrap hidden-phone <?php echo $classApp; ?>">
						<?php echo Text::_('JTPERSONS_HEADING_APPTITLE'); ?>
					</th>					
					<th width="2%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JT_HEADING_ID', 'jpn.id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>			
					<th class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JTPERSONS_HEADING_FIRSTNAME', 'jpn.firstName', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<?php if ($this->lists['patronym']) { ?>				
						<th class="nowrap hidden-phone <?php echo $classPat; ?>">
							<?php echo HTMLHelper::_('grid.sort', 'JTPERSONS_HEADING_PATRONYM', 'jpn.patronym', $this->lists['order_Dir'], $this->lists['order']); ?>
						</th>
					<?php } ?>						
					<th class="nowrap hidden-phone"> 
						<?php echo HTMLHelper::_('grid.sort', 'JTPERSONS_HEADING_FAMNAME', 'jpn.familyName', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>					
					<th class="nowrap hidden-phone <?php echo $classPer; ?>"> 
						<?php echo HTMLHelper::_('grid.sort', 'JTPERSONS_HEADING_PERIOD', '13', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th class="nowrap hidden-phone <?php echo $classTree; ?>"> 
						<?php echo Text::_('JTPERSONS_HEADING_DEFTREE'); ?>
					</th>				
					<th class="nowrap hidden-phone">
						<?php echo Text::_('JT_HEADING_PUBLISHED'); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?php echo Text::_('JT_HEADING_LIVING'); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?php echo Text::_('JT_HEADING_PAGE'); ?>
					</th>
					<th class="nowrap hidden-phone <?php echo $classMap; ?>"> 
						<?php echo Text::_('JT_HEADING_MAP'); ?>
					</th>				
					<th class="nowrap hidden-phone <?php echo $classRob; ?>" title="<?php echo Text::_('JFIELD_METADATA_ROBOTS_DESC'); ?>"> 
						<?php echo Text::_('JFIELD_METADATA_ROBOTS_LABEL'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td id="footer" colspan="<?php echo ($this->lists['patronym']) ? 11 : 10; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			
			<tbody>
				<?php foreach ($this->items as $i => $row) {
				    $clickLiving   	= 'return Joomla.listItemTask(\'cb'.$i.'\', \'updateLiving\')';
				    //$clickLiving   	= 'return listItemTask(\'cb'.$i.'\', \'updateLiving\')';
				    if ($row->living) {
				        $living = '<span class="icon-publish" aria-hidden="true" style="color:green;font-size:150%" title="'.Text::_("JT_FILTER_VAL_LIVING").'"></span>';
				        // $living = HTMLHelper::_('image', 'admin/' . 'tick.png', Text::_('JT_FILTER_VAL_LIVING'), null, true);
				    } else {
				        $living = '<span class="icon-unpublish" aria-hidden="true" style="color:red;font-size:150%" title="'.Text::_("JT_FILTER_VAL_NOTLIVING").'"></span>';
				        //$living = HTMLHelper::_('image', 'admin/' . 'publish_r.png', Text::_('JT_FILTER_VAL_NOTLIVING'), null, true);
				    }

				    $clickPage   = 'return Joomla.listItemTask(\'cb'.$i.'\', \'updatePage\')';
				    if ($row->page) {
				        $page = '<span class="icon-publish" aria-hidden="true" style="color:green;font-size:150%" title="'.Text::_("JT_FILTER_VAL_PAGE").'"></span>';
				        // $page = HTMLHelper::_('image', 'admin/' . 'tick.png', Text::_('JT_FILTER_VAL_PAGE'), null, true);
				    } else {
				        $page = '<span class="icon-unpublish" aria-hidden="true" style="color:red;font-size:150%" title="'.Text::_("JT_FILTER_VAL_NOPAGE").'"></span>';
				        //$page = HTMLHelper::_('image', 'admin/' . 'publish_r.png', Text::_('JT_FILTER_VAL_NOPAGE'), null, true);
				    }

				    $map		 = '<select id="map'.$row->app_id.'!'.$row->id.'" name="map'.$row->app_id.'!'.$row->id.'" class="inputbox" onchange="javascript:jtsaveaccess(\'cb'.$i.'\')">';
				    $map		.= HTMLHelper::_('select.options', $this->map, 'value', 'text', ((int) $row->map + 1));
				    $map		.= '</select>';

				    $robot		 = '<select id="robot'.$row->app_id.'!'.$row->id.'" name="robot'.$row->app_id.'!'.$row->id.'" class="inputbox" onchange="javascript:jtsaveaccess(\'cb'.$i.'\')">';
				    $robot		.= HTMLHelper::_('select.options', $this->robots, 'value', 'text', ((int) $row->robots + 1));
				    $robot		.= '</select>';
				    ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="nowrap center hidden-phone">
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $row->app_id.'!'.$row->id); ?>
						</td>
						<td class="small hidden-phone <?php echo $classApp; ?>">
							<?php echo $this->escape($row->appTitle);?>
						</td>
						<td class="center small hidden-phone">
							<?php echo $row->id; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $this->escape($row->firstName);?>
						</td>
						<?php if ($this->lists['patronym']) { ?>
							<td class="hidden-phone <?php echo $classPat; ?>">
								<?php echo $this->escape($row->patronym);?>
							</td>
						<?php } ?>
						<td class="hidden-phone">
							<?php echo $this->escape($row->familyName);?>
						</td>
						<td class="center small hidden-phone <?php echo $classPer; ?>">
							<?php echo $row->period;?>
						</td>
						<td class="small hidden-phone <?php echo $classTree; ?>">
							<?php echo $this->escape($row->familyTree);?>
						</td>
						<td class="centerHTMLHelper hidden-phone">
							<?php
				        $options = [
				                        'task_prefix' => 'persons.',
				                        'disabled' => false,
				                        'id' => 'state-' . $row->id,
				                    ];
				    echo (new PublishedButton())->render((int) $row->published, $i, $options);
				    //echo HTMLHelper::_('grid.published', $row, $i);?>
						</td>
						<td class="center hidden-phone">
							<a href="javascript:void(0);" onclick="<?php echo $clickLiving; ?>" title="<?php echo Text::_('JT_HEADING_LIVING'); ?>">
								<?php echo $living; ?>
							</a>
						</td>
						<td class="center hidden-phone">
							<a href="javascript:void(0);" onclick="<?php echo $clickPage; ?>" title="<?php echo Text::_('JT_HEADING_PAGE'); ?>">
								<?php echo $page; ?>
							</a>
						</td>
						<td class="hidden-phone <?php echo $classMap; ?>">
							<?php echo $map;?>
						</td>
						<td class="hidden-phone <?php echo $classRob; ?>">
							<?php echo $robot;?>
						</td>

					</tr>
				<?php } ?>
			</tbody>
		</table>
		</div>

	</div>
	
	<input type="hidden" name="option" value="com_joaktree" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="persons" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HtmlHelper::_('form.token'); ?>
	
</form>
