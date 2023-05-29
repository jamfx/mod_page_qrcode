<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_page_qr_code
 *
 * @copyright   www.nik-o-mat.de
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The article latest module service provider.
 *
 * @since  2.0.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Nikomate\\Module\\PageQrCode'));
        $container->registerServiceProvider(new HelperFactory('\\Nikomate\\Module\\PageQrCode\\Site\\Helper'));

        $container->registerServiceProvider(new Module());
    }
};
