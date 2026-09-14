<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Puff\Di\Container;

if (!\function_exists('logger')) {
    function logger(): LoggerInterface
    {
        $container = Container::getInstance();
        if ($container === null) {
            throw new LogicException('The Puff container has not been initialized.');
        }

        if (!$container->bound(LoggerInterface::class)) {
            throw new LogicException('The logger service is not registered.');
        }
        $logger = $container->get(LoggerInterface::class);
        if (!$logger instanceof LoggerInterface) {
            throw new LogicException('The logger service is not registered.');
        }
        return $logger;
    }
}
