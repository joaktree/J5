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
 * use https://github.com/donatso/family-chart/tree/master
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

$app = Factory::getApplication();
$wa = $app->getDocument()->getWebAssetManager();
$comfield	= 'media/com_joaktree/';
$wa->registerAndUseStyle('joaktree', $comfield.'css/joaktree.css');
$wa->registerAndUseScript('d3', 'https://unpkg.com/d3@7');
$wa->registerAndUseStyle('family', 'https://unpkg.com/family-chart@latest/dist/styles/family-chart.css');
$wa->registerAndUSeScript('family', 'https://unpkg.com/family-chart@latest', ['type' => 'module']);
$wa->registerAndUSeScript('descendantgraph', $comfield.'js/joaktree_interactive_tree.js');

$params = JoaktreeHelper::getJTParams();

$css = ".f3 .link {stroke:".$params->get('link', 'white')."; stroke-width:".(int)$params->get('linksize', 1)."}";
$wa->addInlineStyle($css);

Factory::getApplication()->getDocument()->addScriptOptions(
    'joaktree_interactive_tree',
    array(  'background' => $params->get('background', '#e0e0e0'),'color' => $params->get('color', '#737272'),
            'link' => $params->get('link'), 'linksize' => (int)$params->get('linksize', 1),
            'ancestors' => (int)$params->get('ancestors', 3),'descendants' => (int)$params->get('descendants', 1),
            'search' => $params->get('search', 'true'),'latest' => $params->get('latest', 'true'),
            'latestsize' => (int)$params->get('latestsize', 5),
            'searchtext' => Text::_('JT_TREE_SEARCH'),'latesttext' => Text::_('JT_TREE_LATEST')
        )
);
?>
<div id="jt-content">

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<div class="jt-h1">
		<?php echo Text::_('JT_DESCENDANTS').'&nbsp;'.Text::_('JT_OF') ?>
		<?php
            $link = Route::_(
                'index.php?option=com_joaktree&view=joaktree'
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
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>

<div class="jt-clearfix jt-update">
	<?php echo $this->lists[ 'lastUpdate' ]; ?>
</div>
<div class="jt-stamp">
	<?php echo $this->lists[ 'CR' ]; ?>
</div>

</div><!-- jt-content -->
