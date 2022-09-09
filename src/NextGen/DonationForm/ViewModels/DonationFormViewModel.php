<?php

namespace Give\NextGen\DonationForm\ViewModels;

use Give\NextGen\DonationForm\Actions\GenerateDonateRouteUrl;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;

/**
 * @unreleased
 */
class DonationFormViewModel
{
    /**
     * @var int
     */
    private $formId;
    /**
     * @var string
     */
    private $formTemplateId;

    /**
     * @unreleased
     */
    public function __construct(int $formId)
    {
        $this->formId = $formId;
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        /** @var DonationFormRepository $donationFormRepository */
        $donationFormRepository = give(DonationFormRepository::class);

        $donateUrl = (new GenerateDonateRouteUrl())();
        $donationForm = $donationFormRepository->createFieldsApiForm($this->formId);
        $formDataGateways = $donationFormRepository->getFormDataGateways($this->formId);

        return [
            'form' => $donationForm->jsonSerialize(),
            'donateUrl' => $donateUrl,
            'successUrl' => give_get_success_page_uri(),
            'gatewaySettings' => $formDataGateways
        ];
    }
}
