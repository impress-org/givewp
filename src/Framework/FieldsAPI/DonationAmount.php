<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;

class DonationAmount extends Group
{
    const TYPE = 'donationAmount';

    /**
     * @throws NameCollisionException
     * @throws EmptyNameException
     */
    public static function make($name): DonationAmount
    {
        return parent::make($name)
            ->append(
                Amount::make('amount'),
                Hidden::make('currency'),
                Hidden::make('donationType'),
                Hidden::make('frequency'),
                Hidden::make('period'),
                Hidden::make('installments')
            );
    }
}