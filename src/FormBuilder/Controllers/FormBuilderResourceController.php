<?php

namespace Give\FormBuilder\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class FormBuilderResourceController
{
    /**
     * Get the form builder instance
     *
     * @unreleased add required block validation
     * @since 0.1.0
     *
     * @param  WP_REST_Request  $request
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function show(WP_REST_Request $request)
    {
        $formId = $request->get_param('id');

        /** @var DonationForm $form */
        $form = DonationForm::find($formId);

        if (!$form) {
            return rest_ensure_response(new WP_Error(404, 'Form not found.'));
        }

        if ($requiredFieldsError = $this->validateRequiredBlocks($form->blocks)) {
            return rest_ensure_response($requiredFieldsError);
        }

        return rest_ensure_response([
            'blocks' => $form->blocks->toJson(),
            'settings' => $form->settings->toJson()
        ]);
    }

    /**
     * Update the form builder
     *
     * @unreleased add required block validation
     * @since 0.1.0
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     * @throws Exception
     */
    public function update(WP_REST_Request $request)
    {
        $formId = $request->get_param('id');
        $formBuilderSettings = $request->get_param('settings');
        $rawBlocks = $request->get_param('blocks');

        /** @var DonationForm $form */
        $form = DonationForm::find($formId);

        if (!$form) {
            return rest_ensure_response(new WP_Error(404, __('Form not found.', 'give')));
        }

        $blocks = BlockCollection::fromJson($rawBlocks);

        if ($requiredFieldsError = $this->validateRequiredBlocks($blocks)) {
            return rest_ensure_response($requiredFieldsError);
        }

        $updatedSettings = FormSettings::fromJson($formBuilderSettings);

        $form->settings = $updatedSettings;
        $form->title = $updatedSettings->formTitle;
        $form->blocks = $blocks;

        do_action('givewp_form_builder_updated', $form);

        $form->status = $updatedSettings->formStatus;
        $form->save();

        return rest_ensure_response([
            'settings' => $form->settings->toJson(),
            'form' => $form->id,
        ]);
    }

    /**
     * @unreleased
     *
     * @return string[]
     */
    protected function getRequiredBlocks(): array
    {
        return [
            "givewp/donation-amount" => "Donation Amount",
            "givewp/donor-name" => "Donor Name",
            "givewp/email" => "Email",
            "givewp/payment-gateways" => "Payment Gateways",
        ];
    }

    /**
     * @unreleased
     *
     * @return WP_Error|void
     */
    protected function validateRequiredBlocks(BlockCollection $blocks)
    {
        $missingBlockLabels = [];

        foreach ($this->getRequiredBlocks() as $requiredBlockName => $requiredBlockLabel) {
            if (!$blocks->findByName($requiredBlockName)) {
                $missingBlockLabels[] = $requiredBlockLabel;
            }
        }

        if (!empty($missingBlockLabels)) {
            $requiredBlockLabels = implode("', '", $missingBlockLabels);

            return new WP_Error(
                404,
                __(
                    "The following required block(s) were not found: '$requiredBlockLabels'. Please add these missing block(s) and try again.",
                    'give'
                )
            );
        }
    }
}
