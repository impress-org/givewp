<?php

namespace Give\Tests\Feature\FormMigration;

use Give\FormMigration\FormMetaDecorator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\FormMetaDecorator
 */
class TestFormMetaDecorator extends TestCase {
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testIsLastNameRequiredShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'required',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isLastNameRequired());

        give_update_option( 'last_name_field_required', 'required' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'global',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isLastNameRequired());
    }

    /**
     * @unreleased
     */
    public function testIsLastNameRequiredShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => '',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isLastNameRequired());

        give_update_option( 'last_name_field_required', 'optional' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'global',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isLastNameRequired());
    }

    /**
     * @unreleased
     */
    public function testIsNameTitlePrefixEnabledShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'required',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isNameTitlePrefixEnabled());

        give_update_option( 'name_title_prefix', 'required' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'optional',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isNameTitlePrefixEnabled());
    }

    /**
     * @unreleased
     */
    public function testIsNameTitlePrefixEnabledShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'disabled',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isNameTitlePrefixEnabled());

        give_update_option( 'name_title_prefix', 'optional' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_name_title_prefix' => 'disabled',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isNameTitlePrefixEnabled());
    }

    /**
     * @unreleased
     */
    public function testHasFundsShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->hasFunds());
    }

    /**
     * @unreleased
     */
    public function testHasFundOptionsShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->hasFundOptions());
    }

    /**
     * @unreleased
     */
    public function testHasFundsShouldReturnTrue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @unreleased
     */
    public function testHasFundOptionsShouldReturnTrue(): void
    {
        $this->markTestIncomplete();
    }
}
