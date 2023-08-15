<?php

namespace Give\FormMigration\Contracts;

use Give\DonationForms\Models\DonationForm as DonationFormV3;
use Give\DonationForms\V2\Models\DonationForm as DonationFormV2;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\FormMetaDecorator;
use Give\Framework\Blocks\BlockCollection;

abstract class FormMigrationStep
{
    /** @var FormMetaDecorator */
    public $formV2;

    /** @var DonationFormV3 */
    public $formV3;

    /** @var BlockCollection */
    protected $fieldBlocks;

    public function __construct(FormMigrationPayload $payload)
    {
        $this->formV2 = new FormMetaDecorator($payload->formV2);
        $this->formV3 = $payload->formV3;
        $this->fieldBlocks = $payload->formV3->blocks;
    }

    public function canHandle(): bool
    {
        return true;
    }

    abstract public function process();

    protected function getMetaV2($key)
    {
        return give()->form_meta->get_meta($this->formV2->id, $key, true);
    }
}
