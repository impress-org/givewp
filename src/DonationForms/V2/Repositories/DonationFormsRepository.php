<?php

namespace Give\DonationForms\V2\Repositories;

use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys as LegacyDonationFormMetaKeys;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Donations\Models\Donation;
use Give\Framework\Models\ModelQueryBuilder;

/**
 * @since 2.24.0 add support methods for donation form model
 * @since 2.19.0
 */
class DonationFormsRepository
{
    /**
     * Get DonationForm By ID
     *
     * @since 2.24.0
     *
     * @return DonationForm|null
     */
    public function getById(int $donationFormId)
    {
        return $this->prepareQuery()
            ->where('ID', $donationFormId)
            ->get();
    }

    /**
     * @since 2.24.0
     *
     * @return ModelQueryBuilder<Donation>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(DonationForm::class);

        return $builder->from('posts')
            ->select(
                ['ID', 'id'],
                ['post_title', 'title'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status']
            )
            ->attachMeta(
                'give_formmeta',
                'ID',
                'form_id',
                ...DonationFormMetaKeys::getColumnsForAttachMetaQuery(),
                ...LegacyDonationFormMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('post_type', 'give_forms')
            ->whereIn('post_status', DonationFormStatus::toArray());
    }
}
