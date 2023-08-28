<?php

namespace Give\FormMigration\Actions;

use Give\Framework\Database\DB;

class TransferDonations
{
    protected $sourceId;

    public function __construct($sourceId)
    {
        $this->sourceId = $sourceId;
    }

    public static function from($sourceId): self
    {
        return new TransferDonations($sourceId);
    }

    public function to($destinationId): void
    {
        $this->__invoke($destinationId);
    }

    public function __invoke($destinationId)
    {
        DB::table('give_donationmeta')
            ->where('meta_key', '_give_payment_form_id')
            ->where('meta_value', $this->sourceId)
            ->update(['meta_value' => $destinationId]);

        DB::table('give_revenue')
            ->where('form_id', $this->sourceId)
            ->update(['form_id' => $destinationId]);

        give_update_meta($destinationId, 'transferredFormId', true);
    }
}
