<?php
/**
 * Joomla! plugin Joaktree content
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
defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joaktree\Plugin\EditorsXtd\Joaktreemap\Extension\Joaktreemap;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
				$dispatcher = $container->get(DispatcherInterface::class);
                $plugin = new Joaktreemap(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('editors-xtd', 'joaktreemap')
                );
                $plugin->setApplication(Factory::getApplication());
                return $plugin;
            }
        );
    }
};
