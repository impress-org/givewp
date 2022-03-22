<?php

namespace Give\Framework\Support\TypedProperties;

use Give\Framework\Support\TypedProperties\Exceptions\InvalidPropertyType;
use Give\Framework\Support\TypedProperties\Exceptions\PropertyNotInitialized;
use Give\Framework\Support\TypedProperties\Exceptions\PropertyReadOnly;
use Give\Framework\Support\TypedProperties\Exceptions\PropertyWriteOnly;

class TypedProperty
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var bool
     */
    protected $isReadOnly = false;

    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * @unreleased
     *
     * @param string $name
     * @param string $type
     * @param mixed  $default
     * @param bool   $applyDefault
     * @param bool   $readOnly
     *
     * @throws PropertyReadOnly
     */
    public function __construct($name, $type, $default, $applyDefault, $readOnly, $class)
    {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->isReadOnly = $readOnly;
        $this->class = $class;

        if ($applyDefault) {
            $this->setValue($default);
        }
    }

    /**
     * @unreleased
     *
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->isInitialized) {
            throw PropertyNotInitialized::fromTypedProperty($this);
        }

        return $this->value;
    }

    /**
     * @unreleased
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value)
    {
        if (!$this->acceptsValue($value)) {
            throw new InvalidPropertyType($this->name, $this->type);
        }

        if (!$this->isWriteable()) {
            throw new PropertyReadOnly($this->name, $this->class);
        }

        $this->isInitialized = true;
        $this->value = $value;
    }

    /**
     * Returns whether the property is writable. This is based on whether the property is read-only and the property
     * value hasn't been written to, yet.
     *
     * @unreleased
     *
     * @return bool
     */
    public function isWriteable()
    {
        return !$this->isReadOnly || !$this->isInitialized;
    }

    /**
     * Checks whether a given value is of the correct type.
     *
     * @unreleased
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function acceptsValue($value)
    {
        if ($this->type === 'mixed') {
            return true;
        }

        return $this->type === gettype($value);
    }

    /**
     * Mimics the isset() behavior for a property. Here's the definition from the PHP docs:
     * "Determine if a variable is considered set, this means if a variable is declared and is different than null."
     *
     * @return bool
     */
    public function hasBeenSet()
    {
        return $this->isInitialized && $this->value !== null;
    }
}
