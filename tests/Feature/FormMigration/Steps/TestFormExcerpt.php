<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormExcerpt;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\FormExcerpt
 */
class TestFormExcerpt extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testFormExcerptProcess(): void
    {
        // Arrange
        $form = [
            'post_excerpt' => 'This is a test excerpt',
        ];
        $v2Form = $this->createSimpleDonationForm(['form' => $form]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormExcerpt::class);

        // Assert
        $this->assertSame($form['post_excerpt'], $v3Form->settings->formExcerpt);
    }
}
