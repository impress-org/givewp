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

        $alreadyOptionalField = Text::make('not_required_field')
            ->rules('optional');

        $excludeIfField = Text::make('exclude_if_field')
            ->rules('excludeIf:required_field,foo');

        $excludeUnlessField = Text::make('excludeUnless_field')
            ->rules('excludeUnless:required_field,foo');

        $excludeUnlessFieldWithAlreadyOptional = Text::make('excludeUnless_field')
            ->rules('excludeUnless:required_field,foo', 'optional');

        $action = new UpdateValidationRulesWithOptionalAsDefault();

        $requiredFieldRules = $action($requiredField->getValidationRules());
        $this->assertFalse($requiredFieldRules->hasRule('optional'));
        $this->assertCount(1, $requiredFieldRules->getRules());

        $notRequiredFieldRules = $action($notRequiredField->getValidationRules());
        $this->assertTrue($notRequiredFieldRules->hasRule('optional'));
        $this->assertCount(2, $notRequiredFieldRules->getRules());

        $alreadyOptionalFieldRules = $action($alreadyOptionalField->getValidationRules());
        $this->assertTrue($alreadyOptionalFieldRules->hasRule('optional'));
        $this->assertCount(1, $alreadyOptionalFieldRules->getRules());

        $excludeUnlessFieldRules = $action($excludeUnlessField->getValidationRules())->getRules();
        $this->assertInstanceOf(ExcludeUnless::class, $excludeUnlessFieldRules[0]);
        $this->assertEquals($excludeUnlessFieldRules[0], ExcludeUnless::fromString('required_field,foo'));
        $this->assertInstanceOf(Optional::class, $excludeUnlessFieldRules[1]);
        $this->assertCount(2, $excludeUnlessFieldRules);

        $excludeIfFieldRules = $action($excludeIfField->getValidationRules())->getRules();
        $this->assertInstanceOf(ExcludeIf::class, $excludeIfFieldRules[0]);
        $this->assertEquals($excludeIfFieldRules[0], ExcludeIf::fromString('required_field,foo'));
        $this->assertInstanceOf(Optional::class, $excludeIfFieldRules[1]);
        $this->assertCount(2, $excludeIfFieldRules);

        $excludeUnlessFieldWithAlreadyOptionalRules = $action(
            $excludeUnlessFieldWithAlreadyOptional->getValidationRules()
        )->getRules();
        $this->assertInstanceOf(ExcludeUnless::class, $excludeUnlessFieldWithAlreadyOptionalRules[0]);
        $this->assertEquals($excludeUnlessFieldRules[0], ExcludeUnless::fromString('required_field,foo'));
        $this->assertInstanceOf(Optional::class, $excludeUnlessFieldWithAlreadyOptionalRules[1]);
        $this->assertCount(2, $excludeUnlessFieldWithAlreadyOptionalRules);
    }
}
