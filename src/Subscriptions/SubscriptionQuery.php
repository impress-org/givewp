<?php

namespace Give\Subscriptions;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 4.8.0
 */
class SubscriptionQuery
{
    /**
     * @var ModelQueryBuilder<Subscription>
     */
    protected $query;

    /**
     * @since 4.8.0
     */
    public function __construct()
    {
        $this->query = Subscription::query();
    }

    /**
     * Clone the query with all current conditions applied
     *
     * @since 4.8.0
     */
    public function clone(): self
    {
        $cloned = new self();

        // Copy the internal query state by cloning the ModelQueryBuilder
        $cloned->query = clone $this->query;

        return $cloned;
    }

    /**
     * Delegates methods not defined locally to the underlying query.
     *
     * @since 4.8.0
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
     * @since 4.8.0
     */
    public function selectDonorNames(): self
    {
        $this->query->attachMeta(
            'give_donormeta',
            'customer_id',
            'donor_id',
            [DonorMetaKeys::FIRST_NAME, 'firstName'],
            [DonorMetaKeys::LAST_NAME, 'lastName']
        );

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function whereCampaignId(int $campaignId): self
    {
        $this->query->join(function (JoinQueryBuilder $builder) use ($campaignId) {
            $builder->innerJoin('give_donationmeta', 'donationMetaCampaignId')
                ->on('donationMetaCampaignId.meta_key', DonationMetaKeys::CAMPAIGN_ID, true)
                ->andOn('donationMetaCampaignId.meta_value', $campaignId, true)
                ->andOn('donationMetaCampaignId.donation_id', 'parent_payment_id');
        });

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function excludeAnonymousDonors(): self
    {
        $this->query->join(function (JoinQueryBuilder $builder) {
            $builder->innerJoin('give_donationmeta', 'donationMetaAnonymous')
                ->on('donationMetaAnonymous.meta_key', DonationMetaKeys::ANONYMOUS, true)
                ->andOn('donationMetaAnonymous.meta_value', '0')
                ->andOn('donationMetaAnonymous.donation_id', 'parent_payment_id');
        });

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function whereDonorId(int $donorId): self
    {
        $this->query->where('customer_id', $donorId);

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function whereMode(string $mode): self
    {
        $this->query->where('payment_mode', $mode);

        return $this;
    }

    /**
     * @since 4.8.0
     *
     * @param $status string|array
     */
    public function whereStatus($status): self
    {
        if (is_array($status)) {
            $this->query->whereIn('status', $status);
        } else {
            $this->query->where('status', $status);
        }

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function limit(int $limit): self
    {
        $this->query->limit($limit);

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function offset(int $offset): self
    {
        $this->query->offset($offset);

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * @since 4.8.0
     */
    public function getAll(): array
    {
        return $this->query->getAll() ?? [];
    }

    /**
     * @since 4.8.0
     */
    public function count(): int
    {
        return $this->query->count();
    }
}
