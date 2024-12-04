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
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

$comfield	= 'media/com_joaktree/';
$app = Factory::getApplication();

$com_id = $app->input->getInt('Itemid');
$document = $app->getDocument();

$this->iso_params = JoaktreeHelper::getJTParams();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();

$wa->registerAndUseStyle('joaktree', $comfield.'css/joaktree.css');
$wa->registerAndUseStyle('iso', $comfield.'css/iso/isotope.css');
$wa->registerAndUseStyle('rslider', $comfield.'css/iso/rSlider.min.css');

if ($this->iso_params->get("pagination", "false") == 'infinite') {
    $wa->registerAndUseScript('infinite', $comfield.'js/iso/infinite-scroll.min.js');
} else {
    $wa->registerAndUseScript('imgload', $comfield.'js/iso/imagesloaded.min.js');
}
$wa->registerAndUseScript('isotope', $comfield.'js/iso/isotope.min.js');
$wa->registerAndUseScript('packery', $comfield.'js/iso/packery-mode.min.js');
$wa->registerAndUseScript('rslider', $comfield.'js/iso/rSlider.min.js');

if ((bool) $app->getConfig()->get('debug')) {
    $document->addScript(''.URI::base(true).'/media/com_joaktree/js/iso/init.js');
} else {
    $wa->registerAndUseScript('iso', $comfield.'js/iso/init.min.js');
}
$this->families = array();
$this->families_alias = array();
$this->family_count = array();
$this->list = array();
$this->rangelabel =  "";// Text::_('JT_ISO_BIRTH');
$this->rangedesc =  '';
$this->minrange =  9999;
$this->maxrange =  0;
$this->default_family = '';
$this->default_letter = '';
$this->iso_layout = $this->iso_params->get('iso_layout', "masonry");
$this->displayfilterfamily =  $this->iso_params->get('displayfilterfamily', "false");
$this->displayalpha = "true";// $this->iso_params->get('displayalpha', "false");
$this->familyfiltercount = $this->iso_params->get('familyfiltercount', "true");
$this->displayrange = $this->iso_params->get('displayrange', "false");
$this->rangestep = $this->iso_params->get('rangestep', "false");

$this->limit_items = 0;               // => param

$this->sortFilter = array();
$first = "";
// create family alias / family counters
foreach ($this->personlist as $key => $item) {
    $alias = ApplicationHelper::stringURLSafe((string) $item->familyName);
    $char = strtoupper(substr($item->familyName, 0, 1));
    if (!$first && ctype_alpha($char)) $first = strtoupper($char);
    $this->families[$alias] = $item->familyName;
    $this->families_alias[$alias] = $alias;
    if (!isset($this->family_count[$alias])) {
        $this->family_count[$alias] = 0;
    }
    $this->family_count[$alias]++;
    $this->sortFilter[$alias] = $alias;
    $date = null;
    if ($item->birthDate) {
        $date = $item->birthDate;
    } elseif ($item->deathDate) {
        $date = $item->deathDate;
    }
    if ($date) {
        if ($date < $this->minrange) {
            $this->minrange = $date;
        }
        if ($date > $this->maxrange) {
            $this->maxrange = $date;
        }
    }
}
if (($this->displayrange == "true") && ($this->rangestep == "auto")) {
    $step = ((int)$this->maxrange - (int)$this->minrange) / 5 ;
    if ($step < 1) {
        $step = 1;
    }
    $this->rangestep = $step;
}
if (count($this->personlist) > 300) {
    $this->default_letter = $first;
}
$document->addScriptOptions(
    'com_joaktree_'.$com_id,
    array(  'layout' => $this->iso_layout, 'nbcol' => $this->iso_params->get('iso_nbcol', 2),
            'displayfilterfamily' => $this->displayfilterfamily,'displayalpha' => $this->displayalpha,
            'displayrange' => $this->displayrange,'minrange' => $this->minrange,'maxrange' => $this->maxrange,
            'rangestep' => $this->rangestep,
            'limit_items' => $this->limit_items,
            'default_family' => $this->default_family,'default_letter' => $this->default_letter )
);

echo $this->loadTemplate('jt');
