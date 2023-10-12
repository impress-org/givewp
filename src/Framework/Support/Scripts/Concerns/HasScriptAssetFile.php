<?php

namespace Give\Framework\Support\Scripts\Concerns;

trait HasScriptAssetFile {
     /**
      * @since 3.0.0
     */
    protected function getScriptAssetDependencies(string $path)
    {
        $assets = $this->getScriptAsset($path);

        return $assets['dependencies'];
    }

    /**
     * @since 3.0.0
     */
    protected function getScriptAssetVersion(string $path)
    {
        $assets = $this->getScriptAsset($path);

        return $assets['version'];
    }

    /**
     * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-dependency-extraction-webpack-plugin/#wordpress
     *
     * @since 3.0.0
     */
    protected function getScriptAsset(string $path): array
    {
        return file_exists($path) ? require $path : ['dependencies' => [], 'version' => filemtime($path)];
    }
}