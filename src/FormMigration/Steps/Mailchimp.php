<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.3.0
 */
class Mailchimp extends FormMigrationStep
{
    /**
     * @since 3.3.0
     */
    public function canHandle(): bool
    {
        return $this->formV2->isMailchimpEnabled();
    }

    /**
     * @since 3.3.0
     */
    public function process(): void
    {
        $block = BlockModel::make([
            'name'       => 'givewp/mailchimp',
            'attributes' => $this->getAttributes(),
        ]);

        $this->fieldBlocks->insertAfter('givewp/email', $block);
    }

    /**
     * @since 3.3.0
     */
    private function getAttributes(): array
    {
        return [
            'label'            => $this->formV2->getMailchimpLabel(),
            'checked'          => $this->formV2->getMailchimpDefaultChecked(),
            'doubleOptIn'      => give_get_option('give_mailchimp_double_opt_in', true),
            'subscriberTags'   => $this->formV2->getMailchimpSubscriberTags(),
            'sendDonationData' => $this->formV2->getMailchimpSendDonationData(),
            'sendFFMData'      => $this->formV2->getMailchimpSendFFMData(),
            'defaultAudiences' => $this->formV2->getMailchimpDefaultAudiences(),
        ];
    }
}

