<?php

namespace Give\Tests;

use Give\Tests\Config\Config;
use Give\Tests\Config\Local;
use Give\Tests\Config\Workflow;

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
     * @since 2.22.1
     */
    public function __construct() {
        $this->local = new Local();
        $this->workflow = new Workflow();
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
     * @since 2.22.1
     */
    public function hasConfig(): bool
    {
        return $this->isLocal() || $this->isWorkflow();
    }

    /**
     * @since 2.22.1
     */
    public function current(): Config
    {
        if ($this->isWorkflow()){
            return $this->workflow;
        }

        return $this->local;
    }
}
