<?php

namespace Give\DonationForms\Adapter;

use DateTime;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\Framework\Models\Model;

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
 * @property bool               $enableDonationGoal
 * @property bool               $usesFormBuilder
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
        'enableDonationGoal' => 'bool',
        'usesFormBuilder' => 'bool',
    ];


    /**
     * @unreleased
     */
    public static function fromQueryBuilderObject(object $object): Form
    {
        return (new ConvertQuery())($object);
    }
}
