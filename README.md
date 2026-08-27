# Puff Logger

`puff/logger` is a PSR-3 logger with configurable output handlers and level filtering.

```php
'logger' => [
    'handler' => Puff\Logger\Handler\StreamHandler::class,
    'level' => Psr\Log\LogLevel::INFO,
    'stream' => 'php://stderr',
    'path' => dirname(__DIR__) . '/runtime/logs',
],
```

The default `StreamHandler` writes to `php://stderr`, which keeps command output on stdout clean and works with process supervisors and container log collectors. `FileHandler` remains available and writes daily files to `logger.path`. Custom handlers must implement `Puff\Logger\Handler\HandlerInterface` and are instantiated through the Puff container.

Application entry points can use the `logger()` helper, which returns the registered PSR-3 logger:

```php
logger()->info('User logged in', ['user_id' => $userId]);
logger()->error('Payment failed', ['exception' => $exception]);
```

The optional `Log` facade resolves the same PSR-3 logger from the container:

```php
Log::info('User {id} logged in', ['id' => 42]);
Log::error('Payment failed', ['exception' => $exception]);
```

`Log` is a global facade; the concrete implementation remains `Puff\Logger\Logger`.

Scalar PSR-3 placeholders are interpolated, while the complete context remains available as structured JSON:

```php
logger()->info('User {id} logged in', ['id' => 42]);
```

Throwable context is normalized to its class, message, code, file, and line. Stack traces are intentionally omitted to keep production logs bounded.

Services should continue to receive `Psr\Log\LoggerInterface` through constructor injection. For repeated logging in a loop, resolve the helper once and reuse the returned logger.
