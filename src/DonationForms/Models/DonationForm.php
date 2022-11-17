<?php

namespace Give\DonationForms\Models;

use DateTime;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Contracts\ModelReadOnly;
use Give\Framework\Models\Model;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class DonationForm
 *
 * @unreleased
 *
 * @property int $id
 * @property string $title
 * @property string $donationLevels
 * @property string $goal
 * @property int $donationsCount
 * @property Money $donationsRevenue
 * @property string $shortcode
 * @property DateTime $createdAt
 * @property DateTime $updatedAt
 * @property string $status // TODO: Implement DonationFormStatus class and replace this with DonationFormStatus
 */
class DonationForm extends Model implements ModelReadOnly, ModelHasFactory
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'title' => 'string',
        'donationLevels' => 'string',
        'goal' => 'string',
        'donationsCount' => 'int',
        'donationsRevenue' => Money::class,
        'shortcode' => 'string',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
        'status' => 'string', // TODO: Implement DonationFormStatus class and replace this with DonationFormStatus::class
    ];

    /**
     * @inheritdoc
     */
    protected $relationships = [
        'donations' => Relationship::HAS_MANY,
    ];

    public static function find($id)
    {
        return give()->donationForms->getById($id);
    }

    public static function query()
    {
        return give()->donationForms->prepareQuery();
    }

    public static function fromQueryBuilderObject($object)
    {
        // TODO: Implement fromQueryBuilderObject() method.
    }

    public static function factory()
    {
        // TODO: Implement factory() method.
    }
}
