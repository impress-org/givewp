<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;

class DonationAmount extends Group
{
    const TYPE = 'donationAmount';
    /**
     * @var boolean
     */
    public $subscriptionsEnabled = false;
    /**
     * @var boolean
     */
    public $subscriptionDetailsAreFixed = false;

    /**
     * @throws NameCollisionException
     * @throws EmptyNameException
     */
    public static function make($name): DonationAmount
    {
        return parent::make($name)
            ->append(
                Amount::make('amount'),
                Hidden::make('currency')
            );
    }

    /**
     * @since 3.0.0
     */
    public function enableSubscriptions($enable = true): self
    {
        $this->subscriptionsEnabled = $enable;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function subscriptionDetailsAreFixed($fixed = true): self
    {
        $this->subscriptionDetailsAreFixed = $fixed;

        return $this;
    }

    /**
     * @since 3.0.0
     * @throws NameCollisionException
     */
    public function donationType(Field $field): self
    {
        $this->append($field);

        return $this;
    }

    /**
     * @since 3.0.0
     * @throws NameCollisionException
     */
    public function subscriptionPeriod(Field $field): self
    {
        if ($this->subscriptionsEnabled){
            $this->append($field);
        }

        return $this;
    }

    /**
     * @since 3.0.0
     * @throws NameCollisionException
     */
    public function subscriptionFrequency(Field $field): self
    {
        if ($this->subscriptionsEnabled){
            $this->append($field);
        }

        return $this;
    }

    /**
     * @since 3.0.0
     * @throws NameCollisionException
     */
    public function subscriptionInstallments(Field $field): self
    {
        if ($this->subscriptionsEnabled){
            $this->append($field);
        }

        return $this;
    }
}
