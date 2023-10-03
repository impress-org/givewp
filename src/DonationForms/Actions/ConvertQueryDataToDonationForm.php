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
        $donationForm = new DonationForm([
            'id' => (int)$queryObject->id,
            'title' => $queryObject->title,
            'createdAt' => Temporal::toDateTime($queryObject->createdAt),
            'updatedAt' => Temporal::toDateTime($queryObject->updatedAt),
            'status' => new DonationFormStatus($queryObject->status),
            'settings' => FormSettings::fromjson($queryObject->{DonationFormMetaKeys::SETTINGS()->getKeyAsCamelCase()}),
            'blocks' => BlockCollection::fromJson($queryObject->{DonationFormMetaKeys::FIELDS()->getKeyAsCamelCase()}),
        ]);

        $amountBlock = $donationForm->blocks->findByName('givewp/donation-amount');
        $amountLevels = $amountBlock ? $amountBlock->getAttribute('levels') : [];

        if ($amountBlock && count($amountLevels) > 0) {
            $formattedLevels = array_map('give_format_amount', $amountLevels);
            //$amountBlock->setAttribute('levels', $formattedLevels);
        }


        return $donationForm;
    }
}
