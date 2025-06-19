<?php

namespace Give\Donors;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
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

        $this->query->join(function (JoinQueryBuilder $builder) use ($mode) {
            // The donationmeta1.donation_id should be used in other "donationmeta" joins to make sure we are retrieving data from the proper donation
            $builder->innerJoin('give_donationmeta', 'donationmeta1')
                ->on('donationmeta1.meta_key', DonationMetaKeys::DONOR_ID, true)
                ->andOn('donationmeta1.meta_value', 'ID');

            // Include only current payment "mode"
            $builder->innerJoin('give_donationmeta', 'donationmeta2')
                ->on('donationmeta2.meta_key', DonationMetaKeys::MODE, true)
                ->andOn('donationmeta2.meta_value', $mode, true)
                ->andOn('donationmeta2.donation_id', 'donationmeta1.donation_id');
        });

        if ($campaignId) {
            // Filter by CampaignId - Donors only can be filtered by campaignId if they donated to a campaign
            $this->query->join(function (JoinQueryBuilder $builder) use ($campaignId) {
                $builder->innerJoin('give_donationmeta', 'donationmeta3')
                    ->on('donationmeta3.meta_key', DonationMetaKeys::CAMPAIGN_ID, true)
                    ->andOn('donationmeta3.meta_value', $campaignId, true)
                    ->andOn('donationmeta3.donation_id', 'donationmeta1.donation_id');
            });
        }

        if ($excludeAnonymousDonors) {
            // Exclude anonymous donors from results - Donors only can be excluded if they made an anonymous donation
            $this->query->join(function (JoinQueryBuilder $builder) {
                $builder->innerJoin('give_donationmeta', 'donationmeta4')
                    ->on('donationmeta4.meta_key', DonationMetaKeys::ANONYMOUS, true)
                    ->andOn('donationmeta4.meta_value', '0')
                    ->andOn('donationmeta4.donation_id', 'donationmeta1.donation_id');
            });
        }

        // Make sure the donation is valid
        $this->query->whereIn('donationmeta1.donation_id', function (QueryBuilder $builder) {
            $builder
                ->select('ID')
                ->from('posts')
                ->where('post_type', 'give_payment')
                ->whereIn('post_status', ['publish', 'give_subscription'])
                ->whereRaw("AND ID = donationmeta1.donation_id");
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
