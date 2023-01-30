<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use function strlen;

/**
 * @since 0.1.0
 */
class DonationConfirmationReceiptViewRouteData
{
    /**
     * @var string|null
     */
    public $receiptId;

    /**
     *
     * @since 0.1.0
     */
    public static function fromRequest(array $request): DonationConfirmationReceiptViewRouteData
    {
        $self = new self();

        $self->receiptId = isset($request['receipt-id']) && $self::isReceiptIdValid(
            $request['receipt-id']
        ) ? $request['receipt-id'] : null;

        return $self;
    }

    /**
     * The receipt ID is a md5 hash which has a 32 character length.
     *
     * @since 0.1.0
     */
    public static function isReceiptIdValid(string $receiptId): bool
    {
        return strlen($receiptId) === 32;
    }
}
