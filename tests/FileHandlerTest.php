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
use Puff\Logger\Handler\FileHandler;

final class FileHandlerTest extends TestCase
{
    public function testCreatesDirectoryAndAppendsMessages(): void
    {
        $base = \tempnam(\sys_get_temp_dir(), 'puff-log-');
        self::assertIsString($base);
        \unlink($base);
        $directory = $base . '/logs';
        try {
            $handler = new FileHandler($directory);
            $handler->handle('first');
            $handler->handle('second');
            $file = $directory . '/' . \date('Ymd') . '.log';
            self::assertSame("first\nsecond\n", \file_get_contents($file));
        } finally {
            $file = $directory . '/' . \date('Ymd') . '.log';
            \is_file($file) && \unlink($file);
            \is_dir($directory) && \rmdir($directory);
            \is_dir($base) && \rmdir($base);
        }
    }
}
