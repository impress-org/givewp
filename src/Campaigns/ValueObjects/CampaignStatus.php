<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\ListTable\StatusVocabulary;
use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since TBD Add labels(), label() and vocabulary().
 * @since 4.0.0
 *
 * Statuses copied from https://github.com/impress-org/give-peer-to-peer/blob/develop/src/P2P/ValueObjects/Status.php
 *
 * @method static CampaignStatus ARCHIVED()
 * @method static CampaignStatus ACTIVE()
 * @method static CampaignStatus INACTIVE()
 * @method static CampaignStatus DRAFT()
 * @method static CampaignStatus PENDING()
 * @method static CampaignStatus PROCESSING()
 * @method static CampaignStatus FAILED()
 * @method bool isArchived()
 * @method bool isActive()
 * @method bool isInactive()
 * @method bool isDraft()
 * @method bool isPending()
 * @method bool isProcessing()
 * @method bool isFailed()
 */
class CampaignStatus extends Enum
{
    const ARCHIVED  = 'archived';
    const ACTIVE  = 'active';
    const INACTIVE  = 'inactive';
    const DRAFT = 'draft';
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const FAILED = 'failed';

    /**
     * @since TBD
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE => __('Active', 'give'),
            self::INACTIVE => __('Inactive', 'give'),
            self::ARCHIVED => __('Archived', 'give'),
            self::DRAFT => __('Draft', 'give'),
            self::PENDING => __('Pending', 'give'),
            self::PROCESSING => __('Processing', 'give'),
            self::FAILED => __('Failed', 'give'),
        ];
    }

    /**
     * @since TBD
     */
    public function label(): string
    {
        return self::labels()[$this->getValue()];
    }

    /**
     * Pending, Processing and Failed are internal: they show as badges but the filter does not offer them.
     *
     * @since TBD
     */
    public static function vocabulary(): StatusVocabulary
    {
        return new StatusVocabulary(
            self::labels(),
            [self::ACTIVE, self::INACTIVE, self::ARCHIVED, self::DRAFT]
        );
    }
}
