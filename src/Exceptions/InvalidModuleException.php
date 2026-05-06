<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Exceptions;

use Throwable;

final class InvalidModuleException extends ModulithException
{
    private function __construct(
        string $message,
        public readonly string $path,
        public readonly string $reason,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function forPath(string $path, string $reason, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Invalid module at [%s]: %s', $path, $reason),
            $path,
            $reason,
            $previous,
        );
    }
}
