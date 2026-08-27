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
use Puff\Logger\Logger;

final class LoggerTest extends TestCase
{
    public function testFiltersAllPsrLevelsAtInfoThreshold(): void
    {
        $handler = new MemoryHandler();
        $logger = new Logger($handler, 'info');

        $logger->emergency('emergency');
        $logger->alert('alert');
        $logger->critical('critical');
        $logger->error('error');
        $logger->warning('warning');
        $logger->notice('notice');
        $logger->info('info');
        $logger->debug('debug');

        self::assertCount(7, $handler->messages);
        self::assertNotContains('debug', $handler->messages);
    }

    public function testFormatsContext(): void
    {
        $handler = new MemoryHandler();
        (new Logger($handler))->info('query', ['elapsed' => 0.1]);

        self::assertStringContainsString('query {"elapsed":0.1}', $handler->messages[0]);
    }

    public function testInterpolatesScalarPlaceholders(): void
    {
        $handler = new MemoryHandler();
        (new Logger($handler))->info('User {id} is {state}', ['id' => 10, 'state' => 'active']);

        self::assertStringContainsString('User 10 is active', $handler->messages[0]);
    }

    public function testNormalizesThrowableContext(): void
    {
        $handler = new MemoryHandler();
        (new Logger($handler))->error('Failed', ['exception' => new \RuntimeException('Broken', 42)]);

        self::assertStringContainsString('"class":"RuntimeException"', $handler->messages[0]);
        self::assertStringContainsString('"message":"Broken"', $handler->messages[0]);
        self::assertStringContainsString('"code":42', $handler->messages[0]);
    }
}
