<?php

namespace Give\Campaigns\Models;

use DateTime;
use Give\Campaigns\Repositories\CampaignPageRepository;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 4.0.0
 *
 * @property int $id
 * @property int $campaignId
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 * @property CampaignPageStatus $status
 * @property string $content
 */
class CampaignPage extends Model implements ModelCrud
{
    public $properties = [
        'id' => 'int',
        'campaignId' => 'int',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
        'status' => CampaignPageStatus::class,
        'content' => 'string'
    ];

    public $relationships = [
        'campaign' => Relationship::BELONGS_TO,
    ];

    /**
     * @since 4.0.0
     */
    public function getEditLinkUrl(): string
    {
        // By default, the URL is encoded for display purposes.
        // Setting any other value prevents encoding the URL.
        return get_edit_post_link($this->id, 'redirect');
    }

    /**
     * @since 4.0.0
     */
    public function campaign(): ?Campaign
    {
        return Campaign::find($this->campaignId);
    }

    /**
     * @since 4.0.0
     */
    public static function find($id): ?CampaignPage
    {
        return give(CampaignPageRepository::class)->getById($id);
    }

    /**
     * @since 4.0.0
     */
    public static function create(array $attributes): CampaignPage
    {
        $campaignPage = new static($attributes);

        give(CampaignPageRepository::class)->insert($campaignPage);

        return $campaignPage;
    }

    /**
     * @since 4.0.0
     */
    public function save(): void
    {
        if (!$this->id) {
            give(CampaignPageRepository::class)->insert($this);
        } else {
            give(CampaignPageRepository::class)->update($this);
        }
    }

    /**
     * @since 4.0.0
     */
    public function delete(): bool
    {
        return give(CampaignPageRepository::class)->delete($this);
    }

    /**
     * @since 4.0.0
     *
     * @return ModelQueryBuilder<CampaignPage>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(CampaignPageRepository::class)->prepareQuery();
    }

    /**
     * @since 4.0.0
     */
    public static function fromQueryBuilderObject($object): CampaignPage
    {
        return new CampaignPage([
            'id' => (int) $object->id,
            'campaignId' => (int) $object->campaignId,
            'createdAt' => Temporal::toDateTime($object->createdAt),
            'updatedAt' => Temporal::toDateTime($object->updatedAt),
            'status' => new CampaignPageStatus($object->status),
            'content' => $object->content
        ]);
    }
}
