<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Raised when syncing ACF fields for a template page from the legacy site fails.
 *
 * "Recoverable" failures (the legacy site was unreachable, its markup could
 * not be parsed) are recorded per page and the sync carries on. Anything else
 * is treated as a bug: it is wrapped in a non-recoverable AcfSyncException and
 * stops the run so it isn't silently reduced to a message string.
 */
class AcfSyncException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $page,
        public readonly string $lang,
        public readonly bool $recoverable = true,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function legacyFetchFailed(string $page, string $lang, Throwable $previous): self
    {
        return new self(
            "Could not fetch the legacy page for {$lang}/{$page}: {$previous->getMessage()}",
            $page,
            $lang,
            true,
            $previous,
        );
    }

    public static function unexpected(string $page, string $lang, Throwable $previous): self
    {
        return new self(
            "Unexpected error while syncing {$lang}/{$page}: {$previous->getMessage()}",
            $page,
            $lang,
            false,
            $previous,
        );
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        return [
            'page' => $this->page,
            'lang' => $this->lang,
            'recoverable' => $this->recoverable,
            'cause' => $this->getPrevious() ? get_class($this->getPrevious()) : null,
        ];
    }
}
