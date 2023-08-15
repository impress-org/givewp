<?php

namespace Give\DonationForms\Actions;

use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Consent;

class ConvertConsentBlockToFieldsApi
{
    /**
     * @since 3.0.0
     */
    public function __invoke(BlockModel $block, int $blockIndex)
    {
        return Consent::make('consent' . '-' . $blockIndex)
            ->tap(function (Consent $consentField) use ($block) {
                if ($block->getAttribute('useGlobalSettings')) {
                    $this->setGlobalAttributes($consentField);
                } else {
                    $this->setPerFormAttributes($consentField, $block);
                }

                return $consentField;
            });
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    private function setGlobalAttributes(Consent $field)
    {
        $field
            ->useGlobalSettings(true)
            ->checkboxLabel(give_get_option('agree_to_terms_label'))
            ->displayType('showFormTerms')
            ->linkText(__('Show terms', 'give'))
            ->agreementText(give_get_option('agreement_text'));
    }

    /**
     * @since 3.0.0
     *
     * @return void
     */
    private function setPerFormAttributes(Consent $field, BlockModel $block)
    {
        $field
            ->useGlobalSettings(false)
            ->checkboxLabel($block->getAttribute('checkboxLabel'))
            ->displayType($block->getAttribute('displayType'))
            ->linkText($block->getAttribute('linkText'))
            ->linkUrl($block->getAttribute('linkUrl'))
            ->modalHeading($block->getAttribute('modalHeading'))
            ->modalAcceptanceText($block->getAttribute('modalAcceptanceText'))
            ->agreementText($block->getAttribute('agreementText'));
    }

}
