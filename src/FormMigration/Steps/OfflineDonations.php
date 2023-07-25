<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Concerns\Blocks\BlockFactory;
use Give\FormMigration\Contracts\FormMigrationStep;

class OfflineDonations extends FormMigrationStep
{
    public function canHandle(): bool
    {
        return $this->formV2->isOfflineDonationsCustomized();
    }

    public function process()
    {
        if($this->formV2->isOfflineDonationsBillingFieldEnabled()) {
            $this->fieldBlocks->findParentByChildName('givewp/payment-gateways')
                ->innerBlocks->append(BlockFactory::billingAddress());
        }

        // @todo Map donation instructions to v3 form.
        $this->formV3->settings->offlineDonationInstructions = $this->formV2->getOfflineDonationInstructions();
    }
}
