<?php

namespace Give\Donors;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 4.4.0
 */
class DonorsQuery
{
    /**
     * @var ModelQueryBuilder<Donor>
     */
    protected $query;

    /**
     * @since 4.4.0
     */
    public function __construct()
    {
        $this->query = Donor::query();
    }

    /**
     * Delegates methods not defined locally to the underlying query.
     *
     * @since 4.4.0
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }

        return $this->query->$method(...$args);
    }

    /**
     * @since 4.8.0 Fix subqueries to not return duplicate donors
     * @since 4.4.0
     */
    public function whereDonorsHaveDonations(
        string $mode = '',
        int $campaignId = 0,
        bool $excludeAnonymousDonors = true
    ): self {
        if (empty($mode)) {
            $mode = give_is_test_mode() ? 'test' : 'live';
        }

        $this->query->whereExists(function (QueryBuilder $builder) use ($mode, $campaignId, $excludeAnonymousDonors) {
            $builder
                ->select('1')
                ->from('give_donationmeta', 'dm1')
                ->join(function (JoinQueryBuilder $joinBuilder) use ($mode) {
                    $joinBuilder
                        ->innerJoin('give_donationmeta', 'dm2')
                        ->on('dm2.donation_id', 'dm1.donation_id')
                        ->andOn('dm2.meta_key', DonationMetaKeys::MODE, true)
                        ->andOn('dm2.meta_value', $mode, true);
                })
                ->join(function (JoinQueryBuilder $joinBuilder) {
                    $joinBuilder
                        ->innerJoin('posts', 'p')
                        ->on('p.ID', 'dm1.donation_id')
                        ->andOn('p.post_type', 'give_payment', true);
                })
                ->whereIn('p.post_status', ['publish', 'give_subscription'])
                ->where('dm1.meta_key', DonationMetaKeys::DONOR_ID)
                ->whereRaw(sprintf('AND dm1.meta_value = %s', $this->query->prefixTable('give_donors.id')));

            if ($campaignId) {
                $builder->join(function (JoinQueryBuilder $joinBuilder) use ($campaignId) {
                    $joinBuilder
                        ->innerJoin('give_donationmeta', 'dm3')
                        ->on('dm3.donation_id', 'dm1.donation_id')
                        ->andOn('dm3.meta_key', DonationMetaKeys::CAMPAIGN_ID, true)
                        ->andOn('dm3.meta_value', $campaignId, true);
                });
            }

            if ($excludeAnonymousDonors) {
                $builder->join(function (JoinQueryBuilder $joinBuilder) {
                    $joinBuilder
                        ->innerJoin('give_donationmeta', 'dm4')
                        ->on('dm4.donation_id', 'dm1.donation_id')
                        ->andOn('dm4.meta_key', DonationMetaKeys::ANONYMOUS, true)
                        ->andOn('dm4.meta_value', '0', true);
                });
            }
        });

        return $this;
    }

    /**
     * @since 4.4.0
     */
    public function limit(int $limit): self
    {
        $this->query->limit($limit);

        return $this;
    }

    /**
     * @since 4.4.0
     */
    public function offset(int $offset): self
    {
        $this->query->offset($offset);

        return $this;
    }

    /**
     * @since 4.4.0
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * @since 4.4.0
     */
    public function getAll(): array
    {
        return $this->query->getAll() ?? [];
    }
}
