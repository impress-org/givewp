<?php

namespace Give\FormMigration\Concerns\Blocks;

use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;

class BlockDifference
{
    protected $blockLookup;

    protected $skip = [];

    protected $blockAddedCallback;

    protected $blockDifferenceCallback;

    public function __construct(BlockCollection $collection) {
        $collection->walk(function(BlockModel $block) {
            $this->blockLookup[$block->clientId] = $block;
        });
    }

    public function skip(...$blockNames): self
    {
        $this->skip = array_merge($this->skip, $blockNames);
        return $this;
    }

    public function onBlockAdded(callable $callback): self
    {
        $this->blockAddedCallback = $callback;
        return $this;
    }

    public function onBlockDifference(callable $callback): self
    {
        $this->blockDifferenceCallback = $callback;
        return $this;
    }

    public function diff(BlockCollection $collection)
    {
        $collection->walk(function(BlockModel $block) {
            if(in_array($block->name, $this->skip)) return;


            if(!isset($this->blockLookup[$block->clientId])) {
                call_user_func($this->blockAddedCallback, $block);
                return;
            }

            $differences = [];
            foreach($block->getAttributes() as $key => $value) {
                $previousValue = $this->blockLookup[$block->clientId]->getAttributes()[$key] ?? null;
                if($previousValue !== $value) {
                    $differences[$key] = [
                        'previous' => $previousValue,
                        'current' => $value,
                    ];
                }
            }

            if($differences) {
                call_user_func($this->blockDifferenceCallback, $block, $differences);
            }
        });
    }
}
