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
                'goalAmount' => $this->faker->numberBetween(100, 5000),
                'enableAutoClose' => false,
                'registration' => 'none',
                'goalType' => GoalTypeOptions::AMOUNT,
                'designId' => 'classic',
                'showHeading' => true,
                'showDescription' => true,
                'heading' => __('Support Our Cause', 'give'),
                'description' => __(
                    'Help our organization by donating today! All donations go directly to making a difference for our cause.',
                    'give'
                )
            ],
            'blocks' => BlockCollection::fromJson($blocksJson),
        ];
    }
}
