<?php

namespace Give\Tests\Config;

interface Config {
    /**
     * Return the test config file path
     *
     * @since 2.22.1
     */
    public function config(): string;

    /**
     * Return the bootstrap file path
     *
     * @since 2.22.1
     */
    public function bootstrap(): string;
}
