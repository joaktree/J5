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
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
$input = Factory::getApplication()->getInput();
$id = $input->getInt('appId', 0);
$filename = Factory::getApplication()->getConfig()->get('log_path').'/joaktree.log.php';
$log = [];
if (file_exists($filename)) {
    $file = fopen($filename, "r");
    fseek($file, 0);

    $ix = 0;
    while(!feof($file)) {
        $line = fgets($file);
        if ((strlen(trim($line)) > 0) && (substr(trim($line), 0, 1) != "#")) {
            $ix += 1;
            $log[$ix] = $line;
        }
    }
    fclose($file);
}
?>
<form action="<?php echo Route::_('index.php?option=com_joaktree&view=upload'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php if(!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div class="span6">
		<div class="clearfix"> </div>
            <div class="nr-main-header">
                <h2><?php echo Text::_('COM_JOAKTREE_IMPORT'); ?></h2>
            </div>

		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label class="m-3"><?php echo Text::_('COM_JOAKTREE_IMPORTFILE');?></label>
				<input class="m-3" type="file" name="fileupload" />
				<input class="m-3" type="submit" value="<?php echo Text::_('COM_JOAKTREE_UPLOAD');?>" name="upload">
				<input type="hidden" name="task" value="upload.upload" />
                <input type="hidden" name="appid" value="<?php echo $id; ?>" />
			</div>
		</div>        
	</div>
	<div id="j-log-container" class="span6">
       <h2><?php echo Text::_('COM_JOAKTREE_LOGFILE');?></h2>
	   <div class="cls_log">	
			<?php
                if (count($log) > 0) {
                    echo "<p style='font-size:15px'>".Text::_('COM_JOAKTREE_LOGFILE_DESC')."</p>";
                    echo "<div style='overflow-y:scroll;height:20em'>";
                    $max = count($log) - 20;
                    if ($max < 0) {
                        $max = 0;
                    }
                    for ($i = count($log);$i > $max;$i--) {
                        $str = str_replace("+00:00", "", $log[$i]);
                        $str = str_replace("INFO", "", $str);
                        if (strlen($str) > 0) {
                            echo "<br/>";
                        }
                        echo $str;
                    }
                    echo "</div>";
                } else {
                    echo "<p style='font-size:15px'>Pas de fichier log disponible</p>";
                }
?>
	   </div>
	</div>
    <div class="clearfix"> </div>
        <?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>        

		
