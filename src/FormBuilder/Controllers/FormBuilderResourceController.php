<?php

namespace Give\FormBuilder\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\FieldsAPI\Form;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class FormBuilderResourceController
{
    /**
     * Get the form builder instance
     *
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

        if ($requiredFieldsError = $this->validateRequiredFields($form->schema())) {
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

        $updatedSettings = FormSettings::fromJson($formBuilderSettings);

        $form->settings = $updatedSettings;
        $form->title = $updatedSettings->formTitle;
        $form->blocks = $blocks;

        if ($requiredFieldsError = $this->validateRequiredFields($form->schema())) {
            return rest_ensure_response($requiredFieldsError);
        }

        $form->status = $updatedSettings->formStatus;
        $form->save();

        return rest_ensure_response([
            'settings' => $form->settings->toJson(),
            'form' => $form->id,
        ]);
    }

    /**
     * @since 0.1.0
     *
     * @return string[]
     */
    protected function getRequiredFieldNames(): array
    {
        return [
            'amount',
            'name',
            'email',
            'gatewayId',
        ];
    }

    /**
     * @since 0.1.0
     *
     * @return WP_Error|void
     */
    protected function validateRequiredFields(Form $schema)
    {
        foreach ($this->getRequiredFieldNames() as $requiredFieldName) {
            if (!$schema->getNodeByName($requiredFieldName)) {
                return new WP_Error(404, __("Required field '$requiredFieldName' not found.", 'give'));
            }
        }
    }
}
