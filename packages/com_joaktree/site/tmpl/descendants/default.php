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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>

<div id="jt-content">

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<div class="jt-h1">
		<?php echo Text::_('JT_DESCENDANTS').'&nbsp;'.Text::_('JT_OF') ?>
		<?php 
			$link = Route::_('index.php?option=com_joaktree&view=joaktree'
							 .'&tech='.$this->lists['technology']
							 .'&Itemid='.$this->person->menuItemId
							 .'&treeId='.$this->lists['treeId']
							 .'&personId='.$this->person->app_id.'!'.$this->person->id
							 );
			$robot = ($this->lists['technology'] == 'a') ? '' : 'rel="noindex, nofollow"';
		?>
		<a href="<?php echo $link; ?>" <?php echo $robot; ?>><?php echo $this->person->fullName; ?></a>
	</div>
	<hr width="100%" size="2" />
		
	<?php 
		$layout = $this->setLayout('');
		$this->display('generation');
		$this->setLayout($layout);
	?>
			
<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
	</div>
<?php } ?>

<div class="jt-clearfix jt-update">
	<?php echo $this->lists[ 'lastUpdate' ]; ?>
</div>
<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

</div><!-- jt-content -->
