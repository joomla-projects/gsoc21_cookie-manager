<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagefilter
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\SiteRouter;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\LanguageFilter\Extension\LanguageFilter;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new LanguageFilter(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'languagefilter'),
                    Factory::getApplication(),
                    $container->get(LanguageFactoryInterface::class)
                );
                $plugin->setSiteRouter($container->get(SiteRouter::class));

                return $plugin;
            }
        );
    }
};
