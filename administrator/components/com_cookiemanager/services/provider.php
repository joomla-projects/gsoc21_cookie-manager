<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
// use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The cookiemanager service provider.
 *
 * @since  4.0.0
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new CategoryFactory('\\Joomla\\Component\\Cookiemanager'));
		$container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Cookiemanager'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Cookiemanager'));
		$container->registerServiceProvider(new RouterFactory('\\Joomla\\Component\\Cookiemanager'));

		$container->set(
				ComponentInterface::class,
				function (Container $container)
				{
					$component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));

					$component->setMVCFactory($container->get(MVCFactoryInterface::class));

					return $component;
		}
		);
	}
};
