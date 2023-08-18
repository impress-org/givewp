<?php

namespace Give\DonationForms\DataTransferObjects;

use function strlen;

/**
 * @since 3.0.0
 */
class DonationConfirmationReceiptViewRouteData
{
    /**
     * @var string|null
     */
    public $receiptId;

    /**
     *
     * @since 3.0.0
     */
    public static function fromRequest(array $request): self
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
     * @since 3.0.0
     */
    public static function isReceiptIdValid(string $receiptId): bool
    {
        return strlen($receiptId) === 32;
    }
}
