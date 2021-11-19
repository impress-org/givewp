<?php

namespace Give\Route;

interface Route
{

    /**
     * Initializes the route with the intent of setting up the proper hooks and such
     * necessary for the route.
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function init();
}
