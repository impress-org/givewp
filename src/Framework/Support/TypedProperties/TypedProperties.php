<?php

namespace Give\Framework\Support\TypedProperties;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\TypedProperties\Exceptions\InvalidPropertyType;
use Give\Framework\Support\TypedProperties\Exceptions\PropertyNotInitialized;
use Give\Framework\Support\TypedProperties\Exceptions\PropertyReadOnly;
use Give\Framework\Support\TypedProperties\Exceptions\PropertyWriteOnly;

trait TypedProperties
{
    /**
     * Values may be set as
     *      "key" => "type"
     *      "key:readonly"
     *      "key" => ["type", "default"]
     *      "key" => ["type:readonly", "default"]
     *
     * @var TypedProperty[]|array The properties that are allowed to be set.
     */
    protected $properties = [];

    /**
     * @var bool Whether the properties have been initialized.
     */
    private $propertiesInitialized = false;

    /**
     * Gets the value of a property after property checks.
     *
     * @unreleased
     *
     * @param $name
     *
     * @return mixed
     * @throws PropertyNotInitialized
     */
    public function __get($name)
    {
        $this->initializeProperties();

        $property = $this->getProperty($name);

        // Use a getter if one exists.
        if (method_exists($this, 'get' . ucfirst($name))) {
            return $this->{'get' . ucfirst($name)}($property);
        }

        return $property->getValue();
    }

    /**
     * Sets the value of a property after property checks.
     *
     * @param $name
     * @param $value
     *
     * @return void
     * @throws PropertyReadOnly
     */
    public function __set($name, $value)
    {
        $this->initializeProperties();

        $property = $this->getProperty($name);

        // Use a setter if one exists.
        if (method_exists($this, 'set' . ucfirst($name))) {
            if (!$property->acceptsValue($value)) {
                throw InvalidPropertyType::fromTypedProperty($property);
            }
            $this->{'set' . ucfirst($name)}($value, $property);
        } else {
            $property->setValue($value);
        }
    }

    /**
     * Checks whether the property has been set or not.
     *
     * @unreleased
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $this->initializeProperties();

        return $this->getProperty($name)->hasBeenSet();
    }

    /**
     * Provide a callable to loop through all the property values.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function mapProperties($callback)
    {
        foreach ($this->properties as $name => $property) {
            $callback($property->getValue(), $name);
        }
    }

    /**
     * @unreleased
     *
     * @param $name
     *
     * @return TypedProperty
     */
    protected function getProperty($name)
    {
        if (!isset($this->properties[$name])) {
            throw new InvalidArgumentException("Property '$name' is not valid.");
        }

        return $this->properties[$name];
    }

    /**
     * Initializes the object properties by taking the shorthand notation and turning each property into a TypedProperty
     * to be used when accessing the object.
     *
     * @unreleased
     *
     * @return void
     */
    private function initializeProperties()
    {
        if ($this->propertiesInitialized) {
            return;
        }

        $properties = [];
        foreach ($this->properties as $name => $details) {
            list($name, $accessRule) = array_pad(explode(':', $name), 2, null);
            list($type, $default) = is_array($details) ? $details : [$details, null];

            if ($accessRule !== null && $accessRule !== 'readonly') {
                throw new InvalidArgumentException("Invalid access rule '$accessRule' for property '$name'.");
            }

            if ($default !== null && gettype($default) !== $type) {
                throw new InvalidArgumentException("Default value for property '$name' is not of type '$type'.");
            }

            $hasDefault = is_array($details) && isset($details[1]);
            $properties[$name] = new TypedProperty(
                $name,
                $type,
                $default,
                $hasDefault,
                $accessRule === 'readonly',
                static::class
            );
        }

        $this->properties = $properties;
        $this->propertiesInitialized = true;
    }
}
