<?php

namespace Give\DonationForms\Adapter;

use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Support\Facades\DateTime\Temporal;

class Converter
{
    /**
     * @unreleased
     */
    public function __invoke(object $queryObject): Form
    {
        return new Form([
            'id' => (int)$queryObject->id,
            'title' => $queryObject->title,
            'createdAt' => Temporal::toDateTime($queryObject->createdAt),
            'updatedAt' => Temporal::toDateTime($queryObject->updatedAt),
            'status' => new DonationFormStatus($queryObject->status),
            'goalSettings' => $this->getGoalSettings($queryObject)
        ]);
    }


    /**
     * @unreleased
     */
    private function getGoalSettings(object $queryObject): GoalSettings
    {
        $settings = $queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()};

        if ($settings) {
            $settings = FormSettings::fromjson($settings);

            return GoalSettings::fromArray([
                'goalAmount' => $settings->goalAmount,
            ]);
        }

        // good ol pain in the...
        return GoalSettings::fromArray([
            'goalAmount' => 777,
        ]);
    }
}
