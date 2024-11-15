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
use Joomla\CMS\HTML\HTMLHelper;		// replace JHtml
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;		//replace JRoute
?>

<form action="<?php echo Route::_($this->lists['link']); ?>" method="post" id="adminForm" name="adminForm">
<?php echo HTMLHelper::_( 'form.token' ); ?>

<div id="jt-content">
<?php if (count($this->items) > 0) { ?> 
<!-- user has access to information -->
	<div class="jt-h1">
		<?php echo Text::_('JT_CHANGEHISTORY').(($this->name) ? '&nbsp;'.Text::_('JT_OF').'&nbsp;'.$this->name: null); ?>
	</div>
	
	<div class="jt-h3">
		<label class="jt-loglabel-datetime"    ><?php echo Text::_('JT_LABEL_DATETIME'); ?></label>
		<label class="jt-loglabel-application" ><?php echo Text::_('JT_LABEL_GEDCOM'); ?></label>
		<label class="jt-loglabel-description" ><?php echo Text::_('JT_LABEL_CHANGE'); ?></label>
		<label class="jt-loglabel-changeobject"><?php echo Text::_('JT_LABEL_CHANGEOBJECT'); ?></label>
		<label class="jt-loglabel-user"        ><?php echo Text::_('JT_LABEL_USER'); ?></label>
	</div>
	<div class="jt-clearfix"></div>

	<!-- For later filtering -->
	<!-- 
	<div>
		<label class="jt-loglabel-datetime"    >&nbsp;</label>
		<label class="jt-loglabel-application" >&nbsp;</label>
		<label class="jt-loglabel-description" >&nbsp;</label>
		<label class="jt-loglabel-changeobject">&nbsp;</label>
		<label class="jt-loglabel-user"        >&nbsp;</label>
	</div>
	<div class="jt-clearfix"></div>
	 -->
	
	<hr width="100%" size="2" />
		
	<?php 
	foreach ($this->items as $item) {
	?>
		<div class="jt-logsize">
			<label class="jt-loglabel-datetime"    ><?php echo $item->changeDateTime; ?></label>
			<label class="jt-loglabel-application" ><?php echo $item->appname; ?></label>
			<label class="jt-loglabel-description" ><?php echo Text::_($item->logevent); ?></label>
			<label class="jt-loglabel-changeobject">
				<?php echo (!empty($item->description)) ? $item->description : $item->deletedItem; ?>
			</label>
			<label class="jt-loglabel-user"        ><?php echo $item->username; ?></label>
		</div>
		<div class="jt-clearfix"></div>
	<?php 		
	}	
	?>
	<?php echo $this->pagination->getListFooter(); ?>
			
<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_( 'JT_NOACCESS' ); ?></div>
	</div>
<?php } ?>

<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="changehistory" />

</div><!-- jt-content -->
</form>
