<?php

namespace Give\NextGen\DonationForm\Blocks\DonationFormBlock;

use Give\Framework\EnqueueScript;
use Give\Framework\FieldsAPI\Email;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Hidden;
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
        register_block_type_from_metadata(
            __DIR__,
            ['render_callback' => [$this, 'render']]
        );
    }

    /**
     * @unreleased
     *
     * @param  array  $attributes
     *
     * @return string
     * @throws EmptyNameException
     */
    public function render($attributes)
    {
        $donationForm = $this->createForm($attributes);

        $donateUrl = Call::invoke(GenerateDonateRouteUrl::class);

        $exports = [
            'attributes' => $attributes,
            'form' => $donationForm->jsonSerialize(),
            'donateUrl' => $donateUrl,
        ];

        // enqueue front-end scripts
        // since this is using render_callback viewScript in blocks.json will not work.
        $enqueueScripts = new EnqueueScript(
            'give-next-gen-donation-form-block-js',
            'src/NextGen/DonationForm/Blocks/DonationFormBlock/build/view.js',
            GIVE_NEXT_GEN_DIR,
            GIVE_NEXT_GEN_URL,
            'give'
        );

        $enqueueScripts->loadInFooter()->enqueue();

        ob_start(); ?>

        <div id="root-give-next-gen-donation-form-block"></div>

        <script>window.giveNextGenExports = <?= wp_json_encode($exports) ?>;</script>

        <?php
        return ob_get_clean();
    }

    /**
     * @unreleased
     *
     * @param  array  $attributes
     * @return Form
     * @throws EmptyNameException
     */
    private function createForm($attributes)
    {
        $gatewayFields = [];
        foreach ($this->paymentGatewayRegister->getPaymentGateways() as $registeredGateway) {
            /** @var PaymentGateway $gateway */
            $gateway = give($registeredGateway);

            if (method_exists($gateway, 'getPaymentFields')) {
                $gatewayFields[] = Group::make($gateway->getId())
                    ->label($gateway->getPaymentMethodLabel())
                    ->append($gateway->getPaymentFields());
            }
        }

        $donationForm = new Form('DonationForm');

        $donationForm->append(
            Group::make('donationDetails')
                ->label(__('Donation Details', 'give'))
                ->append(
                    Text::make('amount')
                        ->label(__('Donation Amount', 'give'))
                        ->defaultValue(50)
                        ->required()
                ),

            Group::make('donorDetails')
                ->label(__('Donor Details', 'give'))
                ->append(
                    Text::make('firstName')
                        ->label(__('First Name', 'give'))
                        ->required(),

                    Text::make('lastName')
                        ->label(__('Last Name', 'give'))
                        ->required(),

                    Email::make('email')
                        ->label(__('Email', 'give'))
                        ->required()
                        ->emailTag('email')
                ),

            Group::make('paymentDetails')
                ->label(__('Payment Details', 'give'))
                ->append(...$gatewayFields),

            Hidden::make('formId')
                ->defaultValue($attributes['formId']),

            Hidden::make('formTitle')
                ->defaultValue('Give Next Gen Form'),

            Hidden::make('userId')
                ->defaultValue(get_current_user_id()),

            Hidden::make('currency')
                ->defaultValue("USD")
        );

        return $donationForm;
    }
}
