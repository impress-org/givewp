<?php

namespace Give\Campaigns\Models;

use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
use Give\Framework\Models\ValueObjects\Relationship;

/**
 * @unreleased
 *
 * @property int $id
 * @property int $campaignId
 */
class CampaignPage extends Model implements ModelCrud
{
    public $properties = [
        'id' => 'int',
    ];

    public $relationships = [
        'campaign' => Relationship::BELONGS_TO,
    ];

    /**
     * @unreleased
     */
    public function getEditLinkUrl(): string
    {
        return get_edit_post_link($this->id);
    }

    /**
     * @unreleased
     */
    public function campaign()
    {
        // TODO: Implement campaign() relationship method.
    }

    /**
     * @unreleased
     */
    public static function find($id)
    {
        $post = get_post($id);

        if(!$post || $post->post_type !== 'give_campaign_page') {
            throw new \Exception('Campaign page not found');
        }

        return new self(['id' => $post->ID]);
    }

    /**
     * @unreleased
     */
    public static function create(array $attributes)
    {
        $id = wp_insert_post([
            'post_type' => 'give_campaign_page',
            'post_status' => 'publish',
        ]);

        if(is_wp_error($id)) {
            throw new \Exception('Failed to create campaign page');
        }

        return new self(['id' => $id]);
    }

    /**
     * @unreleased
     */
    public function save(): void
    {
        // TODO: Implement save() method.
    }

    /**
     * @unreleased
     */
    public function delete(): bool
    {
        return (bool) wp_delete_post($this->id);
    }

    /**
     * @unreleased
     */
    public static function query()
    {
        // TODO: Implement query() method.
    }

    /**
     * @unreleased
     */
    public static function fromQueryBuilderObject($object)
    {
        // TODO: Implement fromQueryBuilderObject() method.
    }
}
