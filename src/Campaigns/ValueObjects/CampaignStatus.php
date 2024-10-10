<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * Statuses copied from https://github.com/impress-org/give-peer-to-peer/blob/develop/src/P2P/ValueObjects/Status.php
 *
 * @method static CampaignStatus ARCHIVE()
 * @method static CampaignStatus ACTIVE()
 * @method static CampaignStatus INACTIVE()
 * @method static CampaignStatus DRAFT()
 * @method static CampaignStatus PENDING()
 * @method static CampaignStatus PROCESSING()
 * @method static CampaignStatus FAILED()
 * @method bool isArchive()
 * @method bool isActive()
 * @method bool isInactive()
 * @method bool isDraft()
 * @method bool isPending()
 * @method bool isProcessing()
 * @method bool isFailed()
 */
class CampaignStatus extends Enum
{
    const ARCHIVE  = 'archive';
    const ACTIVE  = 'active';
    const INACTIVE  = 'inactive';
    const DRAFT = 'draft';
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const FAILED = 'failed';
}
