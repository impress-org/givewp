<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Support\Facades\DateTime\Temporal;

class ConvertQueryDataToDonationForm
{
    /**
     * @since 3.0.0
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
