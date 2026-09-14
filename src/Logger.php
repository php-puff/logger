<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/logger
 * https://github.com/php-puff/logger/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Puff\Logger\Handler\HandlerInterface;

final class Logger extends AbstractLogger
{
    /** @var array<string, int> */
    private const LEVELS = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    public function __construct(private readonly HandlerInterface $handler, private readonly string $level = LogLevel::DEBUG)
    {
        self::validateLevel($level);
    }

    /** @param array<string, mixed> $context */
    public function log(mixed $level, string|\Stringable $message, array $context = []): void
    {
        if (!\is_string($level)) {
            throw new LoggerException('Log level must be a string.');
        }
        self::validateLevel($level);
        if (self::LEVELS[$level] > self::LEVELS[$this->level]) {
            return;
        }
        $this->handler->handle($this->format($level, (string) $message, $context));
    }

    public static function validateLevel(string $level): void
    {
        if (!isset(self::LEVELS[$level])) {
            throw new LoggerException("Invalid log level [{$level}].");
        }
    }

    /** @param array<string, mixed> $context */
    private function format(string $level, string $message, array $context): string
    {
        $message = $this->interpolate($message, $context);
        $suffix = '';
        if ($context !== []) {
            $encoded = \json_encode(
                $this->normalize($context),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE,
            );
            $suffix = ' ' . ($encoded === false ? '[unserializable context]' : $encoded);
        }
        return \sprintf(
            '[%s] [%s] %s%s',
            (new \DateTimeImmutable())->format('Y-m-d H:i:s.u'),
            $level,
            $message,
            $suffix,
        );
    }

    /** @param array<string, mixed> $context */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if ($value === null || \is_scalar($value) || $value instanceof \Stringable) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }
        return \strtr($message, $replace);
    }

    /** @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function normalize(array $context): array
    {
        foreach ($context as $key => $value) {
            if ($value instanceof \Throwable) {
                $context[$key] = [
                    'class' => $value::class,
                    'message' => $value->getMessage(),
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                ];
                continue;
            }
            if (\is_resource($value)) {
                $context[$key] = \get_resource_type($value);
            }
        }
        return $context;
    }
}
