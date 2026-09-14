<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

use Puff\Logger\Handler\StreamHandler;

return [
    'handler' => StreamHandler::class,
    'level' => Psr\Log\LogLevel::DEBUG,
    'stream' => 'php://stderr',
    'path' => \dirname(__DIR__) . '/runtime/logs',
];
