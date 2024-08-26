<?php

namespace Feature\FormMigration\Steps;

use Give\FormMigration\Steps\MigrateMeta;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give\Tests\Unit\FormMigration\TestTraits\FormMigrationProcessor;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Steps\MigrateMeta
 */
class TestMigrateMeta extends TestCase
{
    use FormMigrationProcessor;
    use LegacyDonationFormAdapter;
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testMigrateMetaProcess(): void
    {
        // Arrange
        $v2Form = $this->createSimpleDonationForm();

        // Act
        $v3Form = $this->migrateForm($v2Form, MigrateMeta::class);

        // Assert
       $this->assertSame($v2Form->id, (int) give_get_meta($v3Form->id, 'migratedFormId', true));
    }
}
