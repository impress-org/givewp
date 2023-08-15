<?php

namespace Give\FormMigration;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;

class Pipeline
{
    protected $steps = [];
    protected $payload;

    protected $beforeStep;
    protected $afterStep;

    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    public function beforeEach(callable $beforeStep): Pipeline
    {
        $this->beforeStep = $beforeStep;
        return $this;
    }

    public function afterEach(callable $afterStep): Pipeline
    {
        $this->afterStep = $afterStep;
        return $this;
    }

    public function process(FormMigrationPayload $payload): StepProcessor
    {
        $processor = new StepProcessor($payload);
        foreach ($this->steps as $stepClass) {
            if ($this->beforeStep) call_user_func($this->beforeStep, $stepClass, $payload);
            $_payload = unserialize(serialize($payload));
            $processor(new $stepClass($payload));
            if ($this->afterStep) call_user_func($this->afterStep, $stepClass, $payload, $_payload);
        }

        return $processor;
    }
}
