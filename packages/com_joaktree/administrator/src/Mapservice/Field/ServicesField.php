<?php
/**
 * @package     MapsByJoaktree
 * @subpackage  Service
 *
 * @copyright   Joaktree.com
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joaktree\Component\Joaktree\Administrator\Mapservice\Field;

// no direct access
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\Form\FormField;		//replace JFormField
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJService;
use Joaktree\Component\Joaktree\Administrator\Mapservice\MBJProvider;

class ServicesField extends FormField	//JFormField
{
    protected $type = 'services';
    public function getInput()
    {
        $html    = array();
        $script  = array();
        $APIkey  = array();
        $options = array();
        $extrafields = array();

        //load the language file
        MBJService::setLanguage();

        // Script
        $script[] = '<script type="text/javascript">';
        $script[] = "function MBJServiceKey() {" ;
        $script[] = '  var newKey = ""; ';

        // convert value to keys
        $key = json_decode($this->value);

        $html[] = '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="" />';

        $services = MBJService::getServices();

        $html[] = '<p style="clear: both; font-size: 110%; color: red;">'.Text::_('MBJ_DISCAIMER').'</p>';

        //$html[] = '<ul class="config-option-list">';
        $count = count($services);
        foreach ((array) $services as $service) {
            $count--;
            $script[] = '  newKey = newKey + \'"\' + \''.$service.'\' + \'"\' + \':\' + \'"\' + document.getElementById("MBJ_'.$service.'").value + \'"\'; ';
            $script[] = ($count) ? '  newKey = newKey + \',\';' : '';

            //$html[] = '<li>';
            $html[] = '<div class="control-group">';
            //$html[] = '<label>'.Text::_('MBJ_'.strtoupper($service)).'</label>';
            $html[] = '<div class="control-label"><label>'.Text::_('MBJ_'.strtoupper($service)).'</label></div>';
            $html[] = '<div class="controls">';
            $html[] = '<select id="MBJ_'.$service.'" name="MBJ_'.$service.'" class="inputbox" onchange="MBJServiceKey();">';
            $html[] = '<option value="0" '.(!isset($key->$service) ? 'selected="selected"' : '').'>'.Text::_('JNO').'</option>';

            $providers = MBJService::getConnectors($service);
            foreach ((array) $providers as $provider) {
                $selected = ((isset($key->$service) && ($key->$service == $provider)) ? 'selected="selected"' : '');
                $html[] = '<option value="'.$provider.'" '.$selected.'>'.ucfirst($provider).'</option>';

                if (!isset($APIkey[$provider])) {
                    $options['service']  = $service;
                    $options['provider'] = $provider;

                    $providerObject = MBJProvider::getInstance($options);
                    $APIkey[$provider] = ($providerObject->needsAPIkey()) ? true : false;
                }

                // look for additional parametrs
                $params   = call_user_func(array('Joaktree\\Component\\Joaktree\\Administrator\\Mapservice\\'.ucfirst($service).'\\'.ucfirst($provider), 'parameters'));
                foreach ((array) $params as $param => $attributes) {
                    if (!$attributes['value']) {
                        $extrafields[$param] = $attributes['type'];
                    }
                }

            }
            $html[] = '</select>';
            //$html[] = '</li>';
            $html[] = '</div>';
            $html[] = '</div>';
        }

        // API keys
        foreach ($APIkey as $provider => $needsKey) {

            if ($needsKey) {
                $tmp = $provider.'APIkey';

                $script[] = '  newKey = newKey + \',\' + \'"\' + \''.$tmp.'\' + \'"\' + \':\' + \'"\' + document.getElementById("MBJ_APIkey'.$provider.'").value + \'"\'; ';

                //$html[] = '<li>';
                $html[] = '<div class="control-group">';
                //$html[] = '<label>'.Text::sprintf('MBJ_PROVIDER_APIKEY', ucfirst($provider)).'</label>';
                $html[] = '<div class="control-label"><label>'.Text::sprintf('MBJ_PROVIDER_APIKEY', ucfirst($provider)).'</label></div>';
                $html[] = '<div class="controls">';
                $html[] = '<input id="MBJ_APIkey'.$provider.'" '
                         .'size="100" '
                         .'value="'.((isset($key->$tmp)) ? $key->$tmp : '').'" '
                         .'onchange="MBJServiceKey();" '
                         .'> ';
                //$html[] = '</li>';
                $html[] = '</div>';
                $html[] = '</div>';
            }

        }

        // Extra fields
        foreach ($extrafields as $extrafield => $type) {
            $script[] = '  newKey = newKey + \',\' + \'"\' + \''.$extrafield.'\' + \'"\' + \':\' + \'"\' + document.getElementById("MBJ_'.$extrafield.'").value + \'"\'; ';

            //$html[] = '<li>';
            $html[] = '<div class="control-group">';
            //$html[] = '<label class="hasTip" title="'.Text::_('MBJ_DESCR_'.strtoupper($extrafield)).'">';
            $html[] = '<div class="control-label"><label class="hasTip" title="'.Text::_('MBJ_DESCR_'.strtoupper($extrafield)).'">';
            $html[] = Text::_('MBJ_LABEL_'.strtoupper($extrafield)).'</label></div>';

            $html[] = '<div class="controls">';
            if ($type == 'boolean') {
                $html[] = '<select id="MBJ_'.$extrafield.'" '
                         .'onchange="MBJServiceKey();" '
                         .'> ';
                $html[] = '<option value="0" '.((!isset($key->$extrafield) || (!$key->$extrafield)) ? 'selected="selected"' : '').'>'.Text::_('JNO').'</option>';
                $html[] = '<option value="1" '.((isset($key->$extrafield)  && ($key->$extrafield)) ? 'selected="selected"' : '').'>'.Text::_('JYES').'</option>';
                $html[] = '</select>';
            } else {
                $html[] = '<input id="MBJ_'.$extrafield.'" '
                         .'size="100" '
                         .'value="'.((isset($key->$extrafield)) ? $key->$extrafield : '').'" '
                         .'onchange="MBJServiceKey();" '
                         .'> ';
            }
            //$html[] = '</li>';
            $html[] = '</div>';
            $html[] = '</div>';
        }

        // max load size
        $script[] = '  newKey = newKey + \',\' + \'"\' + \'maxloadsize\' + \'"\' + \':\' + \'"\' + document.getElementById("MBJ_maxloadsize").value + \'"\'; ';
        //$html[] = '<li>';
        $html[] = '<div class="control-group">';
        //$html[] = '<label>'.Text::_('MBJ_LABEL_LOADSIZE').'</label>';
        $html[] = '<div class="control-label"><label>'.Text::_('MBJ_LABEL_LOADSIZE').'</label></div>';
        $html[] = '<div class="controls">';
        $html[] = '<input id="MBJ_maxloadsize" '
                 .'size="100" '
                 .'value="'.((isset($key->maxloadsize)) ? $key->maxloadsize : '100').'" '
                 .'onchange="MBJServiceKey();" '
                 .'> ';
        //$html[] = '</li>';
        $html[] = '</div>';
        $html[] = '</div>';

        /// RRG 06/01/2017 choix de localisation utilisant ou non la subdivision
        $script[] = '  newKey = newKey + \',\' + \'"\' + \'indsubdiv\' + \'"\' + \':\' + \'"\' + document.getElementById("MBJ_indsubdiv").value + \'"\'; ';
        $html[] = '<div class="control-group">';
        $html[] = '<div class="control-label"><label>'.Text::_('MBJ_IND_SUBDIV').'</label></div>';
        $html[] = '<div class="controls">';
        $html[] = '<select id="MBJ_indsubdiv" '
                 .'onchange="MBJServiceKey();" '
                 .'> ';
        $html[] = '<option value="0" '.((!isset($key->indsubdiv) || (!$key->indsubdiv)) ? 'selected="selected"' : '').'>'.Text::_('JNO').'</option>';
        $html[] = '<option value="1" '.((isset($key->indsubdiv)  && ($key->indsubdiv)) ? 'selected="selected"' : '').'>'.Text::_('JYES').'</option>';
        $html[] = '</select>';
        //$html[] = '</li>';
        $html[] = '</div>';
        $html[] = '</div>';

        //$html[] = '</ul>';

        // Continue script
        $script[] = '  newKey = "{" + newKey + "}"; ';
        $script[] = '  document.getElementById("'.$this->id.'").value = newKey; ';
        $script[] = "}";
        $script[] = 'window.onload = MBJServiceKey;';
        $script[] = '</script>';
        $script[] = '';

        return implode("\n", array_merge($script, $html));
    }

}
