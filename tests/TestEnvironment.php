<?php

namespace GiveTests;

use GiveTests\Config\Config;
use GiveTests\Config\Local;
use GiveTests\Config\Workflow;

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
     * @unreleased
     */
    public function __construct() {
        $this->local = new Local();
        $this->workflow = new Workflow();
    }

    /**
     * @unreleased
     */
    public function isLocal(): bool
    {
        return file_exists($this->local->config());
    }

    /**
     * @unreleased
     */
    public function isWorkflow(): bool
    {
        return file_exists($this->workflow->config());
    }

    /**
     * @unreleased
     */
    public function hasConfig(): bool
    {
        return $this->isLocal() || $this->isWorkflow();
    }

    /**
     * @unreleased
     */
    public function current(): Config
    {
        if ($this->isWorkflow()){
            return $this->workflow;
        }

        return $this->local;
    }
}
