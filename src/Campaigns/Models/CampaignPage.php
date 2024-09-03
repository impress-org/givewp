<?php

namespace Give\Campaigns\Models;

use DateTime;
use Give\Campaigns\Repositories\CampaignPageRepository;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @unreleased
 *
 * @property int $id
 * @property int $campaignId
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 */
class CampaignPage extends Model implements ModelCrud
{
    public $properties = [
        'id' => 'int',
        'campaignId' => 'int',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
    ];

    public $relationships = [
        'campaign' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     */
    public function getEditLinkUrl(): string
    {
        // By default, the URL is encoded for display purposes.
        // Setting any other value prevents encoding the URL.
        return get_edit_post_link($this->id, 'redirect');
    }

    /**
     * @unreleased
     */
    public function campaign()
    {
        return Campaign::find($this->campaignId);
    }

    /**
     * @unreleased
     */
    public static function find($id)
    {
        return give(CampaignPageRepository::class)
            ->prepareQuery()
            ->where('ID', $id)
            ->get();
    }

    /**
     * @unreleased
     */
    public static function create(array $attributes): CampaignPage
    {
        $campaignPage = new static($attributes);

        give(CampaignPageRepository::class)->insert($campaignPage);

        return $campaignPage;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function delete(): bool
    {
        return give(CampaignPageRepository::class)->delete($this);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<CampaignPage>
     */
    public static function query(): ModelQueryBuilder
    {
        return give(CampaignPageRepository::class)->prepareQuery();
    }

    /**
     * @unreleased
     */
    public static function fromQueryBuilderObject($object): CampaignPage
    {
        return new CampaignPage([
            'id' => (int) $object->id,
            'campaignId' => (int) $object->campaignId,
            'createdAt' => Temporal::toDateTime($object->createdAt),
            'updatedAt' => Temporal::toDateTime($object->updatedAt),
        ]);
    }
}
