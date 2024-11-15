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

if ($this->lists['userAccess']) { ?>
	<table>
	<tbody>
		<?php 
		if (count($this->namelist) > 0) {
			$k = 2;

			for ($i=0, $n=$this->lists['numberRows']; $i < $n; $i++) {
				$rowclass = 'jt-index-entry' . $k;
		?>
				<tr class="<?php echo $rowclass; ?>">
					<td class="jt-small">&nbsp;</td>				
					<?php for ($j=0, $m=$this->lists['columns']; $j < $m; $j++) {
							if (isset($this->namelist	[$i+($j*$this->lists['numberRows'])])) {
								$cell = $this->namelist	[$i+($j*$this->lists['numberRows'])];															
							//if (isset($this->namelist	[$j+($i*$this->lists['numberRows'])])) {
								//$cell = $this->namelist	[$j+($i*$this->lists['numberRows'])];
								if ($cell->familyName != null) {
									$linkName = explode(',', $cell->familyName);
									$link = Route::_($this->lists['link'].'&search3='.array_shift($linkName)); 
					?>
									<td class="jt-small">
									<a href="<?php echo $link; ?>">
									<?php echo $cell->familyName.' ('.$cell->nameCount.')';?>
									</a>
									</td>

						<?php 	} else { ?>
									<td class="jt-small">&nbsp;</td>
						<?php 	} ?>
						<?php } else { ?>
								<td class="jt-small">&nbsp;</td>
						<?php } ?>
					<?php } ?>
				</tr>
				<?php $k = 3 - $k; ?>
		<?php } } ?>
		
		<?php ?>
		<?php if (count($this->namelist) == 0) { ?>
				<tr class="jt-index-entry2">
					<td class="jt-small">&nbsp;</td>
					<td class="jt-small"><?php echo Text::_('JT_NONAMES'); ?></td>
				</tr>
		<?php } ?>
	</tbody>
	</table>

<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
	</div>
<?php } ?>
