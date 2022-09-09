<?php

namespace Give\NextGen\DonationForm\Repositories;

use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\DonationSummary;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Name;
use Give\Framework\FieldsAPI\Paragraph;
use Give\Framework\FieldsAPI\PaymentGateways;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use stdClass;

class DonationFormRepository {
      /**
     * @var PaymentGatewayRegister
     */
    private $paymentGatewayRegister;

    /**
     * @unreleased
     *
     * @param PaymentGatewayRegister $paymentGatewayRegister
     */
    public function __construct(PaymentGatewayRegister $paymentGatewayRegister)
    {
        $this->paymentGatewayRegister = $paymentGatewayRegister;
    }

     /**
     * @return PaymentGateway[]
     */
    public function getEnabledPaymentGateways($formId): array
    {
        $gateways = [];

        $enabledGateways = give_get_option('gateways');
        $defaultGateway = give_get_default_gateway($formId);

        foreach ($enabledGateways as $gatewayId => $enabled) {
            if ($enabled && $this->paymentGatewayRegister->hasPaymentGateway($gatewayId)) {
                $gateways[$gatewayId] = $this->paymentGatewayRegister->getPaymentGateway($gatewayId);
            }
        }

        if (array_key_exists($defaultGateway, $gateways)) {
            $gateways = array_merge([$defaultGateway => $gateways[$defaultGateway]], $gateways);
        }

        return $gateways;
    }

     /**
     * @unreleased
     */
    public function getFormDataGateways(int $formId): array
    {
        $formDataGateways = [];

        foreach ($this->getEnabledPaymentGateways($formId) as $gateway) {
            $gatewayId = $gateway->getId();

            $formDataGateways[$gatewayId] = array_merge(
                [
                    'label' => give_get_gateway_checkout_label($gatewayId) ?? $gateway->getPaymentMethodLabel(),
                ],
                method_exists($gateway, 'formSettings') ? $gateway->formSettings($formId) : []
            );
        }

        return $formDataGateways;
    }


    /**
     * @unreleased
     *
     * @throws EmptyNameException|TypeNotSupported
     */
    public function createFieldsApiForm(int $formId): Form
    {
        $donationForm = new Form($formId);

        $formBlockData = json_decode(get_post($formId)->post_content, false);

        foreach ($formBlockData as $block) {
            $donationForm->append($this->convertFormBlocksToSection($block));
        }

        /** @var Section $paymentDetails */
        $paymentDetails = $donationForm->getNodeByName('payment-details');

        $paymentDetails->append(
            Hidden::make('formId')
                ->defaultValue($formId),

            Hidden::make('currency')
                ->defaultValue(give_get_currency($formId))
        );

        return $donationForm;
    }

    /**
     * @unreleased
     */
    protected function convertFormBlocksToSection(stdClass $block): Section
    {
        return Section::make(substr($block->name, strpos($block->name, '/') + 1))
            ->label($block->attributes->title)
            ->description($block->attributes->description)
            ->append(...array_map([$this, 'convertBlockToNode'], $block->innerBlocks));
    }

    /**
     * @unreleased
     * @throws EmptyNameException
     */
    protected function convertBlockToNode(stdClass $block): Node
    {
        if ($block->name === "custom-block-editor/donation-amount-levels") {
            $node = Amount::make('amount')
                ->levels(...array_map('absint', $block->attributes->levels))
                ->allowCustomAmount()
                ->defaultValue(50)
                ->required();
        } elseif ($block->name === "custom-block-editor/donor-name") {
            $node = Name::make('name')->tap(function ($group) use ($block) {
                $group->getNodeByName('firstName')
                    ->label($block->attributes->firstNameLabel)
                    ->placeholder($block->attributes->firstNamePlaceholder);

                $group->getNodeByName('lastName')
                    ->label($block->attributes->lastNameLabel)
                    ->placeholder($block->attributes->lastNamePlaceholder)
                    ->required($block->attributes->requireLastName);

                if ($block->attributes->showHonorific) {
                    $group->getNodeByName('honorific')
                        ->label('Title')
                        ->options(...$block->attributes->honorifics);
                } else {
                    $group->remove('honorific');
                }
            });
        } elseif ($block->name === "custom-block-editor/paragraph") {
            $node = Paragraph::make(substr(md5(mt_rand()), 0, 7))
                ->content($block->attributes->content);
        } elseif ($block->name === "custom-block-editor/email-field") {
            $node = Email::make('email')->emailTag('email');
        } elseif ($block->name === "custom-block-editor/payment-gateways") {
            $node = PaymentGateways::make('gatewayId');
        } elseif ($block->name === "custom-block-editor/donation-summary") {
            $node = DonationSummary::make('donation-summary');
        } elseif ($block->name === "custom-block-editor/company-field") {
            $node = Text::make('company');
        } else {
            $node = Text::make($block->clientId);
        }

        if ('field' === $node->getNodeType()) {
            // Label
            if (property_exists($block->attributes, 'label')) {
                $node->label($block->attributes->label);
            }

            // Placeholder
            if (property_exists($block->attributes, 'placeholder')) {
                $node->placeholder($block->attributes->placeholder);
            }

            // Required
            if (property_exists($block->attributes, 'isRequired')) {
                $node->required($block->attributes->isRequired);
            }
        }

        return $node;
    }
}
