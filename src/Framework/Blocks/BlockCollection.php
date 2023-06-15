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
     * @return BlockModel[]
     * @since 0.1.0
     *
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * @unreleased
     *
     * @return BlockModel|null
     */
    public function findByName(string $blockName, int $blockIndex = 0)
    {
        return $this->findByNameRecursive($blockName, $blockIndex);
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function insertBefore(string $blockName, BlockModel $block, int $blockIndex = 0): BlockCollection
    {
        $blockCollection = $this->findByNameRecursive($blockName, $blockIndex, 'parent');

        if (!$blockCollection) {
            return $this;
        }

        $innerBlocks = $blockCollection->blocks;
        $blockIndex = array_search($blockName, array_column($innerBlocks, 'name'));
        array_splice($innerBlocks, $blockIndex, 0, [$block]);
        $blockCollection->blocks = $innerBlocks;

        return $this;
    }

    /**
     * @unreleased
     */
    public function insertAfter(string $blockName, BlockModel $block, int $blockIndex = 0): BlockCollection
    {
        $blockCollection = $this->findByNameRecursive($blockName, $blockIndex, 'parent');

        if (!$blockCollection) {
            return $this;
        }

        $innerBlocks = $blockCollection->blocks;
        $blockIndex = array_search($blockName, array_column($innerBlocks, 'name'));
        array_splice($innerBlocks, $blockIndex + 1, 0, [$block]);
        $blockCollection->blocks = $innerBlocks;

        return $this;
    }

    /**
     * @unreleased
     */
    public function prepend(string $blockName, BlockModel $block, int $blockIndex = 0): BlockCollection
    {
        $blockCollection = $this->findByNameRecursive($blockName, $blockIndex);

        if (!$blockCollection) {
            return $this;
        }

        $innerBlocks = $blockCollection->innerBlocks->blocks;
        array_unshift($innerBlocks, $block);
        $blockCollection->blocks = $innerBlocks;

        return $this;
    }

    /**
     * @unreleased
     */
    public function append(string $blockName, BlockModel $block, int $blockIndex = 0): BlockCollection
    {
        $blockCollection = $this->findByNameRecursive($blockName, $blockIndex);

        if (!$blockCollection) {
            return $this;
        }

        $innerBlocks = $blockCollection->innerBlocks->blocks;
        $innerBlocks[] = $block;
        $blockCollection->blocks = $innerBlocks;

        return $this;
    }
}
