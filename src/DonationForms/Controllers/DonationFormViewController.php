<?php

namespace Give\DonationForms\Controllers;

use Give\DonationForms\DataTransferObjects\DonationFormPreviewRouteData;
use Give\DonationForms\DataTransferObjects\DonationFormViewRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Routes\Permissions\DonationFormPermissions;
use Give\DonationForms\ViewModels\DonationFormViewModel;

class DonationFormViewController
{
    /**
     * This renders the donation form view.
     *
     * @since TBD Require a published form unless the current user can edit forms.
     * @since 4.14.3 Prevent triggering a fatal error due to form not found.
     * @since 3.0.0
     */
    public function show(DonationFormViewRouteData $data): string
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($data->formId);

        if (!$donationForm || !$this->canView($donationForm)) {
            wp_die(
                esc_html__('Donation form not found.', 'give'),
                esc_html__('Not Found', 'give'),
                ['response' => 404]
            );
        }

        $viewModel = new DonationFormViewModel(
            $donationForm->id,
            $donationForm->blocks,
            $donationForm->settings
        );

        ob_clean();
        return $viewModel->render();
    }

    /**
     * This renders the donation form preview
     *
     * @since TBD Require a published form unless the current user can edit forms.
     * @since 4.14.3 Prevent triggering a fatal error due to form not found.
     * @since 3.0.0
     */
    public function preview(DonationFormPreviewRouteData $data): string
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($data->formId);

        if (!$donationForm || !$this->canView($donationForm)) {
            wp_die(
                esc_html__('Donation form not found.', 'give'),
                esc_html__('Not Found', 'give'),
                ['response' => 404]
            );
        }

        $viewModel = new DonationFormViewModel(
            $donationForm->id,
            $data->formBlocks ?: $donationForm->blocks,
            $data->formSettings ?: $donationForm->settings,
            true
        );

        ob_clean();
        return $viewModel->render();
    }

    /**
     * Whether the current request may render this form.
     *
     * @since TBD
     */
    private function canView(DonationForm $donationForm): bool
    {
        return $donationForm->status->isPublished() || DonationFormPermissions::canViewPrivate();
    }
}
