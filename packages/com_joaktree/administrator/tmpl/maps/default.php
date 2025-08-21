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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$sortFields = $this->getSortFields();

$staticmapAPIkey  = (isset($this->mapSettings->staticmap)) ? $this->mapSettings->staticmap.'APIkey' : '';
$interactivemapAPIkey = (isset($this->mapSettings->interactivemap)) ? $this->mapSettings->interactivemap.'APIkey' : '';
$geocodeAPIkey    = (isset($this->mapSettings->geocode)) ? $this->mapSettings->geocode.'APIkey' : '';
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
	
		<?php if(!empty( $this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php  $divClassSpan = 'span10'; ?>
<?php } else { ?>
	<?php  $divClassSpan = ''; ?>	
<?php } ?>
	<div id="j-main-container" class="<?php echo $divClassSpan; ?>">

		<!-- ========= Showing map and service settings ========= -->
		<?php if (  (empty($this->mapSettings->staticmap))
				 || ((!empty($this->mapSettings->staticmap)) && isset($this->mapSettings->$staticmapAPIkey) && empty($this->mapSettings->$staticmapAPIkey))
				 || (empty($this->mapSettings->interactivemap))		 
				 || ((!empty($this->mapSettings->interactivemap))&& isset($this->mapSettings->$interactivemapAPIkey) && empty($this->mapSettings->$interactivemapAPIkey))		 
				 || (empty($this->mapSettings->geocode)) 
				 || ((!empty($this->mapSettings->geocode))   && isset($this->mapSettings->$geocodeAPIkey) && empty($this->mapSettings->$geocodeAPIkey))		 
				 || ($this->mapSettings->invalidpc  >  5)
				 ) {
		?>
		<fieldset class="adminform">
			<legend><?php echo Text::_('JTMAP_TITLE_PARAMS');?></legend>
			<table >
				<tr class="row0">
					<th class="pull-left" style="padding-right: 20px;"><?php echo Text::_('MBJ_STATICMAP'); ?></th>
				    <td><?php echo (empty($this->mapSettings->staticmap))
				    							? '<strong style="color: red">'.Text::_('JNO').'</strong>'
				    							: ucfirst($this->mapSettings->staticmap); ?>
					</td>
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('COM_JOAKTREE_API_LABEL'); ?></th>
				    <td><?php echo (isset($this->mapSettings->$staticmapAPIkey) && !empty($this->mapSettings->$staticmapAPIkey)) 
				    				? Text::_('JYES')
				    				: ( (!isset($this->mapSettings->$staticmapAPIkey))
				    				  ? '...'
				    				  : '<strong style="color: red">'.Text::_('JNO').'</strong>'
				    				  );
				    	?>
				    </td>
				    
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('JT_NUM_LOCATIONS'); ?></th>
				    <td><?php echo $this->mapSettings->total; ?></td>
				</tr>
				<tr class="row1">
					<th class="pull-left" style="padding-right: 20px;"><?php echo Text::_('MBJ_INTERACTIVEMAP'); ?></th>
				    <td><?php echo (empty($this->mapSettings->interactivemap))
				    							? '<strong style="color: red">'.Text::_('JNO').'</strong>'
				    							: ucfirst($this->mapSettings->interactivemap); ?>
					</td>
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('COM_JOAKTREE_API_LABEL'); ?></th>
				    <td><?php echo (isset($this->mapSettings->$interactivemapAPIkey) && !empty($this->mapSettings->$interactivemapAPIkey)) 
				    				? Text::_('JYES')
				    				: ( (!isset($this->mapSettings->$interactivemapAPIkey))
				    				  ? '...'
				    				  : '<strong style="color: red">'.Text::_('JNO').'</strong>'
				    				  );
				    	?>
				    </td>
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('JT_NUM_INVALIDLOCATIONS'); ?></th>
				    
				    <td><?php if ($this->mapSettings->invalidpc > 20) { ?>
				    		<strong style="color: red"><?php } ?>	    
				    		<?php echo $this->mapSettings->invalid.'&nbsp;('.$this->mapSettings->invalidpc.'%)'; ?>
				    	<?php if ($this->mapSettings->invalidpc > 20) { ?>
				    		</strong><?php } ?>
				    </td>
				</tr>
				<tr class="row0">
					<th class="pull-left" style="padding-right: 20px;"><?php echo Text::_('MBJ_GEOCODE'); ?></th>
				    <td><?php echo (empty($this->mapSettings->geocode))
				    							? '<strong style="color: red">'.Text::_('JNO').'</strong>'
				    							: ucfirst($this->mapSettings->geocode); ?>
					</td>
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('COM_JOAKTREE_API_LABEL'); ?></th>
				    <td><?php echo (isset($this->mapSettings->$geocodeAPIkey) && !empty($this->mapSettings->$geocodeAPIkey)) 
				    				? Text::_('JYES')
				    				: ( (!isset($this->mapSettings->$geocodeAPIkey))
				    				  ? '...'
				    				  : '<strong style="color: red">'.Text::_('JNO').'</strong>'
				    				  );
				    	?>
				    </td>
				    
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('MBJ_LABEL_LOADSIZE'); ?></th>
				    <td><?php echo (isset($this->mapSettings->maxloadsize)) ? $this->mapSettings->maxloadsize : ''; ?></td>
				</tr>
				<tr class="row1">
					<th class="pull-left" style="padding-right: 20px;"><?php echo Text::_('MBJ_LABEL_COUNTRY'); ?></th>
				    <td><?php echo (isset($this->mapSettings->country)) ? $this->mapSettings->country : ''; ?></td>
				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('MBJ_LABEL_LANGUAGE'); ?></th>
				    <td><?php echo (isset($this->mapSettings->language)) ? $this->mapSettings->language : ''; ?></td>

				    <th class="pull-left" style="padding-right: 20px; padding-left: 35px;"><?php echo Text::_('MBJ_LABEL_INDHTTPS'); ?></th>
				    <td><?php echo (isset($this->mapSettings->indHttps) && $this->mapSettings->indHttps) ? Text::_('JYES') : Text::_('JNO'); ?></td>
				</tr>
			</table>
		</fieldset>
		<div>&nbsp;<br />&nbsp;</div>
		<?php } ?>
		<!-- ========= END Showing map and service settings ========= -->

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
                    onkeypress="checkenter(event);"
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
						<?php echo HtmlHelper::_('grid.sort', 'JTAPPS_LABEL_TITLE', 'jmp.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
					</th>
					<th class="nowrap hidden-phone"> 
						<?php echo Text::_( 'JT_LABEL_TYPE' ); ?>
					</th>				
					<th class="nowrap hidden-phone"> 
						<?php echo Text::_( 'JT_LABEL_SUBJECT' ); ?>
					</th>				
					<th class="nowrap hidden-phone">
						<?php echo HtmlHelper::_('grid.sort', 'JT_LABEL_PERIODSTART', 'jmp.period_start', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>								
					<th class="nowrap hidden-phone">
						<?php echo HtmlHelper::_('grid.sort', 'JT_LABEL_PERIODEND', 'jmp.period_end', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>					
					<th width="2%" class="nowrap center hidden-phone">
						<?php echo Text::_( 'JT_HEADING_ID' ); ?>
					</th>
				
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="8">
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
						<td class="small hidden-phone">
							<?php echo $this->escape($row->selection);?>&nbsp;/&nbsp;<?php echo $this->escape($row->service);?>
						</td>						
						<td class="small hidden-phone">
							<?php echo $this->escape($row->subject);?>
						</td>						
						<td class="small hidden-phone">
							<?php echo $this->escape($row->period_start);?>
						</td>						
						<td class="small hidden-phone">
							<?php echo $this->escape($row->period_end);?>
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
		<input type="hidden" name="controller" value="maps" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>
</form>