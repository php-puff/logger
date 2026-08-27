<?php

/*
 * PHP Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger\Handler;

final class StreamHandler implements HandlerInterface
{
    /** @var resource|null */
    private $handle = null;

    public function __construct(private string $stream = 'php://stderr')
    {
        if ($stream === '') {
            throw new \InvalidArgumentException('Log stream must not be empty.');
        }
    }

    public function handle(string $message): void
    {
        $handle = $this->resource();
        $locked = !$this->isPhpStream() && \flock($handle, LOCK_EX);
        try {
            if (\fwrite($handle, $message . PHP_EOL) === false) {
                throw new \RuntimeException("Unable to write log stream [{$this->stream}].");
            }
        } finally {
            if ($locked) {
                \flock($handle, LOCK_UN);
            }
        }
    }

    public function __destruct()
    {
        if (\is_resource($this->handle)) {
            \fclose($this->handle);
        }
    }

    /** @return resource */
    private function resource()
    {
        if (\is_resource($this->handle)) {
            return $this->handle;
        }
        $handle = @\fopen($this->stream, 'ab');
        if (!\is_resource($handle)) {
            throw new \RuntimeException("Unable to open log stream [{$this->stream}].");
        }
        return $this->handle = $handle;
    }

    private function isPhpStream(): bool
    {
        return \str_starts_with($this->stream, 'php://');
    }
}
