<?php

namespace Give\Tests\Feature\FormMigration\Steps;

use Give\FormMigration\Steps\FormMeta;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\FormMeta
 */
class TestFormMeta extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testFormMetaProcess(): void
    {
        // Arrange
        $meta = [
            '_boolean_legacy_meta' => true,
            '_string_legacy_meta' => 'string',
            '_array_legacy_meta' => ['key' => 'value'],
        ];
        $v2Form = $this->createSimpleDonationForm(['meta' => $meta]);

        // Act
        $v3Form = $this->migrateForm($v2Form, FormMeta::class);

        // Assert
        $this->assertTrue((bool) give_get_meta($v3Form->id, '_boolean_legacy_meta', true));
        $this->assertSame('string', give_get_meta($v3Form->id, '_string_legacy_meta', true));
        $this->assertSame(['key' => 'value'], give_get_meta($v3Form->id, '_array_legacy_meta', true));
    }
}
