<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Blocks\BlockModel;

/**
 * @unreleased
 */
class FundsAndDesignations extends FormMigrationStep
{

    /**
     * @unreleased
     */
    public function process()
    {
        $fundsAndDesignationsAttributes = $this->getFundsAndDesignationsAttributes($this->formV2->id);

        if (empty($fundsAndDesignationsAttributes)) {
            return;
        }

        $fundsAndDesignationsBlock = BlockModel::make([
            'name' => 'givewp/funds-and-designations',
            'attributes' => $fundsAndDesignationsAttributes,
        ]);
        $this->fieldBlocks->insertAfter('givewp/donation-amount', $fundsAndDesignationsBlock);
    }

    /**
     * @unreleased
     */
    public function getFundsAndDesignationsAttributes(int $formId): array
    {
        $label = give_get_meta($formId, 'give_funds_label', true);
        $isAdminChoice = 'admin_choice' === give_get_meta($formId, 'give_funds_form_choice', true);
        $adminChoice = give_get_meta($formId, 'give_funds_admin_choice', true);
        $donorOptions = give_get_meta($formId, 'give_funds_donor_choice', true);


        $options = [];
        foreach ($donorOptions as $fundId) {
            $options[] = [
                'value' => $fundId,
                'label' => $this->getFundLabel($fundId),
                'checked' => $isAdminChoice ? $fundId === $adminChoice : true,
            ];
        }

        $adminChoice = ['value' => $adminChoice, 'label' => $this->getFundLabel($adminChoice), 'checked' => true];

        return [
            'label' => $label,
            'isAdminChoice' => $isAdminChoice,
            'fund' => $adminChoice,
            'options' => $options,
        ];
    }

    /**
     * @unreleased
     */
    public function getFundLabel(int $fundId): string
    {
        global $wpdb;

        $fund = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->give_funds} WHERE id = %d", $fundId)
        );

        if ( ! $fund) {
            return '';
        }

        return $fund->title;
    }
}
