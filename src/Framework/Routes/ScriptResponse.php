<?php

namespace Give\Framework\Routes;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * Sends a built script with revalidation headers. The ETag is the build hash
 * from the script's asset file, so it changes exactly when the bundle does.
 *
 * @since TBD
 */
class ScriptResponse
{
    /**
     * Seconds a browser may reuse the response before revalidating its ETag.
     *
     * @since TBD
     */
    protected int $maxAge = HOUR_IN_SECONDS;

    /**
     * @since TBD
     */
    protected string $file;

    /**
     * @since TBD
     */
    protected string $assetFile;

    /**
     * @since TBD
     *
     * @param string $file Absolute path to the built script. Its version comes
     *                     from the .asset.php file @wordpress/scripts writes
     *                     next to it.
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->assetFile = preg_replace('/\.js$/', '.asset.php', $file);
    }

    /**
     * @since TBD
     */
    public function send(): void
    {
        if (!is_readable($this->file)) {
            status_header(404);
            exit;
        }

        $etag = $this->etag();

        header('Content-Type: application/javascript; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header("Cache-Control: public, max-age={$this->maxAge}");
        header("ETag: {$etag}");

        if ($this->matchesIfNoneMatch($etag, $_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) {
            status_header(304);
            exit;
        }

        readfile($this->file);
        exit;
    }

    /**
     * The build hash from the asset file, quoted as a strong validator.
     *
     * @since TBD
     */
    public function etag(): string
    {
        $version = (string) ScriptAsset::getVersion($this->assetFile);

        return sprintf('"%s"', $version);
    }

    /**
     * Weak validators and the "-gzip" suffix mod_deflate appends both name the same file.
     *
     * @since TBD
     */
    public function matchesIfNoneMatch(string $etag, string $header): bool
    {
        if ($header === '') {
            return false;
        }

        $candidates = array_map(static function (string $value): string {
            return trim(str_replace(['W/', '-gzip"'], ['', '"'], $value));
        }, explode(',', wp_unslash($header)));

        return in_array($etag, $candidates, true) || in_array('*', $candidates, true);
    }
}
