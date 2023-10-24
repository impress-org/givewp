<?php

namespace Give\FormMigration\Concerns\Blocks;

use Give\Framework\Blocks\BlockModel;

class BlockFactory
{
    /**
     * @note Fields API conversion requires a string for the description, even if empty.
     */
    public static function section($title = '', $description = '', BlockModel ...$innerBlocks): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => [
                'title' => $title,
                'description' => $description,
            ],
            'innerBlocks' => $innerBlocks,
        ]);
    }

    public static function paragraph($content): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/paragraph',
            'attributes' => [
                'content' => $content,
            ],
        ]);
    }

    public static function company($isRequired): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/company',
            'attributes' => [
                'label' => __('Company Name', 'give'),
                'isRequired' => $isRequired,
            ]
        ]);
    }

    public static function login($isRequired): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/login',
            'attributes' => [
                'required' => $isRequired,
                'loginRedirect' => false,
                'loginNotice' => __('Already have an account?', 'give'),
                'loginConfirmation' => __('Thank you for your continued support.', 'give'),
            ]
        ]);
    }

    public static function termsAndConditions(array $attributes): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/terms-and-conditions',
            'attributes' => array_merge([
                'useGlobalSettings' => false,
                'checkboxLabel' => __('I agree to the Terms and conditions.', 'give'),
                'displayType' => 'showFormTerms',
                'linkText' => __('Show terms', 'give'),
                'linkUrl' => '',
                'agreementText' => __(
                    'Acceptance of any contribution, gift or grant is at the discretion of the GiveWP. The GiveWP will not accept any gift unless it can be used or expended consistently with the purpose and mission of the GiveWP. No irrevocable gift, whether outright or life-income in character, will be accepted if under any reasonable set of circumstances the gift would jeopardize the donor’s financial security.The GiveWP will refrain from providing advice about the tax or other treatment of gifts and will encourage donors to seek guidance from their own professional advisers to assist them in the process of making their donation.',
                    'give'
                ),
                'modalHeading' => __('Do you consent to the following', 'give'),
                'modalAcceptanceText' => __('Accept', 'give'),
            ], $attributes),
        ]);
    }

    public static function billingAddress(array $attributes = [])
    {
        return BlockModel::make([
            'name' => 'givewp/billing-address',
            'attributes' => array_merge([
                'groupLabel' => 'Billing Address',
                'country' => [['value' => 'sample', 'label' => __("A full country list will be displayed here...", 'give')]],
                'countryLabel' => __("Country", 'give'),
                'address1Label' => __("Address Line 1", 'give'),
                'address1Placeholder' => __("Address Line 1", 'give'),
                'address2Label' => __("Address Line 2", 'give'),
                'address2Placeholder' => __("Address Line 2", 'give'),
                'requireAddress2' => false,
                'cityLabel' => __("City", 'give'),
                'cityPlaceholder' => __("City", 'give'),
                'stateLabel' => __("State/Province/Country", 'give'),
                'statePlaceholder' => __("This changes by country selection...", 'give'),
                'zipLabel' => __("Zip/Postal Code", 'give'),
                'zipPlaceholder' => __("Zip/Postal Code", 'give'),
            ], $attributes),
        ]);
    }

    /**
     * @since 3.0.0
     */
    public static function donorComments(): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/donor-comments',
            'attributes' => [
                'label' => __('Comment', 'give'),
                'description' => __('Would you like to add a comment to this donation?', 'give'),
            ],
        ]);
    }

    public static function anonymousDonations(array $attributes = []): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/anonymous',
            'attributes' => array_merge([
                'label' => __('Make this an anonymous donation.', 'give'),
                'description' => __(
                    'Would you like to prevent your name, image, and comment from being displayed publicly?',
                    'give'
                ),
            ], $attributes),
        ]);
    }
}
