<?php

namespace Give\NextGen\Framework\Blocks;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 * A structured model for a Gutenberg block.
 * Similar to WP_Block_Parser_Block, but without innerHTML or innerContent.
 * ... and without the HTML comments as structure.
 * ... but now with a Collection for innerBlocks :)
 */
class BlockModel implements Arrayable
{
    /** @var string */
    public $name;

    /** @var string */
    public $clientId;

    /** @var bool */
    public $isValid;

    /** @var array */
    public $attributes;

    /** @var BlockCollection */
    public $innerBlocks;

    /**
     * @param  string  $name
     * @param  string|null  $clientId
     * @param  bool  $isValid
     * @param  array  $attributes
     * @param  BlockCollection|null  $innerBlocks
     */
    public function __construct(
        string $name,
        $clientId,
        bool $isValid = true,
        array $attributes = [],
        $innerBlocks = null
    ) {
        $this->name = $name;
        $this->clientId = $clientId ?? $name;
        $this->isValid = $isValid;
        $this->attributes = $attributes;
        $this->innerBlocks = $innerBlocks;
    }

    /**
     * @unreleased
     */
    public function hasAttribute($name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @unreleased
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    /**
     * Returns the unqualified, or short name, of the block without the namespace.
     *
     * @unreleased
     */
    public function getShortName(): string
    {
        return substr($this->name, strpos($this->name, '/') + 1);
    }

    /**
     * @unreleased
     *
     * @param  array  $blockData
     * @return BlockModel
     */
    public static function make( array $blockData ): BlockModel
    {
        $innerBlocks = !empty($blockData['innerBlocks']) ? new BlockCollection(
            array_map([__CLASS__, 'make'],
                $blockData['innerBlocks'])
        ) : null;

        return new BlockModel(
            $blockData['name'],
            !empty($blockData['clientId']) ? $blockData['clientId'] : $blockData['name'],
            !empty($blockData['isValid']) ? $blockData['isValid'] : true,
            !empty($blockData['attributes']) ? $blockData['attributes'] : [],
            $innerBlocks
        );
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'clientId' => $this->clientId,
            'isValid' => $this->isValid,
            'attributes' => $this->attributes,
            'innerBlocks' => $this->innerBlocks ? $this->innerBlocks->toArray() : []
        ];
    }
}
