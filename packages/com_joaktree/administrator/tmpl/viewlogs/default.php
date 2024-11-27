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
$input = Factory::getApplication()->input;
$id = $input->getInt('appId', 0);
$filename = Factory::getApplication()->getConfig()->get('log_path').'/joaktreeged.log.php';
$log = [];
if (file_exists($filename)) {
    $file = fopen($filename, "r");
    fseek($file, 0);

    $bStart = false;
    while (!feof($file)) {
        $line = fgets($file);
        if ((strlen(trim($line)) > 0) && (substr(trim($line), 0, 1) != "#")) {
            if (strpos($line, 'Delete Data :')) { // Data deleted fir this source ?
                $linespl = explode(':', $line);
                $str = trim($linespl[count($linespl) - 1]);
                if ((int)$str == $id) {
                    $bStart = true;
                    $log = [];
                }
            }
            if (strpos($line, 'Start : '.$id)) {
                $bStart = true;
                $log = [];
                $log[] = $line;
                continue;
            }
            if ($bStart) {
                $log[] = $line;
            }
            if (strpos($line, 'End :')   // end of current application
            ||  strpos($line, 'Start : ')) { // other application : exit
                $bStart = false;
            }
        }
    }
    fclose($file);
}
?>
<form action="<?php echo Route::_('index.php?option=com_joaktree&view=viewlogs'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="j-log-container" class="span6">
       <h2><?php echo Text::_('COM_JOAKTREE_LOGFILE');?></h2>
	   <div class="cls_log">	
			<?php
                if (count($log) > 0) {
                    echo "<p style='font-size:15px'>".Text::_('COM_JOAKTREE_VIEWLOG_DESC')."</p>";
                    echo "<div>";

                    for ($i = 0;$i < count($log);$i++) {
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

		
