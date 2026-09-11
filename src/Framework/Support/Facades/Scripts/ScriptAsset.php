<?php

namespace Give\Framework\Support\Facades\Scripts;

use Give\Framework\Support\Facades\Facade;

/**
 * @since TBD Correct the @method annotations so static analysis resolves the return types
 * @since 2.32.0
 *
 * @method static array{dependencies: array<string>, version: int|string} get(string $path)
 * @method static int|string getVersion(string $path)
 * @method static array<string> getDependencies(string $path)
 */
class ScriptAsset extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return ScriptAssetFacade::class;
    }
}
