<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Valentin\AuthStarter\Command\InstallCommand;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // Register the install command
    $services->set(InstallCommand::class)
        ->arg('$projectDir', param('kernel.project_dir'))
        ->tag('console.command');

    // Auto-register controllers
    $services->load('Valentin\\AuthStarter\\Controller\\', __DIR__.'/../../Controller')
        ->tag('controller.service_arguments');

    // Auto-register security authenticators
    $services->load('Valentin\\AuthStarter\\Security\\', __DIR__.'/../../Security');
};