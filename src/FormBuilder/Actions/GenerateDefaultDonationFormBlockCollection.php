<?php

namespace Give\FormBuilder\Actions;

use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;

/**
 * @since 3.1.0
 */
class GenerateDefaultDonationFormBlockCollection
{
    /**
     * @since 3.1.0
     */
    public function __invoke(): BlockCollection
    {
        $section1 = $this->createSection(
            __('How much would you like to donate today?', 'give'),
            __('All donations directly impact our organization and help us further our mission.', 'give'),
            $this->createAmountBlock()
        );

        $section2 = $this->createSection(
            __('Who\'s Giving Today?', 'give'),
            __('We\'ll never share this information with anyone.', 'give'),
            $this->createDonorNameBlock(),
            $this->createEmailBlock()
        );

        $section3 = $this->createSection(
            __('Payment Details', 'give'),
            __('How would you like to pay for your donation?', 'give'),
            $this->createDonationSummaryBlock(),
            $this->createPaymentGatewaysBlock()
        );

        return BlockCollection::make([
            $section1,
            $section2,
            $section3
        ]);
    }

     /**
     * @since 3.1.0
     */
    protected function createSection(string $title, string $description, BlockModel ...$innerBlocks): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => [
                'title' => $title,
                'description' => $description,
            ],
            'innerBlocks' => new BlockCollection($innerBlocks),
        ]);
    }

     /**
     * @since 3.1.0
     */
    protected function createAmountBlock(): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                "label" => __("Donation Amount", 'give'),
                "levels" => [
                    ['value' => 10, 'checked' => true],
                    ['value' => 25],
                    ['value' => 50],
                    ['value' => 100],
                    ['value' => 250],
                    ['value' => 500],
                ],
                "priceOption" => "multi",
                "setPrice" => 25,
                "customAmount" => true,
                "customAmountMin" => 1,
                "recurringBillingPeriodOptions" => [
                    "month"
                ],
                "recurringBillingInterval" => 1,
                "recurringEnabled" => false,
                "recurringLengthOfTime" => "0",
                "recurringOptInDefaultBillingPeriod" => "month",
                "recurringEnableOneTimeDonations" => true
            ],
            'innerBlocks' => [],
        ]);
    }

     /**
     * @since 3.1.0
     */
    protected function createDonorNameBlock(): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/donor-name',
            'attributes' => [
                "showHonorific" => false,
                "honorifics" => [
                    __("Mr", 'give'),
                    __("Ms", 'give'),
                    __("Mrs", 'give')
                ],
                "firstNameLabel" => __("First name", 'give'),
                "firstNamePlaceholder" => __("First name", 'give'),
                "lastNameLabel" => __("Last name", 'give'),
                "lastNamePlaceholder" => __("Last name", 'give'),
                "requireLastName" => false
            ],
            "innerBlocks" => []
        ]);
    }

     /**
     * @since 3.1.0
     */
    protected function createEmailBlock(): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/email',
            'attributes' => [
                "label" => __("Email Address", 'give'),
                "isRequired" => true,
            ],
            "innerBlocks" => []
        ]);
    }

     /**
     * @since 3.1.0
     */
    protected function createDonationSummaryBlock(): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/donation-summary',
            'attributes' => [],
            'innerBlocks' => []
        ]);
    }

     /**
     * @since 3.1.0
     */
    protected function createPaymentGatewaysBlock(): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/payment-gateways',
            'attributes' => [],
            'innerBlocks' => []
        ]);
    }
}
