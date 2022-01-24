<?php

namespace Give\Donations\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;

/**
 * Class DonationPostData
 *
 * @unreleased
 */
class DonationPostData
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $createdAt;
    /**
     * @var string
     */
    private $updatedAt;
    /**
     * @var DonationStatus
     */
    private $status;
    /**
     * @var int
     */
    private $parentId;

    /**
     * Convert data from WP Post to Donation
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromPost($post)
    {
        $self = new static();

        $self->id = $post->ID;
        $self->createdAt = get_post_datetime($post, 'date');
        $self->updatedAt = get_post_datetime($post, 'modified');
        $self->status = new DonationStatus($post->post_status);
        $self->parentId = $post->post_parent;

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donation
     */
    public function toDonation()
    {
        $amount = (int)give()->payment_meta->get_meta($this->id, '_give_payment_total', true);
        $currency = give()->payment_meta->get_meta($this->id, '_give_payment_currency', true);
        $donorId = (int)give()->payment_meta->get_meta($this->id, '_give_payment_donor_id', true);
        $firstName = give()->payment_meta->get_meta($this->id, '_give_donor_billing_first_name', true);
        $lastName = give()->payment_meta->get_meta($this->id, '_give_donor_billing_last_name', true);
        $email = give()->payment_meta->get_meta($this->id, '_give_payment_donor_email', true);

        $donation = new Donation($amount, $currency, $donorId, $firstName, $lastName, $email);

        $donation->id = $this->id;
        $donation->createdAt = $this->createdAt;
        $donation->updatedAt = $this->updatedAt;
        $donation->status = $this->status;
        $donation->gateway = give()->payment_meta->get_meta($this->id, '_give_payment_gateway', true);
        $donation->sequentialId = (int)give()->seq_donation_number->get_serial_number($this->id);
        $donation->parentId = $this->parentId;
        $donation->subscriptionId = (int)give()->payment_meta->get_meta($this->id, 'subscription_id', true);

        return $donation;
    }
}
