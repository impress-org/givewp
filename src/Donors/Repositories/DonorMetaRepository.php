<?php

namespace Give\Donors\Repositories;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\Facades\Str;

/**
 * @unreleased
 */
class DonorMetaRepository
{
    /**
     * @unreleased
     *
     * @return mixed|null
     */
    public function get(int $donorId, string $metaKey)
    {
        $query = $this->prepareQuery()
            ->where("donor_id", $donorId)
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
    public function upsert(int $donorId, string $metaKey, $metaValue): void
    {
        if (is_array($metaValue) || is_object($metaValue)) {
            $metaValue = json_encode($metaValue);
        }

        $exists = $this->exists($donorId, $metaKey);

        $queryBuilder = $this->prepareQuery();

        if (!$exists) {
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

    /**
     * @unreleased
     */
    public function exists(int $donorId, string $metaKey): bool
    {
        $query = $this->prepareQuery()
            ->where("donor_id", $donorId)
            ->where("meta_key", $metaKey)
            ->get();

        return !is_null($query);
    }

    /**
     * @unreleased
     */
    public function prepareQuery(): QueryBuilder
    {
        return DB::table("give_donormeta");
    }
}
