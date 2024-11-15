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
use Joomla\CMS\Router\Route;		//replace JRoute
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml

if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->

	<div id="jt-content">
		<table>
			<!-- header -->
			<thead>
				
				<tr>
					<th class="jt-content-th" width="5" align="center">
						<?php echo Text::_( 'JT_NUM' ); ?>
					</th>
					<th class="jt-content-th">
						<div class="jt-h3-list">
							<?php echo Text::_( 'JT_LABEL_NAME' ); ?>
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
			</thead>
			<!-- table body -->
			<tbody>
			<?php
			$k = 2;
			for ($i=0, $n=count( $this->personlist ); $i < $n; $i++)	{
				$row = $this->personlist[$i];
				$link = Route::_('index.php?option=com_joaktree&view=joaktree'
						 .'&tech='.$this->lists['technology']
						 .'&Itemid='.$this->lists['menuItemId']
						 .'&treeId='.$this->lists['tree_id']
						 .'&personId='.$row->app_id.'!'.$row->id
						 );
				$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';
				
				$rowclass = 'jt-table-entry' . $k;
			?>
				<tr class="<?php echo $rowclass; ?>" >
					<td align="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
					<td>
						<a href="<?php echo $link; ?>" target="_top" <?php echo $robot; ?>>
							<?php echo $row->firstName.' '.$row->familyName; ?>
						</a>
					</td>
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

<?php echo HTMLHelper::_( 'form.token' ); ?>

