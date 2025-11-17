<?php

namespace Give\Subscriptions\DataTransferObjects;

use DateTime;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Subscriptions\Models\SubscriptionNote;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;

/**
 * Class SubscriptionNoteQueryData
 *
 * @since 4.8.0
 */
final class SubscriptionNoteQueryData
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $content;
    /**
     * @var int
     */
    public $subscriptionId;
    /**
     * @var SubscriptionNoteType
     */
    public $type;
    /**
     * @var DateTime
     */
    public $createdAt;

    /**
     * Convert data from Subscription Note Object to Subscription Note Model
     *
     * @since 4.8.0
     */
    public static function fromObject($subscriptionNoteQueryObject): self
    {
        $self = new static();

        $self->id = (int)$subscriptionNoteQueryObject->id;
        $self->content = $subscriptionNoteQueryObject->content;
        $self->subscriptionId = (int)$subscriptionNoteQueryObject->subscriptionId;
        $self->type = $subscriptionNoteQueryObject->type ? new SubscriptionNoteType($subscriptionNoteQueryObject->type) : SubscriptionNoteType::ADMIN();
        $self->createdAt = Temporal::toDateTime($subscriptionNoteQueryObject->createdAt);

        return $self;
    }

    /**
     * Convert DTO to Subscription Note
     *
     * @since 4.8.0
     */
    public function toSubscriptionNote(): SubscriptionNote
    {
        $attributes = get_object_vars($this);

        return new SubscriptionNote($attributes);
    }
}
