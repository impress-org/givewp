<?php

namespace Give\Framework\Blocks;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 3.0.0
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
    protected $attributes;

    /** @var BlockCollection */
    public $innerBlocks;

    /**
     * @since 3.1.0 added innerBlocks sanitization
     * @since 3.0.0
     * @param string $name
     * @param string $clientId
     * @param bool $isValid
     * @param array $attributes
     * @param BlockCollection|null $innerBlocks
     */
    public function __construct(
        string $name,
        string $clientId = null,
        bool   $isValid = true,
        array  $attributes = [],
               $innerBlocks = null
    ) {
        $this->name = $name;
        $this->clientId = $clientId ?? wp_generate_uuid4();
        $this->isValid = $isValid;
        $this->attributes = $attributes;
        $this->innerBlocks = $this->sanitizeInnerBlocks($innerBlocks);
    }

    /**
     * @since 3.1.0
     *
     * @param  array|BlockCollection|null  $innerBlocks
     */
    public function sanitizeInnerBlocks($innerBlocks): BlockCollection
    {
        if (empty($innerBlocks)) {
            return new BlockCollection([]);
        }

        if (is_a($innerBlocks, BlockCollection::class)) {
            return $innerBlocks;
        }

        return new BlockCollection(
            array_map([__CLASS__, 'make'],
                $innerBlocks)
        );
    }

    /**
     * @since 3.0.0
     */
    public function hasAttribute($name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @since 3.0.0
     */
    public function getAttribute($name)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : null;
    }

    /**
     * @since 3.0.0
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @since 3.0.0
     */
    public function setAttribute(string $name, $value): BlockModel
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Returns the unqualified, or short name, of the block without the namespace.
     *
     * @since 3.0.0
     */
    public function getShortName(): string
    {
        return substr($this->name, strpos($this->name, '/') + 1);
    }

    /**
     * @since 3.1.0 simplified innerBlocks param
     * @since 3.0.0
     *
     * @param  array  $blockData
     * @return BlockModel
     */
    public static function make( array $blockData ): BlockModel
    {
        return new BlockModel(
            $blockData['name'],
            !empty($blockData['clientId']) ? $blockData['clientId'] : wp_generate_uuid4(),
            !empty($blockData['isValid']) ? $blockData['isValid'] : true,
            !empty($blockData['attributes']) ? $blockData['attributes'] : [],
            $blockData['innerBlocks'] ?? []
        );
    }

    /**
     * @since 3.0.0
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
