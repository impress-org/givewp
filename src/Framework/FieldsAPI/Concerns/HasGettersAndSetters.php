<?php

namespace Give\Framework\FieldsAPI\Concerns;

use BadMethodCallException;
use ReflectionException;
use ReflectionProperty;

/**
 * Trait HasGettersAndSetters
 */
trait HasGettersAndSetters
{
    private static $cache = [];

    /**
     * Handle dynamic method calls to the object.
     *
     * @unreleased
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     * @throws ReflectionException
     */
    public function __call($name, $arguments = null)
    {
        $action = 'set';
        $property = $name;

        if (strpos($name, 'get') === 0) {
            $action = 'get';
            $property = lcfirst(substr($name, 3));
        }

        if (!property_exists($this, $property)) {
            throw new BadMethodCallException(sprintf(__('Property %s does not exist', 'givewp'), $property));
        }

        $propertyAttributes = self::parseProperty($property);

        if (!$propertyAttributes[$action]) {
            throw new BadMethodCallException(sprintf(__('No permissions to %s the property %s', 'givewp'), $action, $property));
        }

        if ($action === 'get') {
            return $this->$property;
        }

        if (empty($arguments)) {
            throw new BadMethodCallException(sprintf(__('No argument provided for %s', 'givewp'), $name));
        }

        if (gettype($arguments[0]) !== $propertyAttributes['type']) {
            throw new BadMethodCallException(sprintf(__('Argument provided for %s is not of type %s', 'givewp'), $name, $propertyAttributes['type']));
        }

        $this->$name = $arguments[0];

        return $this;
    }

    /**
     * Parse property docblock and extract getters and setters.
     *
     * @unreleased
     * @throws ReflectionException
     */
    private static function parseProperty($propertyName): array
    {
        if (isset(self::$cache[$propertyName])) {
            return self::$cache[$propertyName];
        }

        $propertyAttributes = [];

        $property = new ReflectionProperty(static::class, $propertyName);
        $propertyDocComment = $property->getDocComment();
        $propertyDocComment = explode("\n", $propertyDocComment);

        /** @var array $propertyDocComment */
        $propertyDocComment = array_filter($propertyDocComment, function ($line) {
            return strpos($line, '@') !== false;
        });

        foreach ($propertyDocComment as $attribute) {
            $attribute = trim(str_replace('*', '', $attribute));

            if ($attribute === '@getter') {
                $propertyAttributes['get'] = true;
            } elseif ($attribute === '@setter') {
                $propertyAttributes['set'] = true;
            } elseif (strpos($attribute, '@type') === 0) {
                $propertyAttributes['type'] = trim(str_replace('@type', '', $attribute));
            }
        }

        self::$cache[$propertyName] = $propertyAttributes;

        return $propertyAttributes;
    }
}
