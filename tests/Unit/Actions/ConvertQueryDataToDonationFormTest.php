<?php

namespace Give\Tests\Unit\Actions;

use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\NextGen\DonationForm\Actions\ConvertQueryDataToDonationForm;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\DonationForm\ValueObjects\GoalType;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class ConvertQueryDataToDonationFormTest extends TestCase {
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnDonationForm()
    {
        $createdAt = Temporal::getCurrentFormattedDateForDatabase();
        $updatedAt = Temporal::getCurrentFormattedDateForDatabase();
        $blockCollection = new BlockCollection([
                new BlockModel('namespace/block', 'client-id', true)
        ]);

        $queryData = (object)[
            'id' => 1,
            'title' => 'Donation Form',
            'createdAt' => Temporal::getCurrentFormattedDateForDatabase(),
            'updatedAt' => Temporal::getCurrentFormattedDateForDatabase(),
            'status' => DonationFormStatus::PUBLISHED,
            'settings' => json_encode([
                'enableDonationGoal' => false,
                'enableAutoClose' => false,
                'registration' => 'none',
                'goalType' => GoalType::AMOUNT()->getValue(),
            ]),
            'fields' =>  $blockCollection->toJson()
        ];

        $donationForm = (new ConvertQueryDataToDonationForm())($queryData);

        $mockDonationForm = new DonationForm([
            'id' => 1,
            'title' => 'Donation Form',
            'createdAt' => Temporal::toDateTime($createdAt),
            'updatedAt' => Temporal::toDateTime($updatedAt),
            'status' => DonationFormStatus::PUBLISHED(),
            'settings' => FormSettings::fromArray([
                'enableDonationGoal' => false,
                'enableAutoClose' => false,
                'registration' => 'none',
                'goalType' => GoalType::AMOUNT(),
            ]),
            'blocks' => $blockCollection
        ]);

        $this->assertEquals($mockDonationForm->getAttributes(), $donationForm->getAttributes());
    }
}
