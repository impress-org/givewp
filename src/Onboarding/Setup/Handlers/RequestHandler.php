<?php

namespace Give\Onboarding\Setup\Handlers;

defined('ABSPATH') || exit;

interface RequestHandler
{

    /**
     * Determines if a request should be handled and then handles it.
     *
     * @since 2.8.0
     *
     * @return null
     */
    public function maybeHandle();

    /**
     * Handles a request.
     *
     * @since 2.8.0
     *
     * @return null
     */
    public function handle();
}
