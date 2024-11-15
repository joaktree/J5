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

// Button for editing (only active with AJAX)
if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
	if (is_object($this->canDo)) {
?>
		<div class="jt-edit-2" style="text-align: right;">
<?php 
		$docsFromGedcom	= (int) $this->params->get('indDocuments', 0); 
		if (  $this->canDo->get('media.create')
		   || ($this->canDo->get('core.edit') && ($docsFromGedcom))
		   ) {
?>	   	
			<a href="#" onclick="jtsubmitbutton('edit', 'medialist');" >
			<?php echo Text::_('JT_EDITPICTURES'); ?>
			</a>
<?php 	} else { ?>
			<span class="jt-edit-nolink" title="<?php echo Text::_('JT_NOPERMISSION_DESC'); ?>" >
			<?php echo Text::_('JT_EDITPICTURES'); ?>
			</span>
			
<?php 	} ?>
		&nbsp;|			
		</div>
<?php 	   		
	}	
}
?>

<?php
   if(is_array($this->Html) && array_key_exists('pictures', $this->Html)){
     echo $this->Html[ 'pictures' ];
  }
?>


