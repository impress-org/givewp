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
            'label'            => $this->formV2->getMailchimpLabel() ??
                                  give_get_option('give_mailchimp_label', __('Subscribe to newsletter?')),
            'checked'          => $this->formV2->getMailchimpDefaultChecked() ??
                                  give_get_option('give_mailchimp_checked_default', true),
            'doubleOptIn'      => $this->formV2->getMailchimpDoubleOptIn() ??
                                  give_get_option('give_mailchimp_double_opt_in'),
            'subscriberTags'   => $this->formV2->getMailchimpSubscriberTags() ??
                                  [''],
            'sendDonationData' => $this->formV2->getMailchimpSendDonationData() ??
                                  give_get_option('give_mailchimp_donation_data', true),
            'sendFFMData'      => $this->formV2->getMailchimpSendFFMData() ??
                                  give_get_option('give_mailchimp_ffm_pass_field'),
            'defaultAudiences' => $this->formV2->getMailchimpDefaultAudiences() ??
                                  give_get_option('give_mailchimp_list', ['']),
        ];
    }
}

