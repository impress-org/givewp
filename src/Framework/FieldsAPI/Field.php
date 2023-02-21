<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Vendors\StellarWP\Validation\Concerns\HasValidationRules;

/**
 * @since      2.17.0 allow fields to be macroable
 * @since      2.12.0
 * @since      2.13.0 Support visibility conditions
 * @since 2.22.0 Add TapNode trait
 */
abstract class Field implements Node
{
    use Concerns\HasDefaultValue;
    use Concerns\HasName;
    use Concerns\HasType;
    use Concerns\HasVisibilityConditions;
    use Concerns\IsReadOnly;
    use Concerns\IsRequired;
    use Concerns\Macroable;
    use Concerns\SerializeAsJson;
    use Concerns\TapNode;
    use HasValidationRules {
        HasValidationRules::__construct as private __validationRulesConstruct;
    }

    /**
     * @since      2.12.0
     * @since 2.23.1 Make constructor final to avoid unsafe usage of `new static()`.
     *
     * @throws EmptyNameException
     */
    final public function __construct(string $name)
    {
        if (!$name) {
            throw new EmptyNameException();
        }

        $this->name = $name;
        $this->__validationRulesConstruct();
    }

    /**
     * @inheritDoc
     */
    public function getNodeType(): string
    {
        return 'field';
    }

    /**
     * Create a named field.
     *
     * @since 2.12.0
     *
     * @return static
     * @throws EmptyNameException
     */
    public static function make(string $name): self
    {
        if (!$name) {
            throw new EmptyNameException();
        }

        return new static($name);
    }
}
