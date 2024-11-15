<?php
/**
 * Joomla! module Joaktree Related Items
 * file		JoaktreeHelper - helper.php
 *
 * @version	1.5.4
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2020)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Module linking articles to persons in Joaktree component
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Related items module service provider.
 *
 * @since  4.2.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Joaktree\\Module\\Relateditems'));
        $container->registerServiceProvider(new HelperFactory('\\Joaktree\\Module\\Relateditems\\Site\\Helper'));

        $container->registerServiceProvider(new Module());
    }
};
