<?php

namespace Give\Donors\Repositories;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
class DonorMetaRepository
{

    /**
     * @unreleased
     */
    public function upsert(int $donorId, string $metaKey, $metaValue): void
    {
        $queryBuilder = DB::table("give_donormeta");

        $query = $queryBuilder
            ->where("donor_id", $donorId)
            ->where("meta_key", $metaKey)
            ->get();

        if (!$query) {
            $queryBuilder->insert([
                "donor_id" => $donorId,
                "meta_key" => $metaKey,
                "meta_value" => $metaValue
            ]);
        } else {
            $queryBuilder
                ->where("donor_id", $donorId)
                ->where("meta_key", $metaKey)
                ->update(["meta_value" => $metaValue]);
        }
    }
}
