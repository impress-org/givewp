<?php

namespace Give\Framework\Support\Facades\Scripts;

use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
 * 
 * @method static get(string $path): array
 * @method static getVersion(string $path): int|string
 * @method static getDependencies(string $path): array
 */
class ScriptAsset extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return ScriptAssetFacade::class;
    }
}
