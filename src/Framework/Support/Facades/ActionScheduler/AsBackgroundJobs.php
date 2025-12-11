<?php

namespace Give\Framework\Support\Facades\ActionScheduler;

use Give\Framework\Support\Facades\Facade;

/**
 * @since 3.6.0
 *
 * @since 4.0.0 getActionsByGroup - switch parameter $status position with $returnFormat position
 *
 * @method static int enqueueAsyncAction(string $hook, array $args, string $group, bool $unique = false, int $priority = 10)
 * @method static array getActionByHookArgsGroup(string $hook, array $args, string $group, string $returnFormat = OBJECT, string $status = '')
 * @method static array getActionsByGroup(string $group, string $status = '', string $returnFormat = OBJECT)
 * @method static int deleteActionsByGroup(string $group, string $status = '')
 */
class AsBackgroundJobs extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return AsBackgroundJobsFacade::class;
    }
}
