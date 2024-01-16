<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

class GiftAid extends FormMigrationStep
{

    /**
     * @unlreased
     */
    public function process()
    {
        $giftAidSettings = $this->formV2->getGiftAidSettings();

        if (empty($giftAidSettings) || (
                $giftAidSettings['useGlobalSettings'] === true &&
                !give_is_setting_enabled(give_get_option('give_gift_aid_enable_disable', 'disabled'))
            )) {
            return;
        }

        $giftAidBlock = BlockModel::make([
            'name' => 'givewp-gift-aid/gift-aid',
            'attributes' => $giftAidSettings,
        ]);
        $this->fieldBlocks->insertAfter('givewp/donation-amount', $giftAidBlock);
    }
}
