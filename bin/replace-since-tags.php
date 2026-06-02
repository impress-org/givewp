<?php
/**
 * Release prep: replace placeholder docblock tags with the release version.
 *
 *   "@unreleased"        ->  "@since <version>"   (any trailing description is preserved)
 *   "@since TBD"         ->  "@since <version>"
 *
 * Scans the plugin source (php, js, jsx, ts, tsx) and skips build/vendor dirs.
 *
 * Usage:
 *   php bin/replace-since-tags.php <version> [--dry-run]
 *   composer run release:replace-since-tags 4.16.0
 */

declare(strict_types=1);

require __DIR__ . '/lib/release-utils.php';

[$version, $dryRun] = release_parse_args(
    $argv,
    'Usage: php bin/replace-since-tags.php <version> [--dry-run]'
);

$root = release_project_root();

// Directories we never want to touch.
$excludedDirs = [
    '.git',
    'node_modules',
    'vendor',
    'bin',
    'assets/dist',
    'build',
];

$extensions = ['php', 'js', 'jsx', 'ts', 'tsx'];

// "@unreleased" (optionally followed by description) and "@since TBD".
$patterns = [
    '/@unreleased\b/'        => '@since ' . $version,
    '/@since\s+TBD\b/'       => '@since ' . $version,
];

$excludedAbsolute = array_map(static function ($dir) use ($root) {
    return $root . '/' . $dir;
}, $excludedDirs);

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static function ($current) use ($excludedAbsolute) {
            $path = $current->getPathname();
            foreach ($excludedAbsolute as $excluded) {
                if ($path === $excluded || strpos($path, $excluded . '/') === 0) {
                    return false;
                }
            }
            return true;
        }
    )
);

$filesChanged = 0;
$totalReplacements = 0;

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $extensions, true)) {
        continue;
    }

    $contents = file_get_contents($file->getPathname());

    // Cheap pre-filter: skip the regex work unless a placeholder token is present.
    if (strpos($contents, '@unreleased') === false && strpos($contents, 'TBD') === false) {
        continue;
    }

    $fileReplacements = 0;
    $updated = $contents;

    foreach ($patterns as $pattern => $replacement) {
        $updated = preg_replace($pattern, $replacement, $updated, -1, $count);
        $fileReplacements += $count;
    }

    if ($fileReplacements === 0) {
        continue;
    }

    $relative = substr($file->getPathname(), strlen($root) + 1);
    echo "  ✓ $relative ($fileReplacements)\n";

    if (!$dryRun) {
        file_put_contents($file->getPathname(), $updated);
    }

    $filesChanged++;
    $totalReplacements += $fileReplacements;
}

echo ($dryRun ? "[dry run] " : "")
    . "Replaced $totalReplacements placeholder tag(s) with @since $version across $filesChanged file(s).\n";

exit(0);
