<?php

namespace Give\Framework\Models;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Contracts\Arrayable;
use RuntimeException;

/**
 * @since 2.19.6
 */
abstract class Model implements Arrayable
{

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
     * The model relationships assigned to their relationship types
     *
     * @var array
     */
    protected $relationships = [];

    /**
     * Relationships that have already been loaded and don't need to be loaded again.
     *
     * @var Model[]
     */
    private $cachedRelations = [];

    /**
     * Create a new model instance.
     *
     * @since 2.20.0 add support for property defaults
     * @since 2.19.6
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->fill(array_merge($this->getPropertyDefaults(), $attributes));

        $this->syncOriginal();
    }

    /**
     * Sync the original attributes with the current.
     *
     * @since 2.19.6
     *
     * @return $this
     */
    protected function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Get the model's original attribute values.
     *
     * @since 2.19.6
     *
     * @param string|null $key
     *
     * @return mixed|array
     */
    public function getOriginal($key = null)
    {
        return $key ? $this->original[$key] : $this->original;
    }

    /**
     * Determine if a given attribute is dirty.
     *
     * @since 2.19.6
     *
     * @param string|null $attribute
     *
     * @return bool
     */
    public function isDirty($attribute = null)
    {
        if (!$attribute) {
            return (bool)$this->getDirty();
        }

        return array_key_exists($attribute, $this->getDirty());
    }

    /**
     * Determine if a given attribute is clean.
     *
     * @since 2.19.6
     *
     * @param string|null $attribute
     *
     * @return bool
     */
    public function isClean($attribute = null)
    {
        return !$this->isDirty($attribute);
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @since 2.19.6
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
     * @since 2.19.6
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Get an attribute from the model.
     *
     * @since 2.19.6
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function getAttribute($key)
    {
        if (!array_key_exists($key, $this->properties)) {
            throw new InvalidArgumentException("$key is not a valid property.");
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Set a given attribute on the model.
     *
     * @since 2.19.6
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        $this->validatePropertyType($key, $value);

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Validate an attribute to a PHP type.
     *
     * @since 2.19.6
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function isPropertyTypeValid($key, $value)
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
            default:
                return $value instanceof $type;
        }
    }

    /**
     * Validate property type
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function validatePropertyType($key, $value)
    {
        if (!$this->isPropertyTypeValid($key, $value)) {
            $type = $this->getPropertyType($key);

            throw new InvalidArgumentException("Invalid attribute assignment. '$key' should be of type: '$type'");
        }
    }

    /**
     * Get the property type
     *
     * @since 2.19.6
     *
     * @param $key
     *
     * @return string
     */
    protected function getPropertyType($key)
    {
        $type = is_array($this->properties[$key]) ? $this->properties[$key][0] : $this->properties[$key];

        return strtolower(trim($type));
    }

    /**
     * Get the default for a property if one is provided, otherwise default to null
     *
     * @since 2.20.0
     *
     * @param $key
     *
     * @return mixed|null
     */
    protected function getPropertyDefault($key)
    {
        return is_array($this->properties[$key]) && isset($this->properties[$key][1])
            ? $this->properties[$key][1]
            : null;
    }

    /**
     * Returns the defaults for all the properties. If a default is omitted it defaults to null.
     *
     * @since 2.20.0
     *
     * @return array
     */
    protected function getPropertyDefaults()
    {
        $defaults = [];
        foreach (array_keys($this->properties) as $property) {
            $defaults[$property] = $this->getPropertyDefault($property);
        }

        return $defaults;
    }

    /**
     * @since 2.19.6
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @since 2.19.6
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return int[]|string[]
     */
    public static function propertyKeys()
    {
        return array_keys((new static)->properties);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @since 2.19.6
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->relationships)) {
            return $this->getRelationship($key);
        }

        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @since 2.19.6
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @since 2.19.6
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @since 2.20.0 cache the relations after first load
     * @since 2.19.6
     *
     * @param $key
     *
     * @return Model|Model[]
     *
     * @throws InvalidArgumentException
     */
    protected function getRelationship($key)
    {
        if (!is_callable([$this, $key])) {
            throw new InvalidArgumentException("$key() does not exist.");
        }

        if ($this->hasCachedRelationship($key)) {
            return $this->cachedRelations[$key];
        }

        $relationship = new Relationship($this->relationships[$key]);

        switch (true) {
            case ($relationship->equals(Relationship::BELONGS_TO())):
            case ($relationship->equals(Relationship::HAS_ONE())):
                return $this->cachedRelations[$key] = $this->$key()->get();
            case ($relationship->equals(Relationship::HAS_MANY())):
            case ($relationship->equals(Relationship::BELONGS_TO_MANY())):
            case ($relationship->equals(Relationship::MANY_TO_MANY())):
                return $this->cachedRelations[$key] = $this->$key()->getAll();
        }

        return null;
    }

    /**
     * Checks whether a relationship has already been loaded.
     *
     * @since 2.20.0
     */
    protected function hasCachedRelationship(string $key): bool
    {
        return array_key_exists($key, $this->cachedRelations);
    }
}
