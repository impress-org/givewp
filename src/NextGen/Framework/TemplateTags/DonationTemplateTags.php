<?php

namespace Give\NextGen\Framework\TemplateTags;

use Give\Donations\Models\Donation;
use Give\NextGen\Framework\TemplateTags\Actions\TransformTemplateTags;

class DonationTemplateTags {
    /**
     * @var Donation
     */
    protected $donation;
    /**
     * @var string
     */
    protected $content;

    /**
     * @unreleased
     */
    public function __construct(Donation $donation, string $content) {
        $this->donation = $donation;
        $this->content = $content;
    }

    /**
     * @unreleased
     */
    public function getContent(): string
    {
        return (new TransformTemplateTags())($this->content, $this->getTags());
    }

    /**
     * @unreleased
     */
    protected function getTags(): array
    {
        return [
            '{donation.firstName}' => $this->donation->firstName,
            '{donation.email}' => $this->donation->email,
            '{donor.firstName}' => $this->donation->donor->firstName,
            '{donor.email}' => $this->donation->donor->email,
        ];
    }
}