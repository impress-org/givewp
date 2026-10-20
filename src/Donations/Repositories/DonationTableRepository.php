<?php

namespace Give\Donations\Repositories;

use Give\Donations\Models\Donation;
use Give\Donations\Storage\DonationStorageFlag;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * Writes the give_donations table from the Donation model, with the same verbs as
 * DonationRepository. DonationRepository calls these inside its own transaction while the posts
 * storage is still written; once posts are retired this is the donation repository's own write
 * path. Nothing reads from the table yet.
 *
 * Every method is a no-op while the storage flag is posts, which means the table is not in use.
 *
 * @since TBD
 */
class DonationTableRepository
{
    /**
     * @since TBD
     */
    public function insert(Donation $donation): void
    {
        if (!$this->enabled()) {
            return;
        }

        $this->upsertRows([$this->rowFromModel($donation)]);
    }

    /**
     * @since TBD
     */
    public function update(Donation $donation): void
    {
        if (!$this->enabled()) {
            return;
        }

        $this->upsertRows([$this->rowFromModel($donation)]);
    }

    /**
     * @since TBD
     */
    public function trash(Donation $donation): void
    {
        $this->updateStatus($donation->id, DonationStatus::TRASH());
    }

    /**
     * Restore a trashed donation to the status it had before. The model still carries the
     * trashed status, so the previous one is passed in.
     *
     * @since TBD
     */
    public function restore(Donation $donation, DonationStatus $previousStatus): void
    {
        $this->updateStatus($donation->id, $previousStatus);
    }

    /**
     * @since TBD
     */
    public function delete(Donation $donation): void
    {
        $this->deleteByIds($donation->id);
    }

    /**
     * Rows already in table shape, from any mapping. One INSERT ... ON DUPLICATE KEY UPDATE for
     * all of them; generated columns are never listed.
     *
     * @since TBD
     */
    public function upsertRows(array $rows): void
    {
        if (!$rows || !$this->enabled()) {
            return;
        }

        $columns = array_keys($rows[0]);
        $columnList = implode(', ', $columns);
        $updates = implode(', ', array_map(static function ($column) {
            return "{$column} = VALUES({$column})";
        }, array_diff($columns, ['id'])));

        $values = array_map(function (array $row) {
            return '(' . implode(', ', array_map([$this, 'literal'], $row)) . ')';
        }, $rows);

        DB::query(
            "INSERT INTO " . DB::prefix('give_donations') . " ({$columnList}) VALUES " . implode(', ', $values) .
            " ON DUPLICATE KEY UPDATE {$updates}"
        );
    }

    /**
     * @since TBD
     */
    public function deleteByIds(int ...$donationIds): void
    {
        if (!$donationIds || !$this->enabled()) {
            return;
        }

        $in = implode(',', array_map('intval', $donationIds));

        DB::query("DELETE FROM " . DB::prefix('give_donations') . " WHERE id IN ({$in})");
    }

    /**
     * Trash and restore change only the status, so only the status column moves. A renewal's
     * legacy give_subscription status is stored as complete, as the model mapping does.
     *
     * @since TBD
     */
    private function updateStatus(int $donationId, DonationStatus $status): void
    {
        if (!$this->enabled()) {
            return;
        }

        DB::query(DB::prepare(
            "UPDATE " . DB::prefix('give_donations') . " SET status = %s, updated_at = %s WHERE id = %d",
            $status->isRenewal() ? DonationStatus::COMPLETE : $status->getValue(),
            current_time('mysql', true),
            $donationId
        ));
    }

    /**
     * The row for a donation as the model describes it. Column for column the same shape as
     * DonationTableSync::buildRow(), which decodes it from posts; the parity test in the storage
     * suite fails when the two disagree.
     *
     * @since TBD
     */
    private function rowFromModel(Donation $donation): array
    {
        $exchangeRate = $donation->exchangeRate ?? '';
        $address = $donation->billingAddress;

        return [
            'id' => (int)$donation->id,
            'donor_id' => (int)$donation->donorId,
            'campaign_id' => $donation->campaignId ?: null,
            'form_id' => $donation->formId ?: null,
            'subscription_id' => $donation->subscriptionId ?: null,
            'status' => $donation->status->isRenewal() ? DonationStatus::COMPLETE : $donation->status->getValue(),
            'type' => $donation->type->getValue(),
            'mode' => $donation->mode->getValue(),
            'gateway_id' => (string)$donation->gatewayId,
            'gateway_transaction_id' => $this->text($donation->gatewayTransactionId ?? null),
            'amount_total' => (int)$donation->amount->formatToMinorAmount(),
            'fee_amount_recovered' => $donation->feeAmountRecovered ? (int)$donation->feeAmountRecovered->formatToMinorAmount() : null,
            'currency' => $donation->amount->getCurrency()->getCode(),
            'base_currency' => give_get_option('currency', 'USD'),
            'exchange_rate' => is_numeric($exchangeRate) && $exchangeRate > 0 ? (string)$exchangeRate : '1',
            'sequential_number' => give()->donations->getSequentialId($donation->id),
            'purchase_key' => $this->text($donation->purchaseKey ?? null),
            'email' => (string)$donation->email,
            'honorific' => $this->text($donation->honorific ?? null),
            'first_name' => (string)$donation->firstName,
            'last_name' => (string)$donation->lastName,
            'phone' => $this->text($donation->phone ?? null),
            'company' => $this->text($donation->company ?? null),
            'billing_address1' => $this->text($address->address1 ?? null),
            'billing_address2' => $this->text($address->address2 ?? null),
            'billing_city' => $this->text($address->city ?? null),
            'billing_state' => $this->text($address->state ?? null),
            'billing_zip' => $this->text($address->zip ?? null),
            'billing_country' => $this->text($address->country ?? null),
            'anonymous' => (int)(bool)$donation->anonymous,
            'created_at' => get_gmt_from_date(Temporal::getFormattedDateTime(Temporal::withoutMicroseconds($donation->createdAt))),
            'updated_at' => get_gmt_from_date(Temporal::getFormattedDateTime(Temporal::withoutMicroseconds($donation->updatedAt))),
        ];
    }

    /**
     * @since TBD
     */
    private function enabled(): bool
    {
        return !DonationStorageFlag::get()->isPosts();
    }

    /**
     * @since TBD
     *
     * @param mixed $value
     */
    private function literal($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        return DB::prepare('%s', $value);
    }

    /**
     * Nullable text columns hold NULL, never an empty string.
     *
     * @since TBD
     *
     * @param mixed $value
     */
    private function text($value): ?string
    {
        return $value === '' || $value === null ? null : (string)$value;
    }
}
