<?php

namespace Give\DonationForms\Factories;

use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Models\Factories\ModelFactory;

class DonationFormFactory extends ModelFactory
{
    /**
     * @since 3.0.0
     */
    public function definition(): array
    {
        $blocksJson = file_get_contents(
            GIVE_PLUGIN_DIR . 'src/FormBuilder/resources/js/form-builder/src/blocks.json'
        );

        return [
            'title' => __('GiveWP Donation Form', 'give'),
            'status' => DonationFormStatus::PUBLISHED(),
            'settings' => FormSettings::fromArray([
                'enableDonationGoal' => false,
                'goalAmount' => $this->faker->numberBetween(100, 5000),
                'enableAutoClose' => false,
                'registration' => 'none',
                'goalType' => GoalType::AMOUNT(),
                'designId' => 'classic',
                'showHeading' => true,
                'showDescription' => true,
                'heading' => __('Support Our Cause', 'give'),
                'description' => __(
                    'Help our organization by donating today! All donations go directly to making a difference for our cause.',
                    'give'
                ),
                'goalAchievedMessage' => __('Thank you to all our donors, we have met our fundraising goal.', 'give'),
            ]),
            'blocks' => BlockCollection::fromJson($blocksJson),
        ];
    }
}
