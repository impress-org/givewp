<?php

namespace Give\API\REST\V3\Support\Schema;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 4.13.0
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
     * @since 4.13.0
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @since 4.13.0
     */
    public function readonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    /**
     * @since 4.13.0
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @since 4.13.0
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @since 4.13.0
     */
    public function properties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @since 4.13.0
     */
    public function additional(array $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * @since 4.13.0
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @since 4.13.0
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
