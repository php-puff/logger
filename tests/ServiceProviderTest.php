<?php

/*
 * PHP Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Puff\Config\Config;
use Puff\Di\Container;
use Puff\Logger\Handler\FileHandler;
use Puff\Logger\Handler\HandlerInterface;
use Puff\Logger\Handler\StreamHandler;
use Puff\Logger\Logger;
use Puff\Logger\LoggerException;
use Puff\Logger\ServiceProvider;

final class ServiceProviderTest extends TestCase
{
    public function testUsesStderrStreamHandlerByDefault(): void
    {
        $container = new Container();
        (new ServiceProvider($container))->register();

        $logger = $container->make(LoggerInterface::class);
        $property = new \ReflectionProperty(Logger::class, 'handler');
        self::assertInstanceOf(StreamHandler::class, $property->getValue($logger));
    }

    public function testRegistersConfiguredHandlerAndSameLoggerAliases(): void
    {
        $container = new Container();
        $handler = new MemoryHandler();
        $container->instance(MemoryHandler::class, $handler);
        $container->instance('config', new Config(['logger' => [
            'handler' => MemoryHandler::class,
            'level' => 'notice',
        ]]));
        (new ServiceProvider($container))->register();

        $logger = $container->make(LoggerInterface::class);
        self::assertInstanceOf(Logger::class, $logger);
        self::assertSame($logger, $container->make(Logger::class));
        self::assertSame($logger, $container->make('log'));
        $logger->info('hidden');
        $logger->notice('visible');
        self::assertSame(['visible'], $handler->messages);
    }

    public function testRejectsInvalidHandler(): void
    {
        $container = new Container();
        $container->instance('config', new Config(['logger' => ['handler' => \stdClass::class]]));
        (new ServiceProvider($container))->register();

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('must implement');
        $container->make(Logger::class);
    }

    public function testRejectsInvalidLevel(): void
    {
        $container = new Container();
        $container->instance('config', new Config(['logger' => [
            'handler' => MemoryHandler::class,
            'level' => 'verbose',
        ]]));
        (new ServiceProvider($container))->register();

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('Invalid logger level');
        $container->make(Logger::class);
    }

    public function testRejectsInvalidConfigService(): void
    {
        $container = new Container();
        $container->instance('config', 'invalid');
        (new ServiceProvider($container))->register();

        $this->expectException(LoggerException::class);
        $this->expectExceptionMessage('config service must be');
        $container->make(Logger::class);
    }

    public function testConfiguresBuiltinHandlers(): void
    {
        $file = \tempnam(\sys_get_temp_dir(), 'puff-logger-');
        self::assertIsString($file);
        \unlink($file);
        $container = new Container();
        $container->instance('config', new Config(['logger' => [
            'handler' => FileHandler::class,
            'path' => $file,
        ]]));
        (new ServiceProvider($container))->register();

        $container->make(LoggerInterface::class)->info('configured');
        $log = $file . '/' . \date('Ymd') . '.log';
        self::assertFileExists($log);

        \unlink($log);
        \rmdir($file);
    }
}

final class MemoryHandler implements HandlerInterface
{
    /** @var list<string> */
    public array $messages = [];

    public function handle(string $message): void
    {
        $this->messages[] = \preg_replace('/^\[[^]]+] \[[^]]+] /', '', $message) ?? $message;
    }
}
