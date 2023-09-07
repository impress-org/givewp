<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\FieldsAPI\Actions;

use Give\Framework\FieldsAPI\Actions\UpdateValidationRulesWithOptionalAsDefault;
use Give\Framework\FieldsAPI\Text;
use Give\Tests\TestCase;
use Give\Vendors\StellarWP\Validation\Rules\ExcludeIf;
use Give\Vendors\StellarWP\Validation\Rules\ExcludeUnless;
use Give\Vendors\StellarWP\Validation\Rules\Optional;

/**
 * @covers UpdateValidationRulesWithOptionalAsDefault
 */
class UpdateValidationRulesWithOptionalAsDefaultTest extends TestCase
{
    /**
     * @since 3.0.0
     */
    public function testShouldUpdateValidationRulesWithOptionalAsDefault()
    {
        $requiredField = Text::make('required_field')
            ->rules('required');

        $notRequiredField = Text::make('not_required_field')
            ->rules('max:255');

        $excludeIfField = Text::make('exclude_if_field')
            ->rules('excludeIf:required_field,foo');

        $excludeUnlessField = Text::make('excludeUnless_field')
            ->rules('excludeUnless:required_field,foo');

        $action = new UpdateValidationRulesWithOptionalAsDefault();

        $this->assertFalse($action($requiredField->getValidationRules())->hasRule('optional'));
        $this->assertTrue($action($notRequiredField->getValidationRules())->hasRule('optional'));

        $excludeUnlessFieldRules = $action($excludeUnlessField->getValidationRules())->getRules();
        $this->assertInstanceOf(ExcludeUnless::class, $excludeUnlessFieldRules[0]);
        $this->assertInstanceOf(Optional::class, $excludeUnlessFieldRules[1]);

        $excludeIfFieldRules = $action($excludeIfField->getValidationRules())->getRules();
        $this->assertInstanceOf(ExcludeIf::class, $excludeIfFieldRules[0]);
        $this->assertInstanceOf(Optional::class, $excludeIfFieldRules[1]);
    }
}
