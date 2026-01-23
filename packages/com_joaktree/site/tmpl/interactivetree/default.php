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
use Joomla\CMS\HTML\HTMLHelper;
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
$css .= ".f3-form-cont {background-color: ".$params->get('background', '#e0e0e0')."}";
$css .= ".f3-close-btn{left:inherit;right:10px;color: ".$params->get('color', '#737272')."}";
$css .= ".f3-info-field-value {min-height: 0px; margin-bottom:5px}";
$css .= ".f3-info-field-label {font-size:1em}";
$css .= ".f3-close-btn + div {display:none !important}";
$wa->addInlineStyle($css);

$personId = $this->person->app_id.'!'.$this->person->id;

Factory::getApplication()->getDocument()->addScriptOptions(
    'joaktree_interactive_tree',
    array(  'appid' => $this->person->app_id, 'personid' => $personId,'background' => $params->get('background', '#e0e0e0'),'color' => $params->get('color', '#737272'),
            'link' => $params->get('link'), 'linksize' => (int)$params->get('linksize', 1),
            'ancestors' => (int)$params->get('ancestors', 3),'descendants' => (int)$params->get('descendants', 1),
            'search' => $params->get('search', 'true'),'latest' => $params->get('latest', 'true'),
            'latestsize' => (int)$params->get('latestsize', 5),
            'searchtext' => Text::_('JT_TREE_SEARCH'),'latesttext' => Text::_('JT_TREE_LATEST'),
            'nametext' => Text::_('JT_TREE_NAME'), 'birthtext' => Text::_('JT_TREE_BIRTH'), 'deathtext' => Text::_('JT_TREE_DEATH'),
            'detailtext' => Text::_('JT_TREE_DETAIL')
        )
);
HTMLHelper::_('bootstrap.collapse', '#logprevsbtn');

?>
<div id="jt-content">

<?php if ($this->lists['userAccess']) { ?> 
<!-- user has access to information -->
	<div class="jt-h1">
		<?php echo Text::_('JT_INTERACTIVE_TREE').'&nbsp;'.Text::_('JT_OF') ?>
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
    echo '<div id="FamilyChart" class="f3" style="width:100%;height:90vh;margin:auto;background-color:'.$params->get("background", "#e0e0e0").';color:'.$params->get("color", "#737272").';">
        <div id="page-load-base">
            <div class="loader-ellips infinite-scroll-request">
                <span class="loader-ellips__dot"></span>
                <span class="loader-ellips__dot"></span>
                <span class="loader-ellips__dot"></span>
                <span class="loader-ellips__dot"></span>
            </div>
        </div>
        </div>'
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

<?php echo HtmlHelper::_('form.token'); ?>

