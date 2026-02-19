<?php

declare(strict_types=1);

namespace Valentin\AuthStarter;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Main bundle class for Symfony Auth Starter.
 */
class AuthStarterBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}