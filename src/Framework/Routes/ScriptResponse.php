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
     */
    protected string $objectName = '';

    /**
     * @var callable|null
     *
     * @since TBD
     */
    protected $data;

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
     * Expose data to the script as a global object, the way wp_localize_script()
     * does for enqueued scripts. The callable runs at request time, after
     * translations are loaded, so the data can carry the site's translated
     * strings.
     *
     * @since TBD
     *
     * @param callable $data Returns the array to expose; must be JSON-encodable
     */
    public function localize(string $objectName, callable $data): self
    {
        $this->objectName = $objectName;
        $this->data = $data;

        return $this;
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

        $prologue = $this->prologue();
        $etag = $this->etag($prologue);

        header('Content-Type: application/javascript; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header("Cache-Control: public, max-age={$this->maxAge}");
        header("ETag: {$etag}");

        if ($this->matchesIfNoneMatch($etag, $_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) {
            status_header(304);
            exit;
        }

        echo $prologue;
        readfile($this->file);
        exit;
    }

    /**
     * The statement printed ahead of the file when data is localized.
     *
     * @since TBD
     */
    public function prologue(): string
    {
        if (!$this->data) {
            return '';
        }

        return sprintf("var %s = %s;\n", $this->objectName, wp_json_encode(($this->data)()));
    }

    /**
     * The build hash from the asset file, quoted as a strong validator. Localized
     * data is part of the response, so its hash is part of the validator too.
     *
     * @since TBD
     */
    public function etag(string $prologue = ''): string
    {
        $version = (string) ScriptAsset::getVersion($this->assetFile);

        if ($prologue !== '') {
            $version .= '-' . substr(md5($prologue), 0, 8);
        }

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
