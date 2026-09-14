<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger\Tests;

use PHPUnit\Framework\TestCase;
use Puff\Di\Container;
use Puff\Logger\Handler\HandlerInterface;
use Puff\Logger\Logger;
use Puff\Logger\ServiceProvider;

final class FacadeTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::setInstance();
    }

    public function testForwardsStaticCallsToRegisteredLogger(): void
    {
        $container = new Container();
        $handler = new FacadeMemoryHandler();
        $container->instance(Logger::class, new Logger($handler));
        (new ServiceProvider($container))->register();
        Container::setInstance($container);

        \Log::info('Facade {state}', ['state' => 'ready']);

        self::assertStringContainsString('Facade ready', $handler->message);
    }
}

final class FacadeMemoryHandler implements HandlerInterface
{
    public string $message = '';

    public function handle(string $message): void
    {
        $this->message = $message;
    }
}
