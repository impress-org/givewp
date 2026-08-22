<?php

namespace Give\DonationForms\Actions;

use WP_Dependencies;

/**
 * Pins the script and style queues to what has already been enqueued, so a form view route prints
 * its own assets and nothing else.
 *
 * The form view routes render a standalone document by calling wp_print_styles(),
 * wp_print_head_scripts() and wp_print_footer_scripts() directly. Each of those fires an action that
 * plugins legitimately enqueue from, so the queues keep growing after the route has finished
 * enqueuing. Constraining the handles at print time, instead of dequeuing on a hook, keeps the route
 * independent of the priority the other plugin picked.
 *
 * @since 4.16.7
 */
class IsolateEnqueuedFormViewAssets
{
    /**
     * @since 4.16.7
     */
    public function __invoke(): void
    {
        $this->pinQueue(wp_scripts(), 'print_scripts_array', 'scripts');
        $this->pinQueue(wp_styles(), 'print_styles_array', 'styles');
    }

    /**
     * @since 4.16.7
     *
     * @param WP_Dependencies $dependencies The queue to pin.
     * @param string          $filter       Print-time filter the queue is constrained through.
     * @param string          $type         Either "scripts" or "styles".
     */
    private function pinQueue(WP_Dependencies $dependencies, string $filter, string $type): void
    {
        $allowedHandles = $this->expandDependencies($dependencies, $dependencies->queue);

        /**
         * Filters the handles a donation form view route may print. Add a handle here to let an
         * asset enqueued after the form has been prepared through to the rendered form.
         *
         * @since 4.16.7
         *
         * @param string[] $allowedHandles Handles the route will print.
         * @param string   $type           Either "scripts" or "styles".
         */
        $allowedHandles = apply_filters(
            'givewp_donation_form_view_allowed_asset_handles',
            $allowedHandles,
            $type
        );

        add_filter($filter, static function ($handles) use ($allowedHandles) {
            return array_values(array_intersect($handles, $allowedHandles));
        });
    }

    /**
     * Resolves the given handles together with everything they depend on. Registered dependencies
     * may be cyclic, so handles are only ever visited once.
     *
     * @since 4.16.7
     *
     * @param WP_Dependencies $dependencies Queue the handles are registered in.
     * @param string[]        $handles      Handles to resolve.
     *
     * @return string[]
     */
    private function expandDependencies(WP_Dependencies $dependencies, array $handles): array
    {
        $resolved = [];

        while ($handles) {
            /* WordPress allows arguments to be appended to a queued handle. */
            $handle = explode('?', (string)array_shift($handles))[0];

            if (isset($resolved[$handle])) {
                continue;
            }

            $resolved[$handle] = true;

            if (isset($dependencies->registered[$handle])) {
                $handles = array_merge($handles, $dependencies->registered[$handle]->deps);
            }
        }

        return array_keys($resolved);
    }
}
