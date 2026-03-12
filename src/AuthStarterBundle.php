<?php

declare(strict_types=1);

namespace Vraith\AuthStarter;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vraith\AuthStarter\Command\InstallCommand;

class AuthStarterBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set(InstallCommand::class)
            ->autowire()
            ->autoconfigure()
            ->arg('$projectDir', '%kernel.project_dir%');
    }
}
