<?php
namespace Give\Tests\Unit\FormMigration\TestTraits;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\Models\DonationForm as V2DonationForm;
use Give\FormMigration\Contracts\FormMigrationStep;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\StepProcessor;

/**
 * @since 3.16.0
 */
trait FormMigrationProcessor
{
    /**
     * @since 3.16.0
     */
    public function migrateForm(V2DonationForm $v2Form, string $stepClassname): DonationForm
    {
        $payload = FormMigrationPayload::fromFormV2($v2Form);
        $processor = new StepProcessor($payload);
        /** @var FormMigrationStep $step */
        $step = new $stepClassname($payload);
        $processor($step);
        $payload->formV3->save();

        return $payload->formV3;
    }
}
