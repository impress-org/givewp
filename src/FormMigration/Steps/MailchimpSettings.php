<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @unreleased
 */
class MailchimpSettings extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function process(): void
    {
        $prevFormId = $this->formV2->id;

        $isFormEnabled = $this->getFormMetaValue($prevFormId, '_give_mailchimp_enable') === 'true';
        $isFormDisabled = $this->getFormMetaValue($prevFormId, '_give_mailchimp_disable') === 'true';
        $isGloballyEnabled = give_get_option( 'give_mailchimp_show_checkout_signup') === 'on';

        if ($isFormDisabled || (!$isGloballyEnabled && !$isFormEnabled)) {
            return;
        }

        $block = BlockModel::make([
            'name'       => 'givewp/mailchimp',
            'attributes' => $this->getAttributes($prevFormId)
        ]);

        $this->fieldBlocks->insertAfter('givewp/email', $block);
    }

    /**
     * @unreleased
     */
    private function getFormMetaValue(int $prevFormId, string $metaKey)
    {
        $meta = give()->form_meta->get_meta($prevFormId, $metaKey, true);

        return $meta === '' ? null : $meta;
    }

    /**
     * @unreleased
     */
    private function getAttributes($prevFormId): array
    {
        return   [
            'label'            => $this->getFormMetaValue($prevFormId, '_give_mailchimp_custom_label') ??
                                  give_get_option('give_mailchimp_label', __('Subscribe to newsletter?')),

            'checked'          => $this->getFormMetaValue($prevFormId, '_give_mailchimp_checked_default') ??
                                  give_get_option('give_mailchimp_checked_default', true),

            'doubleOptIn'      => $this->getFormMetaValue($prevFormId, '_give_mailchimp_double_opt_in') ??
                                  give_get_option('give_mailchimp_double_opt_in', false),

            'subscriberTags'   => $this->getFormMetaValue($prevFormId, '_give_mailchimp_tags') ?? [],

            'sendDonationData' => $this->getFormMetaValue($prevFormId, '_give_mailchimp_send_donation') ??
                                  give_get_option('give_mailchimp_donation_data', true),

            'sendFFMData'      => $this->getFormMetaValue($prevFormId, '_give_mailchimp_send_ffm') ??
                                  give_get_option('give_mailchimp_ffm_pass_field', false),

            'defaultAudiences' => $this->getFormMetaValue($prevFormId, '_give_mailchimp') ??
                                  give_get_option('give_mailchimp_list', []),
        ];
    }
}
