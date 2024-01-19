<?php

namespace Give\Framework\Blocks;

use Exception;
use Give\Framework\Blocks\Contracts\BlockTypeInterface;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Contracts\Arrayable;

use const FILTER_VALIDATE_BOOL;

/**
 * @unreleased
 */
abstract class BlockType implements BlockTypeInterface, Arrayable {

    /**
     * @var string[]
     */
    protected $properties = [];

    /**
     * @var BlockModel
     */
    public $block;

    /**
     * @throws Exception
     */
    public function __construct(BlockModel $block)
    {
        $this->block = $block;

        if ($this->block->name !== $this->getName()) {
            throw new \RuntimeException(sprintf(
                'BlockModel name "%s" does not match the BlockType name "%s".',
                $this->block->name,
                $this->getName()
            ));
        }
    }

     /**
     * Dynamically retrieve attributes.
     *
     * @unreleased
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        $value = $this->getAttribute($key);

        return $this->castAttributeType($key, $value);
    }

     /**
     * Dynamically set attributes.
     *
     * @unreleased
      *
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

     /**
     * Determine if an attribute exists.
     *
     * @unreleased
     *
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->properties[$key]);
    }

      /**
     * @unreleased
     */
    protected function getAttribute($name)
    {
        return $this->block->getAttribute($name);
    }

    /**
     * @unreleased
     */
    protected function hasAttribute($name): bool
    {
        return $this->block->hasAttribute($name);
    }

    /**
     * @unreleased
     */
    protected function setAttribute(string $name, $value): self
    {
        $this->validateAttributeType($name, $value);

        $this->block->setAttribute($name, $value);

        return $this;
    }

    /**
     * Validates that the given value is a valid type for the given attribute.
     *
     * @unreleased
     *
     * @throws InvalidArgumentException
     */
    protected function validateAttributeType(string $key, $value): void
    {
        if ( ! $this->isAttributeTypeValid($key, $value)) {
            $type = $this->getAttributeType($key);

            throw new InvalidArgumentException("Invalid attribute assignment. '$key' should be of type: '$type'");
        }
    }

    /**
     * Validate an attribute to a PHP type.
     *
     * @unreleased
     */
    public function isAttributeTypeValid(string $key, $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        $type = $this->getAttributeType($key);

        switch ($type) {
            case 'int':
                return is_int($value);
            case 'string':
                return is_string($value);
            case 'bool':
                return is_bool($value);
            case 'array':
                return is_array($value);
            default:
                return $value instanceof $type;
        }
    }

    /**
     * @unreleased
     */
    public function castAttributeType(string $key, $value)
    {
        if (is_null($value)) {
            return null;
        }

        $type = $this->getAttributeType($key);

        switch ($type) {
            case 'int':
                return (int)($value);
            case 'string':
                return (string)($value);
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'array':
                return (array)($value);
            default:
                return $value;
        }
    }

     /**
     * @unreleased
     */
    protected function getAttributeType(string $key): string
    {
        $type = is_array($this->properties[$key]) ? $this->properties[$key][0] : $this->properties[$key];

        return strtolower(trim($type));
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return $this->block->toArray();
    }
}
