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

?>
<form action="<?php echo Route::_( 'index.php?option=com_joaktree&view=list&treeId='.$this->lists['tree_id'] ); ?>" method="post" id="adminForm" name="adminForm">
<?php echo HTMLHelper::_( 'form.token' ); ?>

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<?php $colspanValue = ($this->lists['patronym']!= 0)?7:6; ?>
	<div id="jt-content">
	<fieldset class="joaktreeform">
		<legend>
			<?php echo Text::_('JT_CHECKEDNAME').':&nbsp;'; ?>
				<?php 
					echo $this->lists['search1'].'&nbsp;'
						.(($this->lists['patronym'] != 0) ? $this->lists['search2'].'&nbsp;' : null)
						.$this->lists['search3']; 
				?>
		</legend>
		
		<?php if (count($this->personlist) > 0) { ?>
		<!--  there are records in the list -->
			<div class="jt-high-row" style="margin-left: 10px;">
				<?php echo Text::_('JT_MATCHES'); ?>
			</div>
			<div class="jt-clearfix"></div>
			
			<?php $function = (($this->lists['action'] == 'save') || ($this->lists['action'] == 'saveparent1')) ? 'jtSavePerson()' : 'jtNewPerson()' ; ?>
			<div class="jt-buttonbar" style="margin-left: 10px;">
				<a 	href="#"
					id="new"
					class="jt-button-closed jt-buttonlabel"
					title="<?php echo Text::_('JNEW'); ?>" 
					onclick="if (window.parent) {window.parent.<?php echo $function; ?>; }"
				>
					<?php echo Text::_('JNEW'); ?>
				</a>&nbsp;
			</div>
			<div class="jt-clearfix"></div>
		
			<table style="width: 96%;">
				<!-- header -->
				<thead>				
					<tr>
						<th class="jt-content-th" width="5" rowspan="2" align="center">
							<?php echo Text::_( 'JT_NUM' ); ?>
						</th>
						<th class="jt-content-th">
							<div class="jt-h3-list">
								<?php echo Text::_( 'JT_FIRSTNAME' ); ?>
							</div>
						</th>				
						<?php if ($this->lists['patronym'] != 0) { ?>
							<th class="jt-content-th">
								<div class="jt-h3-list">
									<?php echo Text::_( 'JT_PATRONYM' ); ?>
								</div>
							</th>
						<?php } ?>
						<th class="jt-content-th">
							<div class="jt-h3-list">
								<?php echo Text::_( 'JT_FAMILYNAME' ); ?>
							</div>
						</th>				
						<th class="jt-content-th" align="center">
							<div class="jt-h3-list">
								<?php echo Text::_( 'JT_PERIOD' ); ?>
							</div>
						</th>
						<?php if ($this->lists['action'] == 'saveparent1') { ?>
							<th class="jt-content-th" align="center">
								<div class="jt-h3-list">
									<?php echo Text::_( 'JT_PARTNER' ); ?>
								</div>
							</th>
						<?php } ?>
						<th class="jt-content-th" align="center">
							<div class="jt-h3-list">
								<?php echo Text::_( 'JT_ACTIONS' ); ?>
							</div>
						</th>
					</tr>
				</thead>
				<!-- footer -->
				<tfoot>
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
					$row 				= $this->personlist[$i];					
					$numberOfPartners 	= ($this->lists['action'] == 'saveparent1') ? count($row->partners) : 0;
					$numberOfRows     	= ($numberOfPartners) ? $numberOfPartners : 1;
					$rowclass 			= 'jt-table-entry' . $k;
				?>
					<tr class="<?php echo $rowclass; ?>" >
						<td align="center" rowspan="<?php echo $numberOfRows; ?>">
							<?php echo $this->pagination->getRowOffset( $i ); ?>
						</td>
						<td rowspan="<?php echo $numberOfRows; ?>"><?php echo $row->firstName; ?></td>
						<?php if ($this->lists['patronym'] != 0) { ?>
							<td rowspan="<?php echo $numberOfRows; ?>"><?php echo $row->patronym; ?></td>
						<?php } ?>
						<td rowspan="<?php echo $numberOfRows; ?>"><?php echo $row->familyName; ?></td>
						<td rowspan="<?php echo $numberOfRows; ?>"><?php echo $row->birthDate; ?>&nbsp;-&nbsp;<?php echo $row->deathDate; ?></td>
						
						<!-- Show names of parent's partners when adding first parent -->
						<?php if ($this->lists['action'] == 'saveparent1') { ?>
							<td><?php echo (isset($row->partners[0]['fullName'])) ? $row->partners[0]['fullName'] : null ;?></td>
						<?php } ?>
						
						<?php
						if ($this->lists['action'] == 'saveparent1') { 
							$function =  'window.parent.jtSelectPerson(\''.$row->app_id.'\', \''.$row->id.'\', \''.$row->partners[0]['relation_id'].'\', \''.$row->partners[0]['family_id'].'\')';
						} else {
							$function =  'window.parent.jtSelectPerson(\''.$row->app_id.'\', \''.$row->id.'\')';
						} 
						?>
						<td><span class="jt-edit">
								<a 	href="#"
									onclick="if (window.parent) <?php echo $function; ?>;"
									title="<?php echo Text::_('JTSELECT_DESC'); ?>" 
								>
									<?php echo Text::_('JTSELECT'); ?>
								</a>
							</span>	
						</td>
					</tr>
					<?php 
					for ($j=1, $m=$numberOfRows; $j<$m; $j++) {
					?>
						<tr class="<?php echo $rowclass; ?>" >
							<td><?php echo (isset($row->partners[$j]['fullName'])) ? $row->partners[$j]['fullName'] : null ;?></td>
							<?php 	
							if ($this->lists['action'] == 'saveparent1') { 
								$function =  'window.parent.jtSelectPerson(\''.$row->app_id.'\', \''.$row->id.'\', \''.$row->partners[$j]['relation_id'].'\', \''.$row->partners[$j]['family_id'].'\')';
							} else {
								$function =  'window.parent.jtSelectPerson(\''.$row->app_id.'\', \''.$row->id.'\')';
							} 
							?>
							<td><span class="jt-edit">
									<a 	href="#"
										onclick="if (window.parent) <?php echo $function; ?>;"
										title="<?php echo Text::_('JTSELECT_DESC'); ?>" 
									>
										<?php echo Text::_('JTSELECT'); ?>
									</a>
								</span>	
							</td>
						</tr>
					<?php 
					}
					$k = 3 - $k;
				}
				?>
				</tbody>
			</table>
		<?php } else { ?>
		<!--  there are records in the list -->
			<div class="jt-content-th" >
				<div class="jt-noaccess"><?php echo Text::_( 'JT_NOMATCHES' ); ?></div>
			</div>
		<?php } ?>
	</fieldset>
	</div> <!-- jt-content -->
	
<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
	</div>
<?php } ?>

<div class="jt-update">
	<?php echo $this->lists[ 'lastUpdate' ]; ?>
</div>
<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="task" value="" />

</form>