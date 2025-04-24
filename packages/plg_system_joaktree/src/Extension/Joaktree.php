<?php
/**
 * Joomla! plugin Joaktree System
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */

namespace Joaktree\Plugin\System\Joaktree\Extension;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joomla\Event\SubscriberInterface;

final class Joaktree extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {

        return [
            'onAfterGetMenuTypeOptions' => 'onAfterGetMenuTypeOptions'
        ];
    }
    // force Joaktree
    public static function onAfterGetMenuTypeOptions($event)
    {
        $context    = $event[0];
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript('jtjs', JoaktreeHelper::jsfile());

        // $subject    = $event[1];
        // $params     = $event[2];
    }
}
