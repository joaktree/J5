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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

?>

<form action="<?php echo Route::_('index.php?option=com_joaktree&view=list&treeId='.$this->lists['tree_id']); ?>" method="post" id="adminForm" name="adminForm">
<?php echo HTMLHelper::_('form.token'); ?>

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->

	<div id="jt-content">
		<table>
			<!-- header -->
			<thead>
				<?php if (!empty($this->lists['search4'])) { ?>
				<tr>
					<th class="jt-content-th" colspan="<?php echo ($this->lists['patronym'] != 0) ? 6 : 5; ?>">
						<div class="jt-h3-list">
							<span class="jt-label"><?php echo Text::_('JT_LOCATION').':'; ?></span>
							<?php echo stripslashes($this->lists['search4']); ?>&nbsp;&nbsp;&nbsp;
							<input type="hidden" name="search4" id="search4" value="<?php echo base64_encode($this->lists['search4']);?>" />
							<input type="submit" onclick="document.getElementById('search4').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_('JT_RESET'); ?>" title="<?php echo Text::_('JT_TO_RESET'); ?>" />                               
						</div>
					</th>
				</tr>
				<?php } ?>
				
				<tr>
					<th class="jt-content-th" width="5%" rowspan="2" align="center">
						<?php echo Text::_('JT_NUM'); ?>
					</th>
					<th class="jt-content-th">
						<div class="jt-h3-list jt-content-tha">
							<?php echo HTMLHelper::_('grid.sort', Text::_('JT_FIRSTNAME'), 'jpn.firstName', $this->lists['order_Dir'], $this->lists['order']); ?>
						</div>
					</th>
					<?php if ($this->lists['patronym'] != 0) { ?>
					  <th class="jt-content-th">
						<div class="jt-h3-list jt-content-tha">
							<?php echo HTMLHelper::_('grid.sort', Text::_('JT_PATRONYM'), 'jpn.patronym', $this->lists['order_Dir'], $this->lists['order']); ?>
						</div>
					  </th>
					<?php } ?>
					<th class="jt-content-th">
						<div class="jt-h3-list jt-content-tha">
							<?php echo HTMLHelper::_('grid.sort', Text::_('JT_FAMILYNAME'), 'jpn.familyName', $this->lists['order_Dir'], $this->lists['order']); ?>
						</div>
					</th>
					<th class="jt-content-th" align="center">
						<div class="jt-h3-list">
							<?php echo Text::_('BIRT'); ?>
						</div>
					</th>
					<th class="jt-content-th" align="center">
						<div class="jt-h3-list">
							<?php echo Text::_('DEAT'); ?>
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
						<!--<input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_('JT_SEARCH'); ?>" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" /> -->
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" />
						<!--<input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_('JT_RESET'); ?>" title="<?php echo Text::_('JT_TO_RESET'); ?>" />  -->
                        <input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_('JT_TO_RESET'); ?>" />                   
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
						<!--<input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_('JT_SEARCH'); ?>" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" />
						<input type="submit" onclick="document.getElementById('search2').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_('JT_RESET'); ?>" title="<?php echo Text::_('JT_TO_RESET'); ?>" />  -->
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" />
						<input type="submit" onclick="document.getElementById('search2').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_('JT_TO_RESET'); ?>" />                              
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
                        <input type="submit" onclick="this.form.submit();" name="Go" class="button_search" value="" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" />
						<input type="submit" onclick="document.getElementById('search3').value='';this.form.submit();" name="Reset" class="button_erase" value="" title="<?php echo Text::_('JT_TO_RESET'); ?>" />  
                       <!-- <input type="submit" onclick="this.form.submit();" name="Go" class="button" value="<?php echo Text::_('JT_SEARCH'); ?>" title="<?php echo Text::_('JT_TO_SEARCH'); ?>" />
						<input type="submit" onclick="document.getElementById('search1').value='';this.form.submit();" name="Reset" class="button" value="<?php echo Text::_('JT_RESET'); ?>" title="<?php echo Text::_('JT_TO_RESET'); ?>" />    -->
						
					</th>
					<th class="jt-content-th" align="center">&nbsp;
						
					</th>
					<th class="jt-content-th" align="center">&nbsp;
						
					</th>
				</tr>
			</thead>
			<!-- footer -->
			<?php if ($this->pagination->pagesTotal > 1) { ?>
				<tfoot>
					<?php
                        if ($this->lists['patronym'] != 0) {
                            $colspanValue = 6;
                        } else {
                            $colspanValue = 5;
                        }
			    if ($this->lists['tree_id']) { // tree required in request : add it in pagination
			        $this->pagination->setAdditionalUrlParam('treeId', $this->lists['tree_id']);
			    }
			    ?>
					<tr align="center">
						<th colspan="<?php echo $colspanValue; ?>" >
							<?php echo $this->pagination->getListFooter(); ?>
						</th>
					</tr>
				</tfoot>
			<?php } ?>
			<!-- table body -->
			<tbody>
			<?php
			$k = 2;
    for ($i = 0, $n = count($this->personlist); $i < $n; $i++) {
        $row = $this->personlist[$i];
        $link = Route::_(
            'index.php?option=com_joaktree&view=joaktree'
                 .'&tech='.$this->lists['technology']
                 .'&Itemid='.$this->lists['menuItemId']
                 .'&treeId='.$this->lists['tree_id']
                 .'&personId='.$row->app_id.'!'.$row->id
        );
        $robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';

        $rowclass = 'jt-table-entry' . $k;
        ?>
				<tr class="<?php echo $rowclass; ?>" >
					<td align="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
					<td>
						<a href="<?php echo $link; ?>" <?php echo $robot; ?>><?php echo !empty($row->firstName) ? $row->firstName : '.....'; ?></a>
					</td>
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
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>

<div class="jt-update">
	<?php echo $this->lists[ 'lastUpdate' ]; ?>
</div>
<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="treeId" value="<?php echo $this->lists['tree_id']; ?>" />
<input type="hidden" name="view" value="list" />
<input type="hidden" name="itemId" value="<?php echo $this->lists['menuItemId2']; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />

</form>