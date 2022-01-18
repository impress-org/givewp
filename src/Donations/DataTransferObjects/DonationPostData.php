<?php

namespace Give\Donations\DataTransferObjects;

use Give\Donations\Models\Donation;

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
     * @var string
     */
    private $status;
    /**
     * @var int
     */
    private $parentId;
    /**
     * @var int
     */
    private $subscriptionId;

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
        $self->createdAt = $post->post_date;
        $self->updatedAt = $post->post_modified;
        $self->status = $post->post_status;
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
        $donation = new Donation();

        $donation->id = $this->id;
        $donation->createdAt = $this->createdAt;
        $donation->updatedAt = $this->updatedAt;
        $donation->status = $this->status;
        $donation->amount = (int)give()->payment_meta->get_meta($this->id, '_give_payment_total', true);
        $donation->currency = give()->payment_meta->get_meta($this->id, '_give_payment_currency', true);
        $donation->gateway = give()->payment_meta->get_meta($this->id, '_give_payment_gateway', true);
        $donation->donorId = (int)give()->payment_meta->get_meta($this->id, '_give_payment_donor_id', true);
        $donation->firstName = give()->payment_meta->get_meta($this->id, '_give_donor_billing_first_name', true);
        $donation->lastName = give()->payment_meta->get_meta($this->id, '_give_donor_billing_last_name', true);
        $donation->email = give()->payment_meta->get_meta($this->id, '_give_payment_donor_email', true);
        $donation->sequentialId = (int)give()->seq_donation_number->get_serial_number($this->id);
        $donation->parentId = $this->parentId;
        $donation->subscriptionId = (int)give()->payment_meta->get_meta($this->id, 'subscription_id', true);

        return $donation;
    }
}
