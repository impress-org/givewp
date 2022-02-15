<?php

namespace Give\Framework\Models;

use DateTime;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use RuntimeException;

/**
 * @unreleased
 */
abstract class Model
{
    use InteractsWithTime;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model attribute's original state.
     *
     * @var array
     */
    protected $original = [];

    /**
     * The model properties assigned to their types
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Create a new model instance.
     *
     * @unreleased
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Sync the original attributes with the current.
     *
     * @unreleased
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Determine if a given attribute is dirty.
     *
     * @unreleased
     *
     * @param  string  $attribute
     * @return bool
     */
    public function isDirty($attribute)
    {
        return array_key_exists($attribute, $this->getDirty());
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @unreleased
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @unreleased
     *
     * @param  array  $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (!$this->validatePropertyType($key, $value)) {
                $type = $this->getPropertyType($key);

                throw new InvalidArgumentException("Invalid attribute assignment. '$key' should be of type: '$type'");
            }

            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Get an attribute from the model.
     *
     * @unreleased
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function getAttribute($key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            throw new RuntimeException("Attribute '$key' does not exist.");
        }

        return $this->attributes[$key];
    }

    /**
     * Set a given attribute on the model.
     *
     * @unreleased
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Validate an attribute to a PHP type.
     *
     * @unreleased
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function validatePropertyType($key, $value)
    {
        $type = $this->getPropertyType($key);

        switch ($type) {
            case 'int':
                return is_int($value);
            case 'string':
                return is_string($value);
            case 'bool':
                return is_bool($value);
            case DateTime::class:
                return is_date($value);
            default:
                return $value instanceof $type;
        }
    }

    /**
     * Get the property type
     *
     * @unreleased
     *
     * @param $key
     * @return string
     */
    private function getPropertyType($key)
    {
        return strtolower(trim($this->properties[$key]));
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @unreleased
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @unreleased
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        if (!$this->validatePropertyType($key, $value)) {
            $type = $this->getPropertyType($key);

            throw new InvalidArgumentException("Invalid attribute assignment. '$key' should be of type: '$type'");
        }

        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @unreleased
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return !is_null($this->getAttribute($key));
    }
}
