<?php

namespace Give\Form\LegacyConsumer\Actions;

use ArrayObject;
use Give\Framework\FieldsAPI\Conditions\BasicCondition;
use Give\Framework\FieldsAPI\Conditions\Condition;
use Give\Framework\FieldsAPI\Field;
use Give\ValueObjects\Money;

/**
 * @unreleased
 */
class DetermineVisibilityForRequest
{
    /** @var bool */
    const IS_VISIBLE = true;

    /** @var Field */
    private $field;

    /** @var ArrayObject */
    protected $postData;

    /**
     * @unreleased
     * @param Field $field
     * @param array $postData
     */
    public function __construct(Field $field, array $postData )
    {
        $this->field = $field;
        $this->postData = new ArrayObject( $postData );
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        if( ! $this->fieldHasVisibilityConditions() ) {
            return self::IS_VISIBLE;
        }

        $conditions = $this->field->getVisibilityConditions();
        return array_reduce( $conditions, [$this, 'reduceVisibility'], self::IS_VISIBLE );
    }

    protected function fieldHasVisibilityConditions()
    {
        return method_exists( $this->field, 'hasVisibilityConditions' )
            && $this->field->hasVisibilityConditions();
    }

    protected function reduceVisibility( $visibility, Condition $condition )
    {
        $result = $this->compareConditionWithOperator( $condition );

        return 'and' === $condition->boolean
            ? $visibility && $result
            : $visibility || $result;
    }

    protected function compareConditionWithOperator( Condition $condition )
    {
        if( is_a( $condition, BasicCondition::class ) ) {
            return $this->compareBasicConditionWithOperator( $condition );
        }

        // @TODO Implement nested conditions.
        return self::IS_VISIBLE;
    }

    protected function compareBasicConditionWithOperator( BasicCondition $condition )
    {
        $conditionValue = $condition->value;
        $comparisonValue = $this->postData[ $condition->field ];

        if( 'give-amount' === $condition->field ) {
            $conditionValue = $this->normalizeMinorAmount( $conditionValue );
            $comparisonValue = $this->normalizeMinorAmount( $comparisonValue );
        }

        switch( $condition->operator ) {
            case '=':
                return $comparisonValue === $conditionValue;
            case '!=':
                return $comparisonValue !== $conditionValue;
            case '>':
                return $comparisonValue > $conditionValue;
            case '>=':
                return $comparisonValue >= $conditionValue;
            case '<':
                return $comparisonValue < $conditionValue;
            case '<=':
                return $comparisonValue <= $conditionValue;
            default:
                return false;
        }
    }

    /**
     * @param $amount
     * @return int
     */
    protected function normalizeMinorAmount( $amount )
    {
        $currency = give_get_currency($this->postData[ 'give-form-id' ]);
        $allCurrencyData = give_get_currencies('all');
        $currencyData = $allCurrencyData[ $currency ];

        $amount = str_replace( $currencyData['setting']['thousands_separator'], '', $amount );
        $amount = str_replace( $currencyData['setting']['decimal_separator'], '.', $amount );

        return Money::of( $amount, $currency )->getMinorAmount();
    }
}
