<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger;

use Puff\Config\Config;
use Puff\Di\Container;
use Puff\Logger\Handler\FileHandler;
use Puff\Logger\Handler\HandlerInterface;
use Puff\Logger\Handler\StreamHandler;

final readonly class HandlerFactory
{
    public function __construct(private Container $container)
    {
    }

    public function make(string $class, ?Config $config = null): HandlerInterface
    {
        $handler = match ($class) {
            StreamHandler::class => new StreamHandler($this->string($config, 'logger.stream', 'php://stderr')),
            FileHandler::class => new FileHandler($this->string(
                $config,
                'logger.path',
                \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'puff' . DIRECTORY_SEPARATOR . 'logs',
            )),
            default => $this->container->make($class),
        };

        if (!$handler instanceof HandlerInterface) {
            throw new LoggerException("Logger handler [{$class}] must implement " . HandlerInterface::class . '.');
        }
        return $handler;
    }

    private function string(?Config $config, string $key, string $default): string
    {
        $value = $config?->get($key, $default) ?? $default;
        if (!\is_string($value) || $value === '') {
            throw new LoggerException("Logger configuration [{$key}] must be a non-empty string.");
        }
        return $value;
    }
}
