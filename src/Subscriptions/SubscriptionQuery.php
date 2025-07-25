<?php

namespace Give\Subscriptions;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class SubscriptionQuery
{
    /**
     * @var ModelQueryBuilder<Subscription>
     */
    protected $query;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->query = Subscription::query();
    }

    /**
     * Delegates methods not defined locally to the underlying query.
     *
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    public function whereDonorId(int $donorId): self
    {
        $this->query->where('customer_id', $donorId);

        return $this;
    }

    /**
     * @unreleased
     */
    public function whereMode(string $mode): self
    {
        $this->query->where('payment_mode', $mode);

        return $this;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function limit(int $limit): self
    {
        $this->query->limit($limit);

        return $this;
    }

    /**
     * @unreleased
     */
    public function offset(int $offset): self
    {
        $this->query->offset($offset);

        return $this;
    }

    /**
     * @unreleased
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * @unreleased
     */
    public function getAll(): array
    {
        return $this->query->getAll() ?? [];
    }
}
