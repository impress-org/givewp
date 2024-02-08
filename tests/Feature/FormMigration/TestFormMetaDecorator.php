<?php

namespace Give\Tests\Feature\FormMigration;

use Give\FormMigration\FormMetaDecorator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @since 3.4.0
 *
 * @covers \Give\FormMigration\FormMetaDecorator
 */
class TestFormMetaDecorator extends TestCase {
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.4.0
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
     * @since 3.4.0
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
     * @since 3.4.0
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
     * @since 3.4.0
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
     * @since 3.4.0
     */
    public function testHasFundsShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->hasFunds());
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundOptionsShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->hasFundOptions());
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundsShouldReturnTrue(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @since 3.4.0
     */
    public function testHasFundOptionsShouldReturnTrue(): void
    {
        $this->markTestIncomplete();
    }
}
