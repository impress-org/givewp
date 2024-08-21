<?php

namespace Give\Onboarding;

use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.15.0
 */
class BlockFactory
{
    /**
     *
     * @since 3.15.0
     *
     * @param array $attributes
     *
     * @return BlockModel
     */
    public static function company(array $attributes = []): BlockModel
    {
        return BlockModel::make([
            'name'       => 'givewp/company',
            'attributes' => array_merge([
                'label'      => __('Company Name', 'give'),
                'isRequired' => false,
            ], $attributes),
        ]);
    }

    /**
     *
     * @since 3.15.0
     *
     * @param array $attributes
     *
     * @return BlockModel
     */
    public static function termsAndConditions(array $attributes = []): BlockModel
    {
        return BlockModel::make([
            'name'       => 'givewp/terms-and-conditions',
            'attributes' => array_merge([
                'useGlobalSettings'   => false,
                'checkboxLabel'       => __('I agree to the Terms and conditions.', 'give'),
                'displayType'         => 'showFormTerms',
                'linkText'            => __('Show terms', 'give'),
                'linkUrl'             => '',
                'agreementText'       => __(
                    'Acceptance of any contribution, gift or grant is at the discretion of the GiveWP. The GiveWP will not accept any gift unless it can be used or expended consistently with the purpose and mission of the GiveWP. No irrevocable gift, whether outright or life-income in character, will be accepted if under any reasonable set of circumstances the gift would jeopardize the donor’s financial security. The GiveWP will refrain from providing advice about the tax or other treatment of gifts and will encourage donors to seek guidance from their own professional advisers to assist them in the process of making their donation.',
                    'give'
                ),
                'modalHeading'        => __('Do you consent to the following', 'give'),
                'modalAcceptanceText' => __('Accept', 'give'),
            ], $attributes),
        ]);
    }

    /**
     *
     * @since 3.15.0
     *
     * @param array $attributes
     *
     * @return BlockModel
     */
    public static function donorComments(array $attributes = []): BlockModel
    {
        return BlockModel::make([
            'name'       => 'givewp/donor-comments',
            'attributes' => array_merge([
                'label'       => __('Comment', 'give'),
                'description' => __('Would you like to add a comment to this donation?', 'give'),
            ], $attributes),
        ]);
    }

    /**
     *
     * @since 3.15.0
     *
     * @param array $attributes
     *
     * @return BlockModel
     */
    public static function anonymousDonations(array $attributes = []): BlockModel
    {
        return BlockModel::make([
            'name'       => 'givewp/anonymous',
            'attributes' => array_merge([
                'label'       => __('Make this an anonymous donation.', 'give'),
                'description' => __(
                    'Would you like to prevent your name, image, and comment from being displayed publicly?',
                    'give'
                ),
            ], $attributes),
        ]);
    }
}
