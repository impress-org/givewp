<?php

namespace Give\API;

interface RestRoute
{
    /**
     * Register the route with WordPress using the register_rest_route function.
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function registerRoute();
}
