<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class GiftAid extends FormMigrationStep
{

    /**
     * @since 3.4.0
     *
     * @return void
     */
    public function process()
    {
        $giftAidStatus = $this->formV2->getGiftAidStatus();
        $useGlobalSettings = $giftAidStatus === 'global';

        if (empty($giftAidStatus) || (
                $useGlobalSettings === true &&
                !give_is_setting_enabled(give_get_option('give_gift_aid_enable_disable', 'disabled'))
            ) || !in_array($giftAidStatus, ['enabled', 'global'], true)) {
            return;
        }

        $giftAidSettings = [
            'useGlobalSettings' => $useGlobalSettings,
            'title' => $this->formV2->getGiftAidTitle(),
            'description' => $this->formV2->getGiftAidDescription(),
            'longExplanationEnabled' => $this->formV2->getGiftAidLongExplanationEnabled(),
            'linkText' => __('Tell me more', 'give-gift-aid'),
            'modalHeader' => __('What is Gift Aid?', 'give-gift-aid'),
            'longExplanation' => $this->formV2->getGiftAidLongExplanation(),
            'checkboxLabel' => $this->formV2->getGiftAidCheckboxLabel(),
            'agreementText' => $this->formV2->getGiftAidAgreementText(),
            'declarationForm' => $this->formV2->getGiftAidDeclarationForm(),
        ];

        $giftAidBlock = BlockModel::make([
            'name' => 'givewp-gift-aid/gift-aid',
            'attributes' => $giftAidSettings,
        ]);
        $this->fieldBlocks->insertAfter('givewp/donation-amount', $giftAidBlock);
    }
}
