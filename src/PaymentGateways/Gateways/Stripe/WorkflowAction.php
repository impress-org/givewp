<?php

namespace Give\PaymentGateways\Gateways\Stripe;

class WorkflowAction
{
    /** @var Workflow */
    protected $attachedWorkflow;

    public function attachWorkflow( Workflow $workflow )
    {
        $this->attachedWorkflow = $workflow;
    }

    public function __call( $method, $args )
    {
        $this->attachedWorkflow->$method( ...$args );
    }
}
