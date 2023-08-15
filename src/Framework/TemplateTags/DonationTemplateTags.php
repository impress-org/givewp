<?php

namespace Give\Framework\TemplateTags;

use Give\Donations\Models\Donation;
use Give\Framework\TemplateTags\Actions\TransformTemplateTags;

class DonationTemplateTags
{
    /**
     * @var Donation
     */
    protected $donation;
    /**
     * @var string
     */
    protected $content;

    /**
     * @since 3.0.0
     */
    public function __construct(Donation $donation, string $content)
    {
        $this->donation = $donation;
        $this->content = $content;
    }

    /**
     * @since 3.0.0
     */
    public function getContent(): string
    {
        return (new TransformTemplateTags())($this->content, $this->getTags());
    }

    /**
     * @since 3.0.0
     */
    protected function getTags(): array
    {
        return [
            '{first_name}' => $this->donation->firstName,
            '{last_name}' => $this->donation->lastName,
            '{email}' => $this->donation->email,
        ];
    }
}