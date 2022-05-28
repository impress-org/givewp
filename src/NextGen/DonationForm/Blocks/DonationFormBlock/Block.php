<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock;

use Give\Framework\EnqueueScript;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Radio;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Call;
use Give\NextGen\DonationForm\Actions\GenerateDonateRouteUrl;

class Block
{
    /**
     * @var PaymentGatewayRegister
     */
    private $paymentGatewayRegister;

    /**
     * @unreleased
     *
     * @param  PaymentGatewayRegister  $paymentGatewayRegister
     */
    public function __construct(PaymentGatewayRegister $paymentGatewayRegister)
    {
        $this->paymentGatewayRegister = $paymentGatewayRegister;
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function register()
    {
        register_block_type(
            __DIR__,
            ['render_callback' => [$this, 'render']]
        );
    }

    /**
     * @unreleased
     *
     * @return string|null
     * @throws EmptyNameException
     */
    public function render(array $attributes)
    {
        // return early if we're still inside the editor to avoid server side effects
        if (!empty($_REQUEST)) {
            return null;
        }

        $donationForm = $this->createForm($attributes);

        $donateUrl = Call::invoke(GenerateDonateRouteUrl::class);

        $formId = $attributes['formId'];

        $formDataGateways = $this->getFormDataGateways($formId);

        $exports = [
            'attributes' => $attributes,
            'form' => $donationForm->jsonSerialize(),
            'donateUrl' => $donateUrl,
            'successUrl' => give_get_success_page_uri(),
            'gatewaySettings' => $formDataGateways
        ];

        ob_start();
        ?>

        <script>window.giveNextGenExports = <?= wp_json_encode($exports) ?>;</script>

        <?php
        $this->enqueueScripts($formId);
        ?>

        <div id="root-give-next-gen-donation-form-block"></div>

        <?php
        return ob_get_clean();
    }

    /**
     * @unreleased
     *
     * @throws EmptyNameException
     */
    private function createForm(array $attributes): Form
    {
        $formId = $attributes['formId'];

        $gatewayOptions = [];
        foreach ($this->getEnabledPaymentGateways($formId) as $gateway) {
            $gatewayOptions[] = Radio::make($gateway->getId())->label($gateway->getPaymentMethodLabel());
        }

        $donationForm = new Form($formId);

        $formBlockData = json_decode(get_post($formId)->post_content, false);

        foreach( $formBlockData as $block ) {
            $donationForm->append($this->convertFormBlockDataToFieldsAPI($block));
        }

        /**
         * @todo for some reason, `getNodeByName()` isn't working...
         */
        foreach( $donationForm->all() as $node ) {
            if( 'paymentDetails' === $node->getName() ) {
                $node->append(...$gatewayOptions);
            }
        }

        $donationForm->append(

            Hidden::make('formId')
                ->defaultValue($formId),

            Hidden::make('formTitle')
                ->defaultValue('Give Next Gen Form'),

            Hidden::make('userId')
                ->defaultValue(get_current_user_id()),

            Hidden::make('currency')
                ->defaultValue(give_get_currency($formId))
        );

        return $donationForm;
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
     *
     * @param  object  $block
     * @return Section|Text
     * @throws EmptyNameException
     */
    protected function convertFormBlockDataToFieldsAPI($block)
    {
        if( 'custom-block-editor/payment-gateways' === $block->name ) {
            return Section::make('paymentDetails')
                    ->label($block->attributes->title);
        }

        if ($block->innerBlocks) {

            /**
             * @todo Currently re-purposing sections for groups so that they render for the prototype.
             */
            $section = (false && $block->name === "custom-block-editor/name-field-group")
                ? Group::make('name-group')
                : Section::make($block->clientId);

            if (property_exists($block->attributes, 'title')) {
                $section->label($block->attributes->title);
            }

            foreach ($block->innerBlocks as $innerBlock) {
                $section->append($this->convertFormBlockDataToFieldsAPI($innerBlock));
            }

            return $section;
        }


        /*
         * I'm considering refactoring the `namespace/block-type` to be
         * used as `field-category/block-type` for easier parsing.
         *
         * For example:
         *  `section/donor-information`
         *      `group/donor-name`
         *          `text/donor-first-name`
         *          `text/donor-last-name`
         *      `email/donor-email`
         */

        if ($block->name === "custom-block-editor/donation-amount-levels") {
            $field = Text::make('amount')->required();
        } elseif ($block->name === "custom-block-editor/first-name-field") {
            $field = Text::make('firstName')->required();
        } elseif ($block->name === "custom-block-editor/last-name-field") {
            $field = Text::make('lastName')->required();
        } elseif ($block->name === "custom-block-editor/email-field") {
            $field = Email::make('email')->required()->emailTag('email');
        } else {
            $field = Text::make($block->clientId);
        }

        if (property_exists($block->attributes, 'label')) {
            $field->label($block->attributes->label);
        }

        return $field;
    }

    /**
     * @unreleased
     */
    private function getFormDataGateways(int $formId): array
    {
        $formDataGateways = [];

        foreach ($this->getEnabledPaymentGateways($formId) as $gateway) {
            $gatewayId = $gateway->getId();

            $formDataGateways[$gatewayId] = array_merge(
                [
                    'label' => give_get_gateway_checkout_label($gatewayId) ?? $gateway->getPaymentMethodLabel()
                ],
                method_exists($gateway, 'formSettings') ? $gateway->formSettings($formId) : []
            );
        }

        return $formDataGateways;
    }

    /**
     * @unreleased
     *
     * @return void
     */
    private function enqueueScripts(int $formId)
    {
        // enqueue front-end scripts
        // since this is using render_callback viewScript in blocks.json will not work.
        $enqueueBlockScript = new EnqueueScript(
            'give-next-gen-donation-form-block-js',
            'build/donationFormBlockApp.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );

        (new EnqueueScript(
            'give-donation-form-registrars-js',
            'build/donationFormRegistrars.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        ))->loadInFooter()->enqueue();

        foreach ($this->getEnabledPaymentGateways($formId) as $gateway) {
            if (method_exists($gateway, 'enqueueScript')) {
                /** @var EnqueueScript $script */
                $script = $gateway->enqueueScript();

                $script->dependencies(['give-donation-form-registrars-js'])
                    ->loadInFooter()
                    ->enqueue();
            }
        }

        $enqueueBlockScript->dependencies(['give-donation-form-registrars-js'])->loadInFooter()->enqueue();
    }
}
