<?php

namespace Give\Tests\Unit\DonationForms\Routes;

use Give\DonationForms\Controllers\DonateController;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\DataTransferObjects\ValidationRouteData;
use Give\DonationForms\Routes\DonateRoute;
use Give\DonationForms\Routes\ValidationRoute;
use Give\DonationForms\ValueObjects\DonationFormErrorTypes;
use Give\Log\Log;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use ReflectionMethod;

/**
 * @since TBD
 */
class TestRouteErrorLog extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testDonateRouteErrorLogOmitsSubmittedFields(): void
    {
        $formData = DonateFormRouteData::fromRequest([
            'formId' => 123,
            'gatewayId' => 'manual',
            'isEmbed' => false,
            'email' => 'private-donor@example.test',
            'address1' => '123 Private Lane',
            'customField' => 'PrivateCustomValue',
        ]);

        $this->assertOnlyDiagnosticDataLogged(new DonateRoute(new DonateController()), $formData);
    }

    /**
     * @since TBD
     */
    public function testValidationRouteErrorLogOmitsSubmittedFields(): void
    {
        $formData = ValidationRouteData::fromRequest([
            'formId' => 123,
            'email' => 'private-donor@example.test',
            'address1' => '123 Private Lane',
            'customField' => 'PrivateCustomValue',
        ]);

        $this->assertOnlyDiagnosticDataLogged(new ValidationRoute(), $formData);
    }

    /**
     * @since TBD
     */
    private function assertOnlyDiagnosticDataLogged($route, $formData): void
    {
        global $wpdb;

        give()->singleton(Log::class);
        give()->forgetInstance(Log::class);

        $method = new ReflectionMethod($route, 'logError');
        $method->setAccessible(true);
        $method->invoke($route, DonationFormErrorTypes::VALIDATION, 'Form field validation error', $formData, ['email']);

        $storedData = $wpdb->get_var("SELECT data FROM {$wpdb->prefix}give_log ORDER BY id DESC LIMIT 1");
        $this->assertNotEmpty($storedData);
        $this->assertSame(
            [
                'error_type' => DonationFormErrorTypes::VALIDATION,
                'exceptionMessage' => 'Form field validation error',
                'formId' => 123,
                'invalidFields' => ['email'],
            ],
            json_decode($storedData, true)['context']
        );

        foreach (['private-donor@example.test', '123 Private Lane', 'PrivateCustomValue'] as $privateValue) {
            $this->assertStringNotContainsString($privateValue, $storedData);
        }
    }
}
