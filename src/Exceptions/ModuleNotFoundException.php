<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Exceptions;

use Throwable;

final class ModuleNotFoundException extends ModulithException
{
    private function __construct(
        string $message,
        public readonly string $name,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function forName(string $name, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('Module [%s] is not registered.', $name),
            $name,
            $previous,
        );
    }
}
