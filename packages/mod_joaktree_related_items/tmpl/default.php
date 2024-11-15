<?php
/**
 * Joomla! module Joaktree related items
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Joomla! 5.x conversion by Conseilgouz
 */
 // no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joaktree\Module\Relateditems\Site\Helper\RelateditemsHelper;
 
$arlist = RelateditemsHelper::getArticleList();
$jtlist = RelateditemsHelper::getJoaktreeList();

if ((count($arlist) + count($jtlist))== 0 ) {
	return;
}

$showDate = $params->get('showDate', 0);

if(count($arlist) > 0) { ?><h4><?php echo Text::_('JTRELITEMS_ARTICLES'); ?></h4><?php } ?>
<ul>
<?php foreach ($arlist as $item) :	?>
<li>
	<a href="<?php echo $item->route; ?>" <?php echo $item->robot; ?>>
		<?php if ($showDate) echo $item->created . " - "; ?>
		<?php echo $item->title; ?></a>
</li>
<?php endforeach; ?>
</ul>

<?php if(count($jtlist) > 0) { ?><h4><?php echo Text::_('JTRELITEMS_PERSONS'); ?></h4><?php } ?>
<ul>
<?php foreach ($jtlist as $item) :	?>
<li>
	<a href="<?php echo $item->route; ?>" <?php echo $item->robot; ?>>
		<?php if ($showDate) echo $item->created . " - "; ?>
		<?php echo $item->title; ?></a>
</li>
<?php endforeach; ?>
</ul>