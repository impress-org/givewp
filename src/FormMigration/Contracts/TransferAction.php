<?php

namespace Give\FormMigration\Contracts;

abstract class TransferAction
{
    protected $sourceId;

    public function __construct($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    public static function from($sourceId)
    {
        return new static($sourceId);
    }

    public function to($destinationId)
    {
        $this->__invoke($destinationId);
    }

    public abstract function __invoke($destinationId);
}
