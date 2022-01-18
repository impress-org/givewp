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
    private $created_at;
    /**
     * @var string
     */
    private $updated_at;
    /**
     * @var string
     */
    private $status;

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
        $self->created_at = $post->post_date;
        $self->updated_at = $post->post_modified;
        $self->status = $post->post_status;

        return $self;
    }

    /**
     * Convert DTO to Donation
     *
     * @return Donation
     */
    public function toDonation()
    {
        $donation =  new Donation();

        $donation->id = $this->id;
        $donation->created_at = $this->created_at;
        $donation->updated_at = $this->updated_at;
        $donation->status = $this->status;
        $donation->amount = (int)give()->payment_meta->get_meta($this->id, '_give_payment_total', true);
        $donation->currency = give()->payment_meta->get_meta($this->id, '_give_payment_currency', true);
        $donation->gateway = give()->payment_meta->get_meta($this->id, '_give_payment_gateway', true);
        $donation->donor_id = (int)give()->payment_meta->get_meta($this->id, '_give_payment_donor_id', true);
        $donation->first_name = give()->payment_meta->get_meta($this->id, '_give_donor_billing_first_name', true);
        $donation->last_name = give()->payment_meta->get_meta($this->id, '_give_donor_billing_last_name', true);
        $donation->email = give()->payment_meta->get_meta($this->id, '_give_payment_donor_email', true);

        return $donation;
    }
}
