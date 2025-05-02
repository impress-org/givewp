<?php

namespace Give\DonationForms\Adapter;

use DateTime;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Models\Model;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 *
 * @property int                $id
 * @property string             $title
 * @property DateTime           $createdAt
 * @property DateTime           $updatedAt
 * @property DonationFormStatus $status
 * @property GoalSettings       $goalSettings
 * @property array              $levels
 * @property bool               $goalOption
 * @property int                $donationsCount
 * @property Money              $raisedAmount
 */
class Form extends Model
{
    /**
     * @inheritdoc
     */
    protected $properties = [
        'id' => 'int',
        'title' => 'string',
        'createdAt' => DateTime::class,
        'updatedAt' => DateTime::class,
        'status' => DonationFormStatus::class,
        'goalSettings' => GoalSettings::class,
        'levels' => 'array',
        'goalOption' => 'bool',
        'totalNumberOfDonations' => 'int',
        'totalAmountDonated' => Money::class,
        'usesFormBuilder' => 'bool',
    ];


    /**
     * @unreleased
     */
    public static function fromQueryBuilderObject(object $object): Form
    {
        return (new Converter())($object);
    }
}
