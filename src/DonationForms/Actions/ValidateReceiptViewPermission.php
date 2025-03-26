<?php

namespace Give\DonationForms\Actions;

/**
 * @since 4.0.0
 */
class ValidateReceiptViewPermission
{
    /**
     * The GET parameter name for the receipt ID.
     *
     * @var string
     */
    private const RECEIPT_ID = 'receipt_id';

    /**
     * @since 4.0.0
     */
    public function __invoke(bool $canViewReceipt, int $donationId): bool
    {
        if (!isset($_GET[self::RECEIPT_ID])) {
            return $canViewReceipt;
        }

        $receiptId = give_clean($_GET[self::RECEIPT_ID]);

        if (empty($receiptId)) {
            return $canViewReceipt;
        }

        $donation = give()->donations->getByReceiptId($receiptId);

        if (!$donation || $donation->id !== $donationId) {
            return $canViewReceipt;
        }

        return true;
    }
}
