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
     * The same filters on the donors table alone, so a count does not join the donor meta.
     *
     * @since TBD
     *
     * @var QueryBuilder
     */
    protected $countQuery;

    /**
     * Whether a call reached the model query that the count query does not repeat.
     *
     * @since TBD
     *
     * @var bool
     */
    protected $countQueryIsIncomplete = false;

    /**
     * @since TBD Start the count query.
     * @since 4.4.0
     */
    public function __construct()
    {
        $this->query = Donor::query();
        $this->countQuery = DB::table('give_donors');
    }

    /**
     * Delegates methods not defined locally to the underlying query. A where clause is applied to
     * the count query too.
     *
     * @since TBD Repeat where clauses on the count query.
     * @since 4.4.0
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }

        if (preg_match('/^(or)?where/i', $method)) {
            $this->countQuery->$method(...$args);
        } else {
            $this->countQueryIsIncomplete = true;
        }

        return $this->query->$method(...$args);
    }

    /**
     * @since TBD Compare the donor ID as a string, so the (meta_key, meta_value) index finds each donor's donations
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

        $subquery = function (QueryBuilder $builder) use ($mode, $campaignId, $excludeAnonymousDonors) {
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
                // A number compared with the text column makes MySQL cast every row, which skips the index.
                ->whereRaw(sprintf('AND dm1.meta_value = CONCAT(%s)', $this->query->prefixTable('give_donors.id')));

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
        };

        $this->query->whereExists($subquery);
        $this->countQuery->whereExists($subquery);

        return $this;
    }

    /**
     * @since TBD Apply the clause to the count query too.
     * @since 4.19.0.1
     */
    public function whereDonorsAreNotAnonymous(): self
    {
        $subquery = function (QueryBuilder $builder) {
            $builder
                ->select('1')
                ->from('give_donationmeta', 'dm1')
                ->join(function (JoinQueryBuilder $joinBuilder) {
                    $joinBuilder
                        ->innerJoin('give_donationmeta', 'dm2')
                        ->on('dm2.donation_id', 'dm1.donation_id')
                        ->andOn('dm2.meta_key', DonationMetaKeys::ANONYMOUS, true)
                        ->andOn('dm2.meta_value', '1', true);
                })
                ->where('dm1.meta_key', DonationMetaKeys::DONOR_ID)
                ->whereRaw(sprintf('AND dm1.meta_value = CONCAT(%s)', $this->query->prefixTable('give_donors.id')));
        };

        $this->query->whereNotExists($subquery);
        $this->countQuery->whereNotExists($subquery);

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

    /**
     * The number of donors that match the filters. It counts the donors table alone, because the
     * model query joins five donor meta rows per donor that a count does not read. When a call
     * reached the model query that the count query does not repeat, the model query counts.
     *
     * @since TBD
     */
    public function count(): int
    {
        if ($this->countQueryIsIncomplete) {
            return $this->query->count();
        }

        return (clone $this->countQuery)->count();
    }

    /**
     * Clone the query builder.
     *
     * @since TBD Clone the count query too.
     * @since 4.14.0
     *
     * @return self
     */
    public function clone(): self
    {
        $cloned = new self();
        $cloned->query = clone $this->query;
        $cloned->countQuery = clone $this->countQuery;
        $cloned->countQueryIsIncomplete = $this->countQueryIsIncomplete;

        return $cloned;
    }
}
