<?php

namespace Give\DonationForms\Adapter;

use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys as LegacyDonationFormMetaKeys;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Models\ModelQueryBuilder;


/**
 * @unreleased
 */
class Repository
{
    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Form>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(Form::class);

        return $builder->from('posts', 'forms')
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status'],
                ['post_title', 'title'],
                ['post_content', 'page_content']
            )
            ->attachMeta(
                'give_formmeta',
                'ID',
                'form_id',
                ...DonationFormMetaKeys::getColumnsForAttachMetaQuery(),
                ...LegacyDonationFormMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('post_type', 'give_forms')
            ->whereIn('post_status', DonationFormStatus::toArray()) ;
    }
}
