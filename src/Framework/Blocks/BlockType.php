<?php

namespace Give\Framework\Blocks;

use Exception;
use Give\Framework\Blocks\Contracts\BlockTypeInterface;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Contracts\Arrayable;
use RuntimeException;

/**
 * @unreleased
 */
abstract class BlockType implements BlockTypeInterface, Arrayable
{
    /**
     * @var string[]
     */
    protected $properties = [];

    /**
     * @var BlockModel
     */
    protected $block;

    /**
     * @unreleased
     */
    abstract public static function name(): string;

    /**
     * @throws Exception
     */
    public function __construct(BlockModel $block)
    {
        $this->block = $block;

        if ($this->block->name !== $this::name()) {
            throw new RuntimeException(
                sprintf(
                    'BlockModel name "%s" does not match the BlockType name "%s".',
                    $this->block->name,
                    $this::name()
                )
            );
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
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->validateAttributeType($key, $value);

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
        return !is_null($this->getAttribute($key));
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
        if (!$this->isAttributeTypeValid($key, $value)) {
            $type = $this->getPropertyType($key);

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

        $type = $this->getPropertyType($key);

        switch ($type) {
            case 'int':
                return is_int($value);
            case 'string':
                return is_string($value);
            case 'bool':
                return is_bool($value);
            case 'array':
                return is_array($value);
            case 'float':
                return is_float($value);
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

        $type = $this->getPropertyType($key);

        switch ($type) {
            case 'int':
                return (int)($value);
            case 'string':
                return (string)($value);
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'array':
                return (array)($value);
            case 'float':
                return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
            default:
                return $value;
        }
    }

    /**
     * @unreleased
     */
    protected function getPropertyType(string $key): string
    {
        $type = is_array($this->properties[$key]) ? $this->properties[$key][0] : $this->properties[$key];

        return strtolower(trim($type));
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        $attributes = [];

        foreach($this->properties as $key => $type) {
            $attributes[$key] = $this->{$key};
        }

        return [
            'name' => $this::name(),
            'attributes' => $attributes
        ];
    }
}
