<?php

namespace Give\Donations\Repositories;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
class DonationMetaRepository
{

    /**
     * @unreleased
     */
    public function upsert(int $donationId, string $metaKey, $metaValue): void
    {
        $queryBuilder = DB::table("give_donationmeta");

        $query = $queryBuilder
            ->where("donation_id", $donationId)
            ->where("meta_key", $metaKey)
            ->get();

        if (!$query) {
            $queryBuilder->insert([
                "donation_id" => $donationId,
                "meta_key" => $metaKey,
                "meta_value" => $metaValue
            ]);
        } else {
            $queryBuilder
                ->where("donation_id", $donationId)
                ->where("meta_key", $metaKey)
                ->update(["meta_value" => $metaValue]);
        }
    }
}
