<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

use Puff\Di\Facade;

/**
 * @method static void emergency(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void alert(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void critical(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void error(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void warning(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void notice(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void info(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void debug(string|\Stringable $message, array<string, mixed> $context = [])
 * @method static void log(mixed $level, string|\Stringable $message, array<string, mixed> $context = [])
 */
final class Log extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'log';
    }
}
