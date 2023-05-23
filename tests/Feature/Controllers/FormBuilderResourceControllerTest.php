<?php

namespace Give\Tests\Feature\Controllers;

use Exception;
use Give\FormBuilder\Controllers\FormBuilderResourceController;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\ValueObjects\GoalType;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class FormBuilderResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 0.1.0
     *
     * @return void
     * @throws Exception
     */
    public function testShowShouldReturnFormBuilderData()
    {
        /** @var DonationForm $mockForm */
        $mockForm = DonationForm::factory()->create();

        $mockRequest = $this->getMockRequest(WP_REST_Server::READABLE);

        $mockRequest->set_param('id', $mockForm->id);

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->show($mockRequest);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $this->assertSame($response->data, [
            // we just need to compare the json stringified representation of the data so need to remove escaping from encode
            'blocks' => $mockForm->blocks->toJson(),
            'settings' => $mockForm->settings->toJson()
        ]);
    }

    /**
     * @since 0.1.0
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateShouldReturnUpdatedFormBuilderData()
    {
        /** @var DonationForm $mockForm */
        $mockForm = DonationForm::factory()->create();

        $mockRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);

        $updatedSettings = $mockForm->settings;
        $updatedSettings->formTitle = 'Updated Next Gen Form Builder Title';

        $mockRequest->set_param('id', $mockForm->id);
        $mockRequest->set_param('settings', $updatedSettings->toJson());
        $mockRequest->set_param('blocks', $mockForm->blocks->toJson());

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->update($mockRequest);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $this->assertSame($response->data, [
            'settings' => $updatedSettings->toJson(),
            'form' => $mockForm->id,
        ]);

        $this->assertSame($updatedSettings->toJson(), get_post_meta($mockForm->id, 'formBuilderSettings', true));
    }

    /**
     * @since 0.1.0
     */
    public function testUpdateShouldFailIfFormDoesNotExist()
    {
        $mockRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);

        $mockRequest->set_param('id', 99);

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->update($mockRequest);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame(404, $response->get_error_code());
    }

    /**
     * @since 0.1.0
     */
    public function testShowShouldFailIfFormDoesNotExist()
    {
        $mockRequest = $this->getMockRequest(WP_REST_Server::READABLE);

        $mockRequest->set_param('id', 99);

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->show($mockRequest);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame(404, $response->get_error_code());
    }

    /**
     * @return void
     */
    public function testUpdateShouldFailIfSettingsAreInvalid()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testUpdateShouldFailIfBlockDataIsInvalid()
    {
        $blockCollectionWithoutAmountField = BlockCollection::make([
            BlockModel::make([
                'name' => 'givewp/section',
                'attributes' => ['title' => '', 'description' => ''],
                'innerBlocks' => [
                    /* @note The `donation-amount` block is intentionally omitted for this test. */
                    [
                        'name' => 'givewp/donor-name',
                        'attributes' => [
                            'firstNameLabel' => 'First Name',
                            'firstNamePlaceholder' => '',
                            'lastNameLabel' => 'Last Name',
                            'lastNamePlaceholder' => '',
                            'requireLastName' => true,
                        ]
                    ],
                    ['name' => 'givewp/email'],
                    ['name' => 'givewp/payment-gateways'],
                ]
            ]),
        ]);

        $form = DonationForm::factory()->create();

        $mockRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);

        $mockRequest->set_param('id', $form->id);
        $mockRequest->set_param(
            'settings',
            json_encode([
                'formTitle' => 'Form Title',
                'enableDonationGoal' => false,
                'enableAutoClose' => false,
                'registration' => 'none',
                'goalType' => GoalType::AMOUNT,
            ])
        );
        $mockRequest->set_param('blocks', json_encode($blockCollectionWithoutAmountField));

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->update($mockRequest);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame(404, $response->get_error_code());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShowShouldFailIfBlockDataIsInvalid()
    {
        $blockCollectionWithoutAmountField = BlockCollection::make([
            BlockModel::make([
                'name' => 'givewp/section',
                'attributes' => ['title' => '', 'description' => ''],
                'innerBlocks' => [
                    /* @note The `donation-amount` block is intentionally omitted for this test. */
                    [
                        'name' => 'givewp/donor-name',
                        'attributes' => [
                            'firstNameLabel' => 'First Name',
                            'firstNamePlaceholder' => '',
                            'lastNameLabel' => 'Last Name',
                            'lastNamePlaceholder' => '',
                            'requireLastName' => true,
                        ]
                    ],
                    ['name' => 'givewp/email'],
                    ['name' => 'givewp/payment-gateways'],
                ]
            ]),
        ]);

        $form = DonationForm::factory()->create(['blocks' => $blockCollectionWithoutAmountField]);

        $mockRequest = $this->getMockRequest(WP_REST_Server::READABLE);

        $mockRequest->set_param('id', $form->id);

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->show($mockRequest);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame(404, $response->get_error_code());
    }


    /**
     *
     * @since 0.1.0
     */
    public function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            '/wp/v2/' . FormBuilderRestRouteConfig::PATH
        );
    }
}

