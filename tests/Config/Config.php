<?php

namespace GiveTests\Config;

interface Config {
    /**
     * Return the test config file path
     *
     * @unreleased
     */
    public function config(): string;

    /**
     * Return the bootstrap file path
     *
     * @unreleased
     */
    public function bootstrap(): string;

    /**
     * Return the functions file path
     *
     * @unreleased
     */
    public function functions(): string;
}
