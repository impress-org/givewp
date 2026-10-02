<?php

namespace Give\Tests;

use Give\Tests\Config\Config;
use Give\Tests\Config\Local;
use Give\Tests\Config\Workflow;
use Give\Tests\Config\WpEnv;

class TestEnvironment {
    /**
     * @var Local
     */
    private $local;
    /**
     * @var Workflow
     */
    private $workflow;
    /**
     * @var WpEnv
     */
    private $wpEnv;

    /**
     * @since TBD Add the wp-env environment.
     * @since 2.22.1
     */
    public function __construct() {
        $this->local = new Local();
        $this->workflow = new Workflow();
        $this->wpEnv = new WpEnv();
    }

    /**
     * @since TBD
     */
    public function isWpEnv(): bool
    {
        return file_exists($this->wpEnv->bootstrap());
    }

    /**
     * @since 2.22.1
     */
    public function isLocal(): bool
    {
        return file_exists($this->local->config());
    }

    /**
     * @since 2.22.1
     */
    public function isWorkflow(): bool
    {
        return file_exists($this->workflow->config());
    }

    /**
     * @since TBD Add the wp-env environment.
     * @since 2.22.1
     */
    public function hasConfig(): bool
    {
        return $this->isWpEnv() || $this->isLocal() || $this->isWorkflow();
    }

    /**
     * @since TBD Add the wp-env environment, which wins because the checkout it mounts can carry a local config too.
     * @since 2.22.1
     */
    public function current(): Config
    {
        if ($this->isWpEnv()) {
            return $this->wpEnv;
        }

        if ($this->isWorkflow()){
            return $this->workflow;
        }

        return $this->local;
    }
}
