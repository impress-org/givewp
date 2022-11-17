<?php

namespace Give\DonationForms\Models;

use DateTime;
use Give\DonationForms\DataTransferObjects\DonationFormQueryData;
use Give\Framework\Models\Contracts\ModelHasFactory;
use Give\Framework\Models\Contracts\ModelReadOnly;
use Give\Framework\Models\Model;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class DonationForm
 *
 * @unreleased
 *
 * @property int $id
 * @property string $title
 * @property array $levels // TODO: Implement DonationFormLevel class and replace this with DonationFormLevel
 * @property bool $goalOption
 * @property int $totalNumberOfDonations
 * @property Money $totalAmountDonated
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
        'levels' => 'array', // TODO: Implement DonationFormLevel class and replace this with DonationFormLevel
        'goalOption' => 'bool',
        'totalNumberOfDonations' => 'int',
        'totalAmountDonated' => Money::class,
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

    /**
     * @unreleased
     *
     * @param $id
     *
     * @return DonationForm|null
     */
    public static function find($id)
    {
        return give()->donationForms->getById($id);
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<DonationForm>
     */
    public static function query(): ModelQueryBuilder
    {
        return give()->donationForms->prepareQuery();
    }

    /**
     * @unreleased
     *
     * @param object $object
     *
     * @return DonationForm
     */
    public static function fromQueryBuilderObject($object): DonationForm
    {
        return DonationFormQueryData::fromObject($object)->toDonationForm();
    }

    /**
     * @unreleased
     */
    public static function factory()
    {
        // TODO: Implement factory() method.
    }
}
