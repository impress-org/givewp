<?php

namespace Give\Framework\Blocks;

use Give\Framework\Support\Contracts\Arrayable;

class BlockCollection implements Arrayable
{
    /**
     * @var BlockModel[]
     */
    protected $blocks;

    /**
     * @since 3.0.0
     *
     * @param  BlockModel[]  $blocks
     */
    public function __construct(array $blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @since 3.0.0
     *
     * @param  BlockModel[]  $blocks
     */
    public static function make($blocks): self
    {
        return new self($blocks);
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     *
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return array_map(static function (BlockModel $block) {
            return $block->toArray();
        }, $this->blocks);
    }

    /**
     * @return BlockModel[]
     * @since 3.0.0
     *
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * @since 3.0.0
     *
     * @return BlockModel|null
     */
    public function findByName(string $blockName, int $blockIndex = 0)
    {
        return $this->findByNameRecursive($blockName, $blockIndex);
    }

    public function findParentByChildName($blockName, int $blockIndex = 0)
    {
        foreach($this->blocks as $block) {
            if($block->innerBlocks->findByName($blockName)) {
                return $block;
            }
        }

        // @todo Throw exception if not found.
    }

    /**
     * @since 3.0.0
     *
     * @return array{0: BlockModel, 1: int}|void
     */
    public function findParentByBlockCollection(BlockCollection $blockCollection)
    {
        foreach ($this->blocks as $index => $block) {
            if ($block->innerBlocks === $blockCollection) {
                return [$block, $index];
            }
        }
        // @todo Throw exception if not found.
    }

    /**
     * @since 3.0.0
     *
     * @return BlockModel|BlockCollection|null
     */
    private function findByNameRecursive(string $blockName, int $blockIndex = 0, string $return = 'self', BlockCollection $blockCollection = null, int &$count = 0)
    {
        if (!$blockCollection) {
            $blockCollection = $this;
        }

        foreach ($blockCollection->blocks as $block) {
            if ($block->name === $blockName) {
                $count++;

                if ($count === $blockIndex + 1) {
                    if ($return === 'self') {
                        return $block;
                    } elseif ($return === 'parent') {
                        return $blockCollection;
                    }
                }
            } elseif ($block->innerBlocks) {
                $result = $this->findByNameRecursive($blockName, $blockIndex, $return, $block->innerBlocks, $count);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * @since 3.0.0
     */
    public function insertBefore(string $blockName, BlockModel $block, int $blockIndex = 0): BlockCollection
    {
        $blockCollection = $this->findByNameRecursive($blockName, $blockIndex, 'parent');

        if (!$blockCollection) {
            return $this;
        }

        $innerBlocks = $blockCollection->blocks;
        $blockIndex = array_keys(
            array_filter(array_column($innerBlocks, 'name'), function ($name) use ($blockName) {
                return $name === $blockName;
            })
        )[$blockIndex];
        array_splice($innerBlocks, $blockIndex, 0, [$block]);
        $blockCollection->blocks = $innerBlocks;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function insertAfter(string $blockName, BlockModel $block, int $blockIndex = 0): BlockCollection
    {
        $blockCollection = $this->findByNameRecursive($blockName, $blockIndex, 'parent');

        if (!$blockCollection) {
            return $this;
        }

        $innerBlocks = $blockCollection->blocks;
        $blockIndex = array_keys(
            array_filter(array_column($innerBlocks, 'name'), function ($name) use ($blockName) {
                return $name === $blockName;
            })
        )[$blockIndex];
        array_splice($innerBlocks, $blockIndex + 1, 0, [$block]);
        $blockCollection->blocks = $innerBlocks;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function prepend(BlockModel $block): BlockCollection
    {
        array_unshift($this->blocks, $block);
        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function append(BlockModel $block): BlockCollection
    {
        $this->blocks[] = $block;
        return $this;
    }

    /**
     * @since 3.15.0 returns the block collection if block does not exist.
     * @since 3.0.0
     */
    public function remove($blockName) {
        $blockCollection = $this->findByNameRecursive($blockName, 0, 'parent');
        $innerBlocks = $blockCollection->blocks;

        if(!$innerBlocks){
            return $this;
        }

        $blockIndex = array_search($blockName, array_column($innerBlocks, 'name'));
        array_splice($innerBlocks, $blockIndex, 1);
        $blockCollection->blocks = $innerBlocks;
        return $this;
    }

    public function walk(callable $callback)
    {
        foreach ($this->blocks as $block) {
            $callback($block);

            if ($block->innerBlocks) {
                $block->innerBlocks->walk($callback);
            }
        }
    }
}
