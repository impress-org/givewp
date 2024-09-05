<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormGrid;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.16.0
 *
 * @covers \Give\FormMigration\Steps\FormGrid
 */
class TestFormGrid extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testFormGridProcess(): void
    {
        // Arrange
        $meta = [
            '_give_form_grid_option' => 'custom',
            '_give_form_grid_redirect_url' => 'https://example.com',
            '_give_form_grid_donate_button_text' => 'Donate Now',
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormGrid::class);

        // Assert
        $this->assertTrue($v3Form->settings->formGridCustomize);
        $this->assertEquals('https://example.com', $v3Form->settings->formGridRedirectUrl);
        $this->assertEquals('Donate Now', $v3Form->settings->formGridDonateButtonText);
        $this->assertTrue($v3Form->settings->formGridHideDocumentationLink);

    }
}
