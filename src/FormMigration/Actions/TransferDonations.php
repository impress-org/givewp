<?php

namespace Give\FormMigration\Actions;

use Give\FormMigration\Contracts\TransferAction;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;

class TransferDonations extends TransferAction
{
    public function __invoke($destinationId)
    {
        DB::table('give_donationmeta')
            ->where('meta_key', '_give_payment_form_id')
            ->where('meta_value', $this->sourceId)
            ->update(['meta_value' => $destinationId]);

        DB::table('give_revenue')
            ->where('form_id', $this->sourceId)
            ->update(['form_id' => $destinationId]);
    }
}
