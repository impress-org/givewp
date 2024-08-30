<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormTitle;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @since 3.16.0
 *
 * @covers \Give\FormMigration\Steps\FormTitle
 */
class TestFormTitle extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @since 3.16.0
     */
    public function testFormTitleProcess(): void
    {
        // Arrange
        $form = [
            'post_title' => 'Form Title',
        ];
        $v2Form = $this->createSimpleDonationForm(['form' => $form]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormTitle::class);

        // Assert
        $this->assertSame($form['post_title'], $v3Form->title);
        $this->assertSame($form['post_title'], $v3Form->settings->formTitle);
    }
}
