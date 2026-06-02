<?php
/**
 * Shared helpers for the release-prep CLI scripts.
 */

declare(strict_types=1);

/**
 * Absolute path to the project root (the directory containing bin/).
 */
function release_project_root(): string
{
    return dirname(__DIR__);
}

/**
 * Parse a "<version> [--dry-run]" argument list.
 *
 * Prints the usage line and exits(1) when the version is missing or is not a
 * semantic version. Returns [string $version, bool $dryRun] on success.
 */
function release_parse_args(array $argv, string $usage): array
{
    $dryRun = false;
    $version = null;

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--dry-run') {
            $dryRun = true;
            continue;
        }
        if ($version === null) {
            $version = $arg;
        }
    }

    if ($version === null || $version === '') {
        fwrite(STDERR, "Error: a version number is required.\n$usage\n");
        exit(1);
    }

    if (!preg_match('/^\d+\.\d+\.\d+(?:[-+].+)?$/', $version)) {
        fwrite(STDERR, "Error: \"$version\" does not look like a semantic version (e.g. 4.16.0).\n");
        exit(1);
    }

    return [$version, $dryRun];
}
