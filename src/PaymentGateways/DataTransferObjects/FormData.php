<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\ValueObjects\Address;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

/**
 * Class FormData
 * @since 2.18.0
 */
final class FormData
{
    /**
     * @var float
     */
    public $price;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var string
     */
    public $date;
    /**
     * @var string
     */
    public $purchaseKey;
    /**
     * @var string
     */
    public $currency;
    /**
     * @var array
     */
    public $userInfo;
    /**
     * @var string
     */
    public $paymentGateway;
    /**
     * @var string
     */
    public $gatewayNonce;
    /**
     * @var array
     */
    public $postData;
    /**
     * @var CardInfo
     */
    public $cardInfo;
    /**
     * @var int
     */
    public $formId;
    /**
     * @var string
     */
    public $priceId;
    /**
     * @var string
     */
    public $formIdPrefix;
    /**
     * @var string
     */
    public $currentUrl;
    /**
     * @var string
     */
    public $formMinimum;
    /**
     * @var string
     */
    public $formMaximum;
    /**
     * @var string
     */
    public $formHash;
    /**
     * @var string
     */
    public $amount;
    /**
     * @var string
     */
    public $userId;
    /**
     * @var Address
     */
    public $billingAddress;
    /**
     * @var DonorInfo
     */
    public $donorInfo;

    /** @var bool */
    public $anonymous;
    /**
     * @var string|null
     */
    public $company;

    /**
     * Convert data from request into DTO
     *
     * @since 2.22.0 add support for company field
     * @since 2.18.0
     */
    public static function fromRequest(array $request): FormData
    {
        $self = new static();

        $self->price = $request['price'];
        $self->date = $request['date'];
        $self->purchaseKey = $request['purchase_key'];
        $self->currency = give_get_currency($request['post_data']['give-form-id'], $request);
        $self->userInfo = $request['user_info'];
        $self->postData = $request['post_data'];
        $self->formTitle = $request['post_data']['give-form-title'];
        $self->formId = (int)$request['post_data']['give-form-id'];
        $self->priceId = $request['post_data']['give-price-id'] ?? '';
        $self->formIdPrefix = $request['post_data']['give-form-id-prefix'];
        $self->currentUrl = $request['post_data']['give-current-url'];
        $self->formMinimum = $request['post_data']['give-form-minimum'];
        $self->formMaximum = $request['post_data']['give-form-maximum'];
        $self->formHash = $request['post_data']['give-form-hash'];
        $self->amount = $request['post_data']['give-amount'];
        $self->paymentGateway = $request['post_data']['give-gateway'];
        $self->gatewayNonce = $request['gateway_nonce'];
        $self->donorInfo = DonorInfo::fromArray([
            'wpUserId' => $request['user_info']['id'],
            'firstName' => $request['user_info']['first_name'],
            'lastName' => $request['user_info']['last_name'],
            'email' => $request['user_info']['email'],
            'honorific' => ! empty($request['user_info']['title']) ? $request['user_info']['title'] : '',
            'address' => $request['user_info']['address'],
        ]);
        $self->cardInfo = CardInfo::fromArray([
            'name' => $request['card_info']['card_name'],
            'cvc' => $request['card_info']['card_cvc'],
            'expMonth' => $request['card_info']['card_exp_month'],
            'expYear' => $request['card_info']['card_exp_year'],
            'number' => $request['card_info']['card_number'],
        ]);
        $self->billingAddress = Address::fromArray([
            'line1' => $request['card_info']['card_address'],
            'line2' => $request['card_info']['card_address_2'],
            'city' => $request['card_info']['card_city'],
            'state' => $request['card_info']['card_state'],
            'country' => $request['card_info']['card_country'],
            'postalCode' => $request['card_info']['card_zip'],
        ]);

        $self->anonymous = isset($request['post_data']['give_anonymous_donation']) && (bool)absint(
                $request['post_data']['give_anonymous_donation']
            );

        $self->company = !empty($request['post_data']['give_company_name']) ? $request['post_data']['give_company_name'] : null;

        return $self;
    }

    /**
     * @since 3.2.0 added support for honorific field
     * @since 2.22.0 add support for company field
     * @since 2.19.6
     * @throws Exception
     */
    public function toDonation($donorId): Donation
    {
        $donation = new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->paymentGateway,
            'amount' => Money::fromDecimal($this->price, $this->currency),
            'donorId' => $donorId,
            'honorific' => $this->donorInfo->honorific,
            'firstName' => $this->donorInfo->firstName,
            'lastName' => $this->donorInfo->lastName,
            'email' => $this->donorInfo->email,
            'formId' => $this->formId,
            'formTitle' => $this->formTitle,
            'billingAddress' => BillingAddress::fromArray([
                'country' => $this->billingAddress->country,
                'city' => $this->billingAddress->city,
                'state' => $this->billingAddress->state,
                'zip' => $this->billingAddress->postalCode,
                'address1' => $this->billingAddress->line1,
                'address2' => $this->billingAddress->line2,
            ]),
            'levelId' => $this->priceId,
            'anonymous' => $this->anonymous,
            'company' => $this->company
        ]);

        /**
         * Since 2018, we have been updating the donor's company field based on their donation.
         * The company in donation meta never changes, but the company in donor meta gets updated based on the most recent donation in which that donor supplied a company.
         *
         * @see https://github.com/impress-org/givewp/issues/2453#issuecomment-373103211
         */
        if ($donation->company) {
            give()->donor_meta->update_meta($donorId, '_give_donor_company', $donation->company);
        }

        return $donation;
    }
}
