<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * Statuses aligned with WordPress Custom Post Type options.
 *
 * @method static CampaignPageStatus PUBLISH()
 * @method static CampaignPageStatus FUTURE()
 * @method static CampaignPageStatus PRIVATE()
 * @method static CampaignPageStatus DRAFT()
 * @method static CampaignPageStatus TRASH()
 * @method static CampaignPageStatus AUTO_DRAFT()
 * @method static CampaignPageStatus INHERIT()
 * @method bool isPublish()
 * @method bool isFuture()
 * @method bool isPrivate()
 * @method bool isDraft()
 * @method bool isTrash()
 * @method bool isAutoDraft()
 * @method bool isInherit()
 */
class CampaignPageStatus extends Enum
{
    public const PUBLISH = 'publish';
    public const FUTURE = 'future';
    public const PRIVATE = 'private';
    public const DRAFT = 'draft';
    public const TRASH = 'trash';
    public const AUTO_DRAFT = 'auto-draft';
    public const INHERIT = 'inherit';
}
