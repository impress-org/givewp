<?php

namespace Give\FormMigration;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;

class StepProcessor
{
    /**
     * @var FormMigrationPayload
     */
    protected $payload;

    public function __construct(FormMigrationPayload $payload)
    {
        $this->payload = $payload;
    }

    public function __invoke(FormMigrationStep $step)
    {
        if($step->canHandle()) {
            $step->process();
        }
    }

    public function finally(callable $callback)
    {
        $callback($this->payload);
    }
}
