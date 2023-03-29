<?php

namespace Give\NextGen\DonationForm\Actions;

use Give\Donations\ValueObjects\DonationType;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\DonationAmount;
use Give\Framework\FieldsAPI\DonationSummary;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Name;
use Give\Framework\FieldsAPI\Paragraph;
use Give\Framework\FieldsAPI\PaymentGateways;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use Give\NextGen\DonationForm\Rules\DonationTypeRule;
use Give\NextGen\DonationForm\Rules\Max;
use Give\NextGen\DonationForm\Rules\Min;
use Give\NextGen\DonationForm\Rules\Size;
use Give\NextGen\DonationForm\Rules\SubscriptionFrequencyRule;
use Give\NextGen\DonationForm\Rules\SubscriptionInstallmentsRule;
use Give\NextGen\DonationForm\Rules\SubscriptionPeriodRule;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;

/**
 * @since 0.1.0
 */
class ConvertDonationFormBlocksToFieldsApi
{
    /**
     * @var int
     */
    protected $formId;
    /**
     * @var string
     */
    protected $currency;

    /**
     * @since 0.1.0
     *
     * @throws TypeNotSupported|NameCollisionException
     */
    public function __invoke(BlockCollection $blocks, int $formId): Form
    {
        $this->formId = $formId;
        $this->currency = give_get_currency($formId);

        $form = new Form('donation-form');
        $blockIndex = 0;
        foreach ($blocks->getBlocks() as $block) {
            $blockIndex++;
            $form->append($this->convertTopLevelBlockToSection($block, $blockIndex));
        }

        return $form;
    }

    /**
     * @since 0.1.0
     * @throws NameCollisionException
     */
    protected function convertTopLevelBlockToSection(BlockModel $block, int $blockIndex): Section
    {
        return Section::make($block->getShortName() . '-' . $blockIndex)
            ->label($block->getAttribute('title'))
            ->description($block->getAttribute('description'))
            ->append(...array_map([$this, 'convertInnerBlockToNode'], $block->innerBlocks->getBlocks()));
    }

    /**
     * @since 0.1.0
     *
     * @throws EmptyNameException
     */
    protected function convertInnerBlockToNode(BlockModel $block): Node
    {
        $node = $this->createNodeFromBlockWithUniqueAttributes($block);

        return $this->mapGenericBlockAttributesToNode($node, $block);
    }

    /**
     * @since 0.1.0
     *
     * @throws EmptyNameException
     */
    protected function createNodeFromBlockWithUniqueAttributes(BlockModel $block): Node
    {
        switch ($block->name) {
            case "custom-block-editor/donation-amount-levels":
                return $this->createNodeFromAmountBlock($block);

            case "custom-block-editor/donor-name":
                return $this->createNodeFromDonorNameBlock($block);

            case "custom-block-editor/paragraph":
                return Paragraph::make(substr(md5(mt_rand()), 0, 7))
                    ->content($block->getAttribute('content'));

            case "custom-block-editor/email-field":
                return Email::make('email')
                    ->emailTag('email')
                    ->rules('required', 'email');

            case "custom-block-editor/payment-gateways":
                return PaymentGateways::make('gatewayId')
                    ->required();

            case "custom-block-editor/donation-summary":
                return DonationSummary::make('donation-summary');

            case "custom-block-editor/company-field":
                return Text::make('company');

            default:
                return Text::make(
                    $block->hasAttribute('fieldName') ?
                        $block->getAttribute('fieldName') :
                        $block->clientId
                )->storeAsDonorMeta(
                    $block->hasAttribute('storeAsDonorMeta') ? $block->getAttribute('storeAsDonorMeta') : false
                );
        }
    }

    /**
     * @since 0.1.0
     */
    protected function createNodeFromDonorNameBlock(BlockModel $block): Node
    {
        return Name::make('name')->tap(function ($group) use ($block) {
            $group->getNodeByName('firstName')
                ->label($block->getAttribute('firstNameLabel'))
                ->placeholder($block->getAttribute('firstNamePlaceholder'))
                ->rules('required', 'max:255');

            $group->getNodeByName('lastName')
                ->label($block->getAttribute('lastNameLabel'))
                ->placeholder($block->getAttribute('lastNamePlaceholder'))
                ->required($block->getAttribute('requireLastName'))
                ->rules('max:255');

            if ($block->hasAttribute('showHonorific') && $block->getAttribute('showHonorific') === true) {
                $group->getNodeByName('honorific')
                    ->label('Title')
                    ->options(...$block->getAttribute('honorifics'));
            } else {
                $group->remove('honorific');
            }
        });
    }

    /**
     * @since 0.2.0
     */
    protected function createNodeFromAmountBlock(BlockModel $block): Node
    {
        return DonationAmount::make('donationAmount')->tap(function (Group $group) use ($block) {
            $amountRules = ['required', 'numeric'];

            if (!$block->getAttribute('customAmount') &&
                $block->getAttribute('priceOption') === 'set') {
                $size = $block->getAttribute('setPrice');

                $amountRules[] = new Size($size);
            }

            if ($block->getAttribute('customAmount')) {
                if ($block->hasAttribute('customAmountMin')) {
                    $amountRules[] = new Min($block->getAttribute('customAmountMin'));
                }

                if ($block->hasAttribute('customAmountMax') && $block->getAttribute('customAmountMax') > 0) {
                    $amountRules[] = new Max($block->getAttribute('customAmountMax'));
                }
            }

            /** @var Amount $amount */
            $amount = $group->getNodeByName('amount');
            $amount
                ->label($block->getAttribute('label'))
                ->levels(...array_map('absint', $block->getAttribute('levels')))
                ->allowLevels($block->getAttribute('priceOption') === 'multi')
                ->allowCustomAmount($block->getAttribute('customAmount'))
                ->fixedAmountValue($block->getAttribute('setPrice'))
                ->defaultValue(
                    $block->getAttribute('priceOption') === 'set' ?
                        $block->getAttribute('setPrice') : 50
                )
                ->rules(...$amountRules);

            /** @var Hidden $currency */
            $currency = $group->getNodeByName('currency');
            $currency
                ->defaultValue($this->currency)
                ->rules('required', 'currency');

            /** @var Hidden $donationType */
            $donationType = $group->getNodeByName('donationType');
            $donationType
                ->defaultValue(DonationType::SINGLE()->getValue())
                ->rules(new DonationTypeRule());

            /** @var Hidden $period */
            $period = $group->getNodeByName('period');
            $period
                ->defaultValue(null)
                ->rules(new SubscriptionPeriodRule());

            /** @var Hidden $frequency */
            $frequency = $group->getNodeByName('frequency');
            $frequency
                ->defaultValue(null)
                ->rules(new SubscriptionFrequencyRule());


            /** @var Hidden $installments */
            $installments = $group->getNodeByName('installments');
            $installments
                ->defaultValue(0)
                ->rules(new SubscriptionInstallmentsRule());
        });
    }

    /**
     * @since 0.1.0
     */
    protected function mapGenericBlockAttributesToNode(Node $node, BlockModel $block): Node
    {
        if ('field' === $node->getNodeType()) {
            // Label
            if ($block->hasAttribute('label')) {
                $node->label($block->getAttribute('label'));
            }

            // Placeholder
            if ($block->hasAttribute('placeholder')) {
                $node->placeholder($block->getAttribute('placeholder'));
            }

            // Required
            if ($block->hasAttribute('isRequired')) {
                $node->required($block->getAttribute('isRequired'));
            }

            if ($block->hasAttribute('displayInAdmin') && $block->getAttribute('displayInAdmin')) {
                $node->displayInAdmin = $block->getAttribute('displayInAdmin');
            }

            /** TODO: ask kyle about $node->showInReceipt() */
            if ($block->hasAttribute('displayInReceipt') && $block->getAttribute('displayInReceipt')) {
                $node->displayInReceipt = $block->getAttribute('displayInReceipt');
            }
        }

        return $node;
    }
}
