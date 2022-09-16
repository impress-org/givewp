<?php

namespace TestsNextGen\Feature\Controllers;

use Give\FormBuilder\Controllers\FormBuilderResourceController;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;
use TestsNextGen\TestTraits\HasMockForm;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class FormBuilderResourceControllerTest extends TestCase
{
    use RefreshDatabase;
    use HasMockForm;

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShowShouldReturnFormBuilderData()
    {
        $mockForm = $this->createMockForm();

        $mockRequest = $this->getMockRequest(WP_REST_Server::READABLE);

        $mockRequest->set_param('id', $mockForm->id);

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->show($mockRequest);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $this->assertSame($response->data, [
            // we just need to compare the json stringified representation of the data so need to remove escaping from encode
            'blocks' => json_encode($mockForm->data, JSON_UNESCAPED_SLASHES),
            'settings' => json_encode($mockForm->settings)
        ]);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testUpdateShouldReturnUpdatedFormBuilderData()
    {
        $mockForm = $this->createMockForm();

        $mockRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);

        $updatedSettings = array_merge($mockForm->settings, ['formTitle' => 'Updated Next Gen Form Builder Title']);

        $mockRequest->set_param('id', $mockForm->id);
        $mockRequest->set_param('settings', json_encode($updatedSettings, JSON_UNESCAPED_SLASHES));
        $mockRequest->set_param('blocks', json_encode($mockForm->data, JSON_UNESCAPED_SLASHES));

        $formBuilderResourceController = new FormBuilderResourceController();

        $response = $formBuilderResourceController->update($mockRequest);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $this->assertSame($response->data, [
            'settings' => true,
            'form' => $mockForm->id,
        ]);

        $this->assertSame(json_encode($updatedSettings, JSON_UNESCAPED_SLASHES), get_post_meta($mockForm->id, 'formBuilderSettings', true));
    }

    /**
     * @unreleased
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
     * @unreleased
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
     */
    public function testUpdateShouldFailIfBlockDataIsInvalid()
    {
        $this->markTestIncomplete();
    }


    /**
     *
     * @unreleased
     */
    public function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            '/wp/v2/' . FormBuilderRestRouteConfig::PATH
        );
    }
}

