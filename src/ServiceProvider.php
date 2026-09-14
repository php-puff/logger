<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Puff\Config\Config;
use Puff\Di\ServiceProvider as BaseServiceProvider;
use Puff\Logger\Handler\StreamHandler;

final class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->alias(Logger::class, 'log');
        $this->app->singleton(Logger::class, function (): Logger {
            $config = $this->configuration();
            $handlerClass = $config?->get('logger.handler', StreamHandler::class) ?? StreamHandler::class;
            if (!\is_string($handlerClass) || !\class_exists($handlerClass)) {
                throw new LoggerException('Logger configuration [logger.handler] must be an existing class.');
            }
            $factory = $this->app->make(HandlerFactory::class);
            if (!$factory instanceof HandlerFactory) {
                throw new LoggerException('Unable to resolve the logger handler factory.');
            }
            $handler = $factory->make($handlerClass, $config);
            $level = $config?->get('logger.level', LogLevel::DEBUG) ?? LogLevel::DEBUG;
            if (!\is_string($level)) {
                throw new LoggerException('Logger configuration [logger.level] must be a string.');
            }
            try {
                Logger::validateLevel($level);
            } catch (LoggerException $exception) {
                throw new LoggerException("Invalid logger level [{$level}].", 0, $exception);
            }
            return new Logger($handler, $level);
        });
        $this->app->alias(Logger::class, LoggerInterface::class);
    }

    private function configuration(): ?Config
    {
        if (!$this->app->bound('config')) {
            return null;
        }
        $config = $this->app->make('config');
        if (!$config instanceof Config) {
            throw new LoggerException('The config service must be a ' . Config::class . ' instance.');
        }
        return $config;
    }
}
