<?php

namespace Give\FormBuilder\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class FormBuilderResourceController
{
    /**
     * Get the form builder instance
     *
     * @since 3.0.0
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
     * @since 3.0.0
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
        $updatedSettings->formTitle = wp_strip_all_tags($updatedSettings->formTitle);

        $form->settings = $updatedSettings;
        $form->title = $updatedSettings->formTitle;
        $form->blocks = $blocks;

        try {
            $form->schema();
        } catch (NameCollisionException $e) {
            return rest_ensure_response(
                new WP_Error(
                    400,
                    sprintf(
                        __("ERROR: the form was not saved due to a meta key name conflict. A field already exists on this form with the meta key '%s'. Meta key names must be unique. Change the conflicting meta key and try to save again. ", 'give'),
                        $e->getNodeNameCollision()
                    )
                )
            );
        }

        $form->status = $updatedSettings->formStatus;
        $form->save();

        /**
         * @since 3.16.0 Add the request as an additional parameter.
         * @since 3.0.0
         */
        do_action('givewp_form_builder_updated', $form, $request);

        return rest_ensure_response([
            'settings' => $form->settings->toJson(),
            'form' => $form->id,
        ]);
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
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
            $requiredBlockList = implode(
                '',
                array_map(static function ($label) {
                    return "<li>$label</li>";
                }, $missingBlockLabels)
            );

            // return a WP_Error with a list of missing blocks using __() and sprintf()
            return new WP_Error(
                400,
                sprintf(
                    _n(
                        "<p>The following block was not found and is required for the form to work:</p>%s<p>Please add the missing block and try again.</p>",
                        "<p>The following blocks were not found and are required for the form to work:</p>%s<p>Please add these missing blocks and try again.</p>",
                        count($missingBlockLabels),
                        'give'
                    ),
                    "<ul>$requiredBlockList</ul>"
                )
            );
        }
    }
}
