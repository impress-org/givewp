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
use Give\DonationForms\ValueObjects\GoalSource;
use Give\DonationForms\ValueObjects\GoalType;
use Give\DonationForms\ViewModels\DonationFormViewModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonationFormViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD build the signed URLs and the exports in the same second
     * @since 3.6.0 added includeHeaderInMultiStep to form design exports
     * @since 3.0.0
     */
    public function testExportsShouldReturnExpectedArrayOfData()
    {
        $formDesign = new ClassicFormDesign();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray([
                'designId' => $formDesign::id(),
                'goalSource' => GoalSource::FORM(),
            ]),
        ]);

        $donationFormRepository = give(DonationFormRepository::class);

        $donationFormGoalData = new DonationFormGoalData($donationForm->id, $donationForm->settings);
        $totalRevenue = $donationFormRepository->getTotalRevenue($donationForm->id);
        $goalType = $donationForm->settings->goalType ?? GoalType::AMOUNT();
        $formDataGateways = $donationFormRepository->getFormDataGateways($donationForm->id);
        $formApi = $donationFormRepository->getFormSchemaFromBlocks(
            $donationForm->id,
            $donationForm->blocks
        );

        $viewModel = new DonationFormViewModel($donationForm->id, $donationForm->blocks, $donationForm->settings);

        // The donate, validate and auth URLs are signed with an expiration one day from the current second.
        // Build them and the exports within the same second, or the signatures differ and the test flakes.
        do {
            $second = time();
            $donateUrl = (new GenerateDonateRouteUrl())();
            $validateUrl = (new GenerateDonationFormValidationRouteUrl())();
            $authUrl = (new GenerateAuthUrl())();
            $exports = $viewModel->exports();
        } while (time() !== $second);

        $this->assertEquals([
            'donateUrl' => $donateUrl,
            'validateUrl' => $validateUrl,
            'authUrl' => $authUrl,
            'inlineRedirectRoutes' => [
                'donation-confirmation-receipt-view',
            ],
            'registeredGateways' => $formDataGateways,
            'form' => array_merge($formApi->jsonSerialize(), [
                'settings' => $donationForm->settings,
                'currency' => $formApi->getDefaultCurrency(),
                'goal' => $donationForm->settings->showHeader && $donationForm->settings->enableDonationGoal
                    ? $donationFormGoalData->toArray()
                    : [],
                'stats' => $donationForm->settings->showHeader && $donationForm->settings->enableDonationGoal
                    ? [
                        'totalRevenue' => $totalRevenue,
                        'totalCountValue' => $goalType->isDonors() ?
                            $donationFormRepository->getTotalNumberOfDonors($donationForm->id) :
                            $donationFormRepository->getTotalNumberOfDonations($donationForm->id),
                        'totalCountLabel' => $goalType->isDonors() ? __('donors', 'give') : __(
                            'Donations',
                            'give'
                        ),
                    ] : [],
                'design' => [
                    'id' => $formDesign::id(),
                    'name' => $formDesign::name(),
                    'isMultiStep' => $formDesign->isMultiStep(),
                    'includeHeaderInMultiStep' => $formDesign->shouldIncludeHeaderInMultiStep(),
                ],
            ]),
            'previewMode' => false,
        ], $exports);
    }

    /**
     * @since 4.15.0
     */
    public function testPrimaryColorAccessorSanitizesMaliciousValue()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray([]),
        ]);

        $donationForm->settings->primaryColor = 'red;</style><script>alert(1)</script>';

        $viewModel = new DonationFormViewModel($donationForm->id, $donationForm->blocks, $donationForm->settings);

        $this->assertSame('', $viewModel->primaryColor());
    }

    /**
     * @since 4.15.0
     */
    public function testSecondaryColorAccessorSanitizesMaliciousValue()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray([]),
        ]);

        $donationForm->settings->secondaryColor = 'blue;</style><script>alert(1)</script>';

        $viewModel = new DonationFormViewModel($donationForm->id, $donationForm->blocks, $donationForm->settings);

        $this->assertSame('', $viewModel->secondaryColor());
    }

    /**
     * @since 4.15.0
     */
    public function testPrimaryColorAccessorPreservesValidHex()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray(['primaryColor' => '#123abc']),
        ]);

        $viewModel = new DonationFormViewModel($donationForm->id, $donationForm->blocks, $donationForm->settings);

        $this->assertSame('#123abc', $viewModel->primaryColor());
    }

    /**
     * @since 4.15.0
     */
    public function testSecondaryColorAccessorPreservesValidHex()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray(['secondaryColor' => '#abc']),
        ]);

        $viewModel = new DonationFormViewModel($donationForm->id, $donationForm->blocks, $donationForm->settings);

        $this->assertSame('#abc', $viewModel->secondaryColor());
    }
}
