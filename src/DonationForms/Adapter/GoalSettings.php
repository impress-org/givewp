<?php

namespace Give\DonationForms\Adapter;

/**
 * @unreleased
 */
class GoalSettings
{
    public string $title;

    public static function fromArray(array $data)
    {
        $settings = new self();
        $settings->title = $data['title'];
    }
}
