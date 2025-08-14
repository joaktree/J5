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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

HTMLHelper::_('bootstrap.framework');
echo HTMLHelper::_('form.token');

$config     = ComponentHelper::getParams('com_joaktree') ;
$defpath    = $params->get('gedcomfile_path', 'files/com_joaktree/gedfiles');

if (count($this->items) > 1){
?>    
		<div id="head_error" style="display: block;">
			<div style="float: left">
                <h1><?php echo Text::_('JTPROCGEDCOM_ONE_ONLY'); ?></h1>
			</div>
			<div style="float: right">
				<div class="jt-icon">
					<a href="index.php?option=com_joaktree&view=applications">
						<img src="../media/com_joaktree/images/icon-48-app.png" />
						<br />
						<span><?php echo Text::_('JT_SUBMENU_APPLICATIONS'); ?></span>
					</a>
				</div>
			</div>
		</div>
 <?php  
    return false;
}
?>



<div id="j-main-container">
	<div id="cpanel" >
		<div id="head_process" style="display: inline;">
			<div style="float: left; height: 114px;">
				<h1><?php echo Text::_('JTPROCGEDCOM_PROC'); ?></h1>
				<?php echo Text::_('JTPROCGEDCOM_PROC_TXT'); ?>
			</div>
			<div style="float: right">
				<div class="jt-icon">
					<img src="../media/com_joaktree/images/ajax-loader.gif" />
					<br />
					<span><?php echo Text::_('JT_LOADING'); ?></span>
				</div>
			</div>
		</div>
		<div id="head_finished" style="display: none;">
			<div style="float: left">
				<h1><?php echo Text::_('JTPROCGEDCOM_EXPFINISHED'); ?></h1>
			</div>
			<div style="float: right">
				<div class="jt-icon">
					<a href="index.php?option=com_joaktree&view=applications">
						<img src="../media/com_joaktree/images/icon-48-app.png" />
						<br />
						<span><?php echo Text::_('JT_SUBMENU_APPLICATIONS'); ?></span>
					</a>
				</div>
				<div class="jt-icon">
					<a href="index.php?option=com_joaktree&view=trees">
						<img src="../media/com_joaktree/images/icon-48-familytree.png" />
						<br />
						<span><?php echo Text::_('JT_SUBMENU_FAMILYTREES'); ?></span>
					</a>
				</div>
				<div class="jt-icon">
					<a href="index.php?option=com_joaktree&view=persons">
						<img src="../media/com_joaktree/images/icon-48-person.png" />
						<br />
						<span><?php echo Text::_('JT_SUBMENU_PERSONS'); ?></span>
					</a>
				</div>
			</div>
		</div>
		<div id="head_error" style="display: none;">
			<div style="float: left">
				<h1><?php echo Text::_('JTPROCGEDCOM_HERROR'); ?></h1>
			</div>
			<div style="float: right">
				<div class="jt-icon">
					<a href="index.php?option=com_joaktree&view=applications">
						<img src="../media/com_joaktree/images/icon-48-app.png" />
						<br />
						<span><?php echo Text::_('JT_SUBMENU_APPLICATIONS'); ?></span>
					</a>
				</div>
			</div>
		</div>

	</div>
</div>
<div style="clear: both;"></div>

<div style="float: right; width: 50%;">
	<fieldset>
		<legend><?php echo Text::_('JTPROCESS_MSG'); ?></legend>
		<div id="procmsg"></div>
		
	</fieldset>
</div>

<?php foreach ($this->items as $item) {

    $params		= JoaktreeHelper::getJTParams($item->id);
    $path  		= '../'.$params->get('gedcomfile_path',$defpath);
    $file 		= 'export_'.$params->get('gedcomfile_name');
    $fullname	= $path.'/'.$file;

    ?>

<div class="form-horizontal" style="width: 40%;">
	<fieldset>
		<legend><?php echo $item->title; ?></legend>

		<div class="tab-content">
			<div class="tab-pane active">
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('JT_HEADING_ID'); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="id_<?php echo $item->id; ?>" 
							class="readonly"
							value="<?php echo $item->id; ?>"
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('JTPROCESS_START'); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="start_<?php echo $item->id; ?>" 
							class="readonly"
							value=""
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('JTPROCESS_CURRENT'); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="current_<?php echo $item->id; ?>" 
							class="readonly"
							value=""
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group" id="l_persons_<?php echo $item->id; ?>" style="display: none;">
					<div class="control-label">
						<?php echo Text::sprintf('JTGEDCOM_MESSAGE_PERSONS', null); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="persons_<?php echo $item->id; ?>" 
							class="readonly"
							value="0"
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group" id="l_families_<?php echo $item->id; ?>"style="display: none;">
					<div class="control-label">
						<?php echo Text::sprintf('JTGEDCOM_MESSAGE_FAMILIES', null); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="families_<?php echo $item->id; ?>" 
							class="readonly"
							value="0"
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group" id="l_sources_<?php echo $item->id; ?>"style="display: none;">
					<div class="control-label">
						<?php echo Text::sprintf('JTGEDCOM_MESSAGE_SOURCES', null); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="sources_<?php echo $item->id; ?>" 
							class="readonly"
							value="0"
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group" id="l_repos_<?php echo $item->id; ?>"style="display: none;">
					<div class="control-label">
						<?php echo Text::sprintf('JTGEDCOM_MESSAGE_REPOS', null); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="repos_<?php echo $item->id; ?>" 
							class="readonly"
							value="0"
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group" id="l_notes_<?php echo $item->id; ?>"style="display: none;">
					<div class="control-label">
						<?php echo Text::sprintf('JTGEDCOM_MESSAGE_NOTES', null); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="notes_<?php echo $item->id; ?>" 
							class="readonly"
							value="0"
							readonly="readonly"
						/>
					</div>
				</div>
				<div class="control-group" id="l_unknown_<?php echo $item->id; ?>"style="display: none;">
					<div class="control-label">
						<?php echo Text::sprintf('JTGEDCOM_MESSAGE_UNKNOWN', null); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="unknown_<?php echo $item->id; ?>" 
							class="readonly"
							value="0"
							readonly="readonly"
						/>} catch (err) {
  passiveSupported = false;
}
					</div>
				</div>
				<div class="control-group" style="display: none;">
					<div class="control-label">
						<?php echo Text::_('JTPROCESS_END'); ?>
					</div>
					<div class="controls">
						<input 
							type="text" 
							id="end_<?php echo $item->id; ?>" 
							class="readonly"
							value=""
							readonly="readonly"
						/>
					</div>
				</div>

			</div>
		</div>
		<div class="clr"> </div>
	</fieldset>	
</div>
<div >
	<a href="<?php echo $fullname;?>" class="btn btn-primary" id="btngetexport" download="<?php echo $file;?>" style="display:none;width:40%;float:left">
		<?php echo Text::_('JTFIELD_DWNLOADEXPORT_BUTTON'); ?>
	</a>
	<a href="#" class="btn btn-primary" id="btndelexport" style="display:none;float:right;width:40%">
        <?php echo Text::_('JTFIELD_DELEXPORT_BUTTON'); ?>
	</a>
</div>
<script>
try{
	//window.addEventListener('domready', function() {
	window.addEventListener('DOMContentLoaded', function() {

		btn = document.getElementById("btndelexport");
		btn.addEventListener('click', function() {
			delExportFile(<?php echo $item->id; ?>)
		});

		//console.log('export gedcom');
		exportGedcom();

	});
} catch (err) {
  	console.log('erreur');
}
</script>
<?php } ?>


