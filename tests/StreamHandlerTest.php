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
use Puff\Logger\Handler\StreamHandler;

final class StreamHandlerTest extends TestCase
{
    public function testAppendsMessagesToConfiguredStream(): void
    {
        $file = \tempnam(\sys_get_temp_dir(), 'puff-stream-');
        self::assertIsString($file);
        try {
            $handler = new StreamHandler($file);
            $handler->handle('first');
            $handler->handle('second');

            self::assertSame("first\nsecond\n", \file_get_contents($file));
        } finally {
            \unlink($file);
        }
    }

    public function testRejectsEmptyStream(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StreamHandler('');
    }
}
