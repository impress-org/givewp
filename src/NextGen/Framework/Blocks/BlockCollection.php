<?php

namespace Give\NextGen\Framework\Blocks;

use Give\Framework\Support\Contracts\Arrayable;

class BlockCollection implements Arrayable
{
    /**
     * @var BlockModel[]
     */
    protected $blocks;

    /**
     * @since 0.1.0
     *
     * @param  BlockModel[]  $blocks
     */
    public function __construct(array $blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @since 0.1.0
     *
     * @param  BlockModel[]  $blocks
     */
    public static function make($blocks): self
    {
        return new self($blocks);
    }

    /**
     * @since 0.1.0
     */
    public static function fromJson($blocksJson): self
    {
        $blocksJson = json_decode($blocksJson, true, JSON_UNESCAPED_SLASHES);

        $blocks = [];
        foreach ($blocksJson as $block) {
            $blocks[] = BlockModel::make($block);
        }

        return new self($blocks);
    }

    /**
     * @since 0.1.0
     *
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return array_map(static function (BlockModel $block) {
            return $block->toArray();
        }, $this->blocks);
    }

    /**
     * @since 0.1.0
     *
     * @return BlockModel[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
