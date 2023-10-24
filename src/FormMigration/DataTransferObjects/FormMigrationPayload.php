<?php

namespace Give\FormMigration\DataTransferObjects;

use Give\DonationForms\Models\DonationForm as DonationFormV3;
use Give\DonationForms\V2\Models\DonationForm as DonationFormV2;
use Give\DonationForms\ValueObjects\DonationFormStatus;

class FormMigrationPayload
{
    /** @var DonationFormV2 */
    public $formV2;

    /** @var DonationFormV3 */
    public $formV3;

    public function __construct(DonationFormV2 $formV2, DonationFormV3 $formV3)
    {
        $this->formV2 = $formV2;
        $this->formV3 = $formV3;
    }

    public static function fromFormV2(DonationFormV2 $formV2): self
    {
        return new self($formV2, DonationFormV3::factory()->create([
            'status' => DonationFormStatus::DRAFT(),
        ]));
    }
}
