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
use Puff\Di\Container;

final class HelperTest extends TestCase
{
    protected function tearDown(): void
    {
        Container::setInstance();
    }

    public function testReturnsRegisteredPsrLogger(): void
    {
        $container = new Container();
        $expected = new MemoryHandlerLogger();
        $container->instance(LoggerInterface::class, $expected);
        Container::setInstance($container);

        self::assertSame($expected, \logger());
    }

    public function testRejectsMissingContainer(): void
    {
        Container::setInstance();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('container has not been initialized');
        \logger();
    }

    public function testRejectsMissingLoggerService(): void
    {
        Container::setInstance(new Container());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('logger service is not registered');
        \logger();
    }
}

final class MemoryHandlerLogger extends \Psr\Log\AbstractLogger
{
    /** @param array<string, mixed> $context */
    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
    }
}
