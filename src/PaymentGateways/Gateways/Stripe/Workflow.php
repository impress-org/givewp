<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use ReflectionMethod;

/**
 * Encapsulate sequential processes with a shared context.
 */
class Workflow
{
    protected $container = [];

    public function __construct()
    {
        $this->bind( $this );
    }

    public function bind( $concrete ) {
        $this->container[ get_class( $concrete ) ] = $concrete;
        return $this;
    }

    public function resolve( $abstract ) {
        if( ! isset( $this->container[ $abstract ] ) ) {
            throw new \Exception( "Abstract $abstract not found." );
        }
        return $this->container[ $abstract ];
    }

    public function action( callable $action )
    {
        if( is_a( $action, WorkflowAction::class ) ) {
            $action->attachWorkflow( $this );
        }

        $reflection = new ReflectionMethod($action, '__invoke');
        $action( ...array_map( function( $parameter ) {
            return $this->resolve( $parameter->getType()->getName() );
        }, $reflection->getParameters() ) );

        return $this;
    }
}
