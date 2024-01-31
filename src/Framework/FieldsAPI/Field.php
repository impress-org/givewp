<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;
use Give\Vendors\StellarWP\Validation\Concerns\HasValidationRules;

/**
 * @since 2.27.3 add ShowInAdmin, ShowInReceipt, StoreAsMeta
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
    use Concerns\HasPersistence;
    use Concerns\IsReadOnly;
    use Concerns\IsRequired;
    use Concerns\Macroable;
    use Concerns\SerializeAsJson;
    use Concerns\TapNode;
    use Concerns\ShowInAdmin;
    use Concerns\ShowInReceipt;
    use Concerns\HasVisibilityConditions {
        Concerns\HasVisibilityConditions::__construct as private __visibilityConditionsConstruct;
    }
    use HasValidationRules {
        HasValidationRules::__construct as private __validationRulesConstruct;
    }

    /**
     * @since 2.32.0 sets the default scope to donation
     * @since 2.23.1 make constructor final to avoid unsafe usage of `new static()`.
     * @since 2.12.0
     *
     * @throws EmptyNameException
     */
    final public function __construct(string $name)
    {
        if (!$name) {
            throw new EmptyNameException();
        }

        $this->name = $name;
        $this->scope = PersistenceScope::donation();
        $this->__validationRulesConstruct();
        $this->__visibilityConditionsConstruct();
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
