<?php

namespace Give\DonationForms\Adapter;

/**
 * @unreleased
 */
class GoalSettings
{
    public string $title;

    public static function fromArray(array $data): GoalSettings
    {
        $settings = new static();
        $settings->title = $data['title'];

        return $settings;
    }
}
