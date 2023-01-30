<?php

namespace Give\NextGen\DonationForm\Actions;

use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Properties\FormSettings;
use Give\NextGen\DonationForm\ValueObjects\DonationFormMetaKeys;
use Give\NextGen\DonationForm\ValueObjects\DonationFormStatus;
use Give\NextGen\Framework\Blocks\BlockCollection;

class ConvertQueryDataToDonationForm
{
    /**
     * @since 0.1.0
     *
     * @param  object  $queryObject
     */
    public function __invoke($queryObject): DonationForm
    {
        return new DonationForm([
            'id' => (int)$queryObject->id,
            'title' => $queryObject->title,
            'createdAt' => Temporal::toDateTime($queryObject->createdAt),
            'updatedAt' => Temporal::toDateTime($queryObject->updatedAt),
            'status' => new DonationFormStatus($queryObject->status),
            'settings' => FormSettings::fromjson($queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()}),
            'blocks' => BlockCollection::fromJson($queryObject->{DonationFormMetaKeys::FIELDS()->getKeyAsCamelCase()}),
        ]);
    }
}
