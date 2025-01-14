<?php

namespace Give\Donations\Repositories;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\Facades\Str;

/**
 * @unreleased
 */
class DonationMetaRepository
{
    /**
     * @unreleased
     * @return mixed|null
     */
    public function get(int $donationId, string $metaKey)
    {
        $query = $this->prepareQuery()
            ->where("donation_id", $donationId)
            ->where("meta_key", $metaKey)
            ->get();

        if (!$query) {
            return null;
        }

        $value = $query->meta_value;

        if (Str::isJson($value)) {
            return json_decode($value, false);
        }

        return $value;
    }

    /**
     * @unreleased
     */
    public function upsert(int $donationId, string $metaKey, $metaValue): void
    {
        if (is_array($metaValue) || is_object($metaValue)) {
            $metaValue = json_encode($metaValue);
        }

        $exists = $this->get($donationId, $metaKey);

        $queryBuilder = $this->prepareQuery();

        if (!$exists) {
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

    /**
     * @unreleased
     */
    public function prepareQuery(): QueryBuilder
    {
        return DB::table("give_donationmeta");
    }
}
