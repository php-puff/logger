<?php

/*
 * PHP Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger\Handler;

final class FileHandler implements HandlerInterface
{
    private bool $directoryReady;

    public function __construct(private string $directory)
    {
        if ($directory === '') {
            throw new \InvalidArgumentException('Log directory must not be empty.');
        }
        $this->directoryReady = \is_dir($directory);
    }

    public function handle(string $message): void
    {
        if (!$this->directoryReady
            && !@\mkdir($this->directory, 0755, true)
            && !\is_dir($this->directory)
        ) {
            throw new \RuntimeException("Unable to create log directory [{$this->directory}].");
        }
        $this->directoryReady = true;
        $file = \rtrim($this->directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . \date('Ymd') . '.log';
        if (\file_put_contents($file, $message . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException("Unable to write log file [{$file}].");
        }
    }
}
