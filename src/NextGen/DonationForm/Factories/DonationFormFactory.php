<?php

namespace Give\NextGen\DonationForm\Factories;

use Give\Framework\Models\Factories\ModelFactory;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\DonationForm\ValueObjects\GoalTypeOptions;
use Give\NextGen\Framework\Blocks\BlockCollection;

class DonationFormFactory extends ModelFactory
{
    /**
     * @unreleased
     */
    public function definition(): array
    {
        $blocksJson = file_get_contents(GIVE_NEXT_GEN_DIR . 'packages/form-builder/src/blocks.json');

        return [
            'title' => __('GiveWP Donation Form', 'give'),
            'status' => DonationFormStatus::PUBLISHED(),
            'settings' => [
                'enableDonationGoal' => false,
                'enableAutoClose' => false,
                'registration' => 'none',
                'goalType' => GoalTypeOptions::AMOUNT,
                'designId' => 'classic'
            ],
            'blocks' => BlockCollection::fromJson($blocksJson),
        ];
    }
}
