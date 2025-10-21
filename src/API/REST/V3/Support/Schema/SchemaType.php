<?php

namespace Give\API\REST\V3\Support\Schema;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 */
class SchemaType implements Arrayable
{
    public bool $nullable = false;
    public string $description = '';
    public array $additional = [];
    public array $properties = [];
    public string $type = '';
    public bool $required = false;
    public bool $readonly = false;

    /**
     * @unreleased
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @unreleased
     */
    public function readonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    /**
     * @unreleased
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @unreleased
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @unreleased
     */
    public function properties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @unreleased
     */
    public function additional(array $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * @unreleased
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        $schema = [];
        $schema['type'] = $this->nullable ? [$this->type, 'null'] : $this->type;

        if (!empty($this->properties) && is_array($this->properties)) {
            $schema['properties'] = $this->properties;
        }
        if (!empty($this->description)) {
            $schema['description'] = $this->description;
        }
        if ($this->required === true) {
            $schema['required'] = array_keys($this->properties);
        }

        if ($this->readonly === true) {
            $schema['readonly'] = true;
        }

        return array_merge($schema, $this->additional);
    }
}
