<?php

namespace Give\NextGen\DonationForm\ViewModels;

use Give\NextGen\DonationForm\Actions\GenerateDonateRouteUrl;
use Give\NextGen\DonationForm\DataTransferObjects\DonationFormGoalData;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ValueObjects\GoalTypeOptions;
use Give\NextGen\Framework\Blocks\BlockCollection;

/**
 * @unreleased
 */
class DonationFormViewModel
{
    /**
     * @var int
     */
    private $donationFormId;
    /**
     * @var BlockCollection
     */
    private $formBlocks;
    /**
     * TODO: replace formSettings array with $donationForm->settings object when property gets updated
     *
     * @var array{designId: string, primaryColor: string, secondaryColor: string, goalType: string}
     */
    private $formSettings;
    /**
     * @var DonationFormRepository
     */
    private $donationFormRepository;

    /**
     * @unreleased
     */
    public function __construct(
        int $donationFormId,
        BlockCollection $formBlocks,
        array $formSettings = []
    ) {
        $this->donationFormId = $donationFormId;
        $this->formBlocks = $formBlocks;
        $this->formSettings = $formSettings;
        $this->donationFormRepository = give(DonationFormRepository::class);
    }

    /**
     * @unreleased
     */
    public function designId(): string
    {
        return $this->formSettings['designId'] ?? '';
    }

    /**
     * @unreleased
     */
    public function primaryColor(): string
    {
        return $this->formSettings['primaryColor'] ?? '';
    }

    /**
     * @unreleased
     */
    public function secondaryColor(): string
    {
        return $this->formSettings['secondaryColor'] ?? '';
    }

    /**
     * @unreleased
     */
    private function goalType(): GoalTypeOptions
    {
        return new GoalTypeOptions($this->formSettings['goalType'] ?? GoalTypeOptions::AMOUNT);
    }

    /**
     * @unreleased
     */
    private function formStatsData(): array
    {
        $totalRevenue = $this->donationFormRepository->getTotalRevenue($this->donationFormId);
        $goalType = $this->goalType();

        return [
            'totalRevenue' => $totalRevenue,
            'totalCountValue' => $goalType->isDonors() ?
                $this->donationFormRepository->getTotalNumberOfDonors($this->donationFormId) :
                $this->donationFormRepository->getTotalNumberOfDonations($this->donationFormId),
            'totalCountLabel' => $goalType->isDonors() ? __('donors', 'give') : __(
                'donations',
                'give'
            ),
        ];
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $donateUrl = (new GenerateDonateRouteUrl())();
        $donationFormGoalData = new DonationFormGoalData($this->donationFormId, $this->formSettings);

        $formDataGateways = $this->donationFormRepository->getFormDataGateways($this->donationFormId);
        $formApi = $this->donationFormRepository->getFormSchemaFromBlocks(
            $this->donationFormId,
            $this->formBlocks
        )->jsonSerialize();

        return [
            'donateUrl' => $donateUrl,
            'successUrl' => give_get_success_page_uri(),
            'gatewaySettings' => $formDataGateways,
            'form' => array_merge($formApi, [
                'settings' => $this->formSettings,
                'currency' => give_get_currency(),
                'goal' => $donationFormGoalData->toArray(),
                'stats' => $this->formStatsData()
            ]),
        ];
    }
}
