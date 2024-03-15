<?php

namespace Give\Tests\Unit\DonationForms\VieModels;

use Give\DonationForms\Actions\GenerateAuthUrl;
use Give\DonationForms\Actions\GenerateDonateRouteUrl;
use Give\DonationForms\Actions\GenerateDonationFormValidationRouteUrl;
use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\ValueObjects\GoalType;
use Give\DonationForms\ViewModels\DonationFormViewModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonationFormViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0 added includeHeaderInMultiStep to form design exports
     * @since 3.0.0
     */
    public function testExportsShouldReturnExpectedArrayOfData()
    {
        $formDesign = new ClassicFormDesign();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray(['designId' => $formDesign::id()]),
        ]);

        $donationFormRepository = give(DonationFormRepository::class);

        $donationFormGoalData = new DonationFormGoalData($donationForm->id, $donationForm->settings);
        $totalRevenue = $donationFormRepository->getTotalRevenue($donationForm->id);
        $goalType = $donationForm->settings->goalType ?? GoalType::AMOUNT();
        $donateUrl = (new GenerateDonateRouteUrl())();
        $validateUrl = (new GenerateDonationFormValidationRouteUrl())();
        $authUrl = (new GenerateAuthUrl())();
        $formDataGateways = $donationFormRepository->getFormDataGateways($donationForm->id);
        $formApi = $donationFormRepository->getFormSchemaFromBlocks(
            $donationForm->id,
            $donationForm->blocks
        );

        $viewModel = new DonationFormViewModel($donationForm->id, $donationForm->blocks, $donationForm->settings);

        $this->assertEquals($viewModel->exports(), [
            'donateUrl' => $donateUrl,
            'validateUrl' => $validateUrl,
            'authUrl' => $authUrl,
            'inlineRedirectRoutes' => [
                'donation-confirmation-receipt-view'
            ],
            'registeredGateways' => $formDataGateways,
            'form' => array_merge($formApi->jsonSerialize(), [
                'settings' => $donationForm->settings,
                'currency' => $formApi->getDefaultCurrency(),
                'goal' => $donationFormGoalData->toArray(),
                'stats' => [
                    'totalRevenue' => $totalRevenue,
                    'totalCountValue' => $goalType->isDonors() ?
                        $donationFormRepository->getTotalNumberOfDonors($donationForm->id) :
                        $donationFormRepository->getTotalNumberOfDonations($donationForm->id),
                    'totalCountLabel' => $goalType->isDonors() ? __('donors', 'give') : __(
                        'Donations',
                        'give'
                    ),
                ],
                'design' => [
                    'id' => $formDesign::id(),
                    'name' => $formDesign::name(),
                    'isMultiStep' => $formDesign->isMultiStep(),
                    'includeHeaderInMultiStep' => $formDesign->shouldIncludeHeaderInMultiStep(),
                ],
            ]),
            'previewMode' => false
        ]);
    }
}
