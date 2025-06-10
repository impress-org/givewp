<?php

namespace Give\Campaigns\Blocks\CampaignComments\DataTransferObjects;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 4.0.0
 */
class BlockAttributes implements Arrayable
{
    /**
     * @var string
     */
    public $blockId;

    /**
     * @var int
     */
    public $campaignId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $showAnonymous;

    /**
     * @var bool
     */
    public $showAvatar;

    /**
     * @var bool
     */
    public $showDate;

    /**
     * @var bool
     */
    public $showName;

    /**
     * @var int
     */
    public $commentLength;

    /**
     * @var string
     */
    public $readMoreText;

    /**
     * @var int
     */
    public $commentsPerPage;

    /**
     * @since 4.0.0
     */
    public static function fromArray(array $array): BlockAttributes
    {
        $self = new self();

        $self->blockId = !empty($array['blockId']) ? (string)$array['blockId'] : null;
        $self->campaignId = !empty($array['campaignId']) ? (int)$array['campaignId'] : null;
        $self->title = !empty($array['title']) ? (string)$array['title'] : '';
        $self->showAnonymous = !isset($array['showAnonymous']) || (bool)$array['showAnonymous'];
        $self->showAvatar = !isset($array['showAvatar']) || (bool)$array['showAvatar'];
        $self->showDate = !isset($array['showDate']) || (bool)$array['showDate'];
        $self->showName = !isset($array['showName']) || (bool)$array['showName'];
        $self->commentLength = isset($array['commentLength']) ? (int)$array['commentLength'] : 200;
        $self->readMoreText = !empty($array['readMoreText']) ? (string)$array['readMoreText'] : '';
        $self->commentsPerPage = isset($array['commentsPerPage']) ? (int)$array['commentsPerPage'] : 3;

        return $self;
    }

    /**
     * @since 4.0.0
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
