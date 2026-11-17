<?php
/**
 * Joomla! component Joaktree
 *
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */
namespace Joaktree\Component\Joaktree\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Location input with autocomplete suggestions from #__joaktree_locations.
 *
 * Renders the same input as jttext, linked to a <datalist> with the existing
 * locations. Free text is still allowed, so new locations can be added, but
 * typing suggests the existing spelling and avoids near-duplicates.
 *
 * The datalist is created by JavaScript (once per page) because event rows are
 * also used as JavaScript templates for rows added on the fly.
 */
class JtlocationField extends JttextField
{
    protected $type = 'jtlocation';

    /** @var bool  datalist script already added for this request */
    protected static $datalistAdded = false;

    protected function getInput()
    {
        $this->addDatalist();

        $html = parent::getInput();

        return preg_replace('/^<input /', '<input list="jt-locations" autocomplete="off" ', $html, 1);
    }

    protected function addDatalist()
    {
        if (self::$datalistAdded) {
            return;
        }
        self::$datalistAdded = true;

        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select('DISTINCT ' . $db->quoteName('value'))
                ->from($db->quoteName('#__joaktree_locations'))
                ->where($db->quoteName('value') . ' <> ' . $db->quote(''))
                ->order($db->quoteName('value'));
            $db->setQuery($query);
            $locations = $db->loadColumn() ?: [];
        } catch (\Exception $e) {
            $locations = [];
        }

        if (!$locations) {
            return;
        }

        $doc = Factory::getApplication()->getDocument();
        $doc->addScriptOptions('joaktree.locations', array_values($locations));

        $wa = $doc->getWebAssetManager();
        $wa->useScript('core');
        $wa->addInlineScript(
            "document.addEventListener('DOMContentLoaded', function () {"
            . " if (document.getElementById('jt-locations')) { return; }"
            . " var list = (window.Joomla && Joomla.getOptions('joaktree.locations')) || [];"
            . " var dl = document.createElement('datalist');"
            . " dl.id = 'jt-locations';"
            . " list.forEach(function (v) { var o = document.createElement('option'); o.value = v; dl.appendChild(o); });"
            . " document.body.appendChild(dl);"
            . "});",
            [],
            [],
            ['core']
        );
    }
}
