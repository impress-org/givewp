<?php
/**
 * Release prep: update every version string declared in .puprc -> paths.versions.
 *
 * Each entry in paths.versions has a "file" and a "regex" with two capture groups:
 *   group 1 = the literal prefix to keep (e.g. "Version: ")
 *   group 2 = the version to replace
 *
 * Usage:
 *   php bin/update-versions.php <version> [--dry-run]
 *   composer run release:bump-versions 4.16.0
 */

declare(strict_types=1);

require __DIR__ . '/lib/release-utils.php';

[$version, $dryRun] = release_parse_args(
    $argv,
    'Usage: php bin/update-versions.php <version> [--dry-run]'
);

$root = release_project_root();
$puprcPath = $root . '/.puprc';

if (!is_file($puprcPath)) {
    fwrite(STDERR, "Error: .puprc not found at $puprcPath\n");
    exit(1);
}

$puprc = json_decode(file_get_contents($puprcPath), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "Error: could not parse .puprc: " . json_last_error_msg() . "\n");
    exit(1);
}

$versionFiles = $puprc['paths']['versions'] ?? [];

if (empty($versionFiles)) {
    fwrite(STDERR, "Error: no entries found under paths.versions in .puprc\n");
    exit(1);
}

echo ($dryRun ? "[dry run] " : "") . "Setting version to $version in " . count($versionFiles) . " location(s):\n";

$hadError = false;

foreach ($versionFiles as $entry) {
    $file = $entry['file'] ?? null;
    $regex = $entry['regex'] ?? null;

    if (!$file || !$regex) {
        fwrite(STDERR, "  ! skipping malformed entry: " . json_encode($entry) . "\n");
        $hadError = true;
        continue;
    }

    $path = $root . '/' . $file;

    if (!is_file($path)) {
        fwrite(STDERR, "  ! $file not found, skipping\n");
        $hadError = true;
        continue;
    }

    $contents = file_get_contents($path);
    $pattern = '~' . $regex . '~';

    $updated = preg_replace_callback(
        $pattern,
        static function ($matches) use ($version) {
            // Keep capture group 1 (the prefix), swap group 2 (the version).
            return $matches[1] . $version;
        },
        $contents,
        -1,
        $count
    );

    if ($updated === null) {
        fwrite(STDERR, "  ! invalid regex for $file: $regex\n");
        $hadError = true;
        continue;
    }

    if ($count === 0) {
        fwrite(STDERR, "  ! no match in $file for regex: $regex\n");
        $hadError = true;
        continue;
    }

    if ($updated === $contents) {
        echo "  = $file already at $version ($regex)\n";
        continue;
    }

    if (!$dryRun) {
        file_put_contents($path, $updated);
    }

    echo "  ✓ $file ($count replacement" . ($count === 1 ? '' : 's') . ")\n";
}

exit($hadError ? 1 : 0);
