<?php

namespace Give\Subscriptions\Models;

use DateTime;
use Give\Subscriptions\Factories\SubscriptionNoteFactory;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Subscriptions\DataTransferObjects\SubscriptionNoteQueryData;

/**
 * @unreleased
 *
 * @property int $id
 * @property string $content
 * @property int $subscriptionId
 * @property SubscriptionNoteType $type
 * @property DateTime $createdAt
 * @property Subscription $subscription
 */
class SubscriptionNote extends Model implements ModelCrud, ModelHasFactory
{
    /**
     * {@inheritdoc}
     */
    protected $properties = [
        'id' => 'int',
        'content' => 'string',
        'subscriptionId' => 'int',
        'type' => SubscriptionNoteType::class,
        'createdAt' => DateTime::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected $relationships = [
        'subscription' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     *
     * @return SubscriptionNote|null
     */
    public static function find($id)
    {
        return give()->subscriptions->notes->getById($id);
    }

    /**
     * @unreleased
     *
     * @return $this
     *
     * @throws Exception|InvalidArgumentException
     */
    public static function create(array $attributes): SubscriptionNote
    {
        $subscriptionNote = new static($attributes);

        give()->subscriptions->notes->insert($subscriptionNote);

        return $subscriptionNote;
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception|InvalidArgumentException
     */
    public function save()
    {
        if (! $this->id) {
            give()->subscriptions->notes->insert($this);
        } else {
            give()->subscriptions->notes->update($this);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function delete(): bool
    {
        return give()->subscriptions->notes->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<SubscriptionNote>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->subscriptions->notes->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Subscription>
     */
    public function subscription(): ModelQueryBuilder
    {
        return give()->subscriptions->queryById($this->subscriptionId);
    }

    /**
     * @unreleased
     *
     * @param  object  $object
     */
    public static function fromQueryBuilderObject($object): SubscriptionNote
    {
        return SubscriptionNoteQueryData::fromObject($object)->toSubscriptionNote();
    }

    /**
     * @unreleased
     */
    public static function factory(): SubscriptionNoteFactory
    {
        return new SubscriptionNoteFactory(static::class);
    }
}
