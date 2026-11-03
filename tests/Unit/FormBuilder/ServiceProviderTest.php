<?php

namespace Give\Tests\Unit\FormBuilder;

use Give\DonationForms\Models\DonationForm;
use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WPDieException;

/**
 * @since 3.16.2
 * @since 4.19.1 Send the form builder nonce and log in as an admin, and cover the refused requests.
 */
class ServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.19.1
     */
    public function setUp(): void
    {
        parent::setUp();

        // check_ajax_referer() and wp_die() only reach the test handler in an AJAX request. The handler
        // is also set for the ajax die handler because DOING_AJAX, once defined by another test, stays set.
        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);
    }

    /**
     * @since 4.19.1
     */
    public function tearDown(): void
    {
        unset($_REQUEST['_ajax_nonce'], $_GET['formId'], $_POST['mode']);

        parent::tearDown();
    }

    /**
     * @since 3.16.2
     * @since 4.19.1 Send the form builder nonce and log in as an admin.
     */
    public function testItDismissesTheAdditionalPaymentGatewaysNotice()
    {
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $_REQUEST['_ajax_nonce'] = wp_create_nonce(FormBuilderRouteBuilder::AJAX_NONCE_ACTION);

        do_action('wp_ajax_givewp_additional_payment_gateways_hide_notice');

        $this->assertTrue(
            (bool) get_user_meta($userId, 'givewp-additional-payment-gateways-notice-dismissed', true)
        );
    }

    /**
     * @since 4.19.1
     */
    public function testItDismissesTheGoalNotice()
    {
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $_REQUEST['_ajax_nonce'] = wp_create_nonce(FormBuilderRouteBuilder::AJAX_NONCE_ACTION);

        do_action('wp_ajax_givewp_goal_hide_notice');

        $this->assertTrue((bool) get_user_meta($userId, 'givewp-goal-notice-dismissed', true));
    }

    /**
     * @since 4.19.1
     */
    public function testItCompletesTheTour()
    {
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $_REQUEST['_ajax_nonce'] = wp_create_nonce(FormBuilderRouteBuilder::AJAX_NONCE_ACTION);
        $_POST['mode'] = 'design';

        do_action('wp_ajax_givewp_tour_completed');

        $this->assertNotEmpty(get_user_meta($userId, 'givewp-form-builder-design-tour-completed', true));
    }

    /**
     * @since 4.19.1
     *
     * @dataProvider formNoticeProvider
     */
    public function testItDismissesTheFormNotices(string $action, string $metaKey)
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
        $_REQUEST['_ajax_nonce'] = wp_create_nonce(FormBuilderRouteBuilder::AJAX_NONCE_ACTION);
        $formId = DonationForm::factory()->create()->id;
        $_GET['formId'] = (string)$formId;

        do_action($action);

        $this->assertNotEmpty(give_get_meta($formId, $metaKey, true));
    }

    /**
     * @since 4.19.1
     *
     * @dataProvider handlerProvider
     */
    public function testItRefusesARequestWithoutANonce(string $action)
    {
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);

        $this->assertRefused($action);
        $this->assertNothingWasSaved($userId);
    }

    /**
     * @since 4.19.1
     *
     * @dataProvider handlerProvider
     */
    public function testItRefusesARequestWithAnInvalidNonce(string $action)
    {
        $userId = $this->factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $_REQUEST['_ajax_nonce'] = 'invalid';

        $this->assertRefused($action);
        $this->assertNothingWasSaved($userId);
    }

    /**
     * @since 4.19.1
     *
     * @dataProvider handlerProvider
     */
    public function testItRefusesAUserWhoCannotEditForms(string $action)
    {
        $userId = $this->factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);
        $_REQUEST['_ajax_nonce'] = wp_create_nonce(FormBuilderRouteBuilder::AJAX_NONCE_ACTION);

        $this->assertRefused($action);
        $this->assertNothingWasSaved($userId);
    }

    /**
     * @since 4.19.1
     */
    public function formNoticeProvider(): array
    {
        return [
            'migration' => ['wp_ajax_givewp_migration_hide_notice', 'givewp-form-builder-migration-hide-notice'],
            'transfer' => ['wp_ajax_givewp_transfer_hide_notice', 'givewp-form-builder-transfer-hide-notice'],
        ];
    }

    /**
     * @since 4.19.1
     */
    public function handlerProvider(): array
    {
        return [
            'tour' => ['wp_ajax_givewp_tour_completed'],
            'migration notice' => ['wp_ajax_givewp_migration_hide_notice'],
            'transfer notice' => ['wp_ajax_givewp_transfer_hide_notice'],
            'goal notice' => ['wp_ajax_givewp_goal_hide_notice'],
            'additional payment gateways notice' => ['wp_ajax_givewp_additional_payment_gateways_hide_notice'],
        ];
    }

    /**
     * Fires the handler and expects it to stop with the "-1" response.
     *
     * @since 4.19.1
     */
    private function assertRefused(string $action): void
    {
        try {
            do_action($action);
            $this->fail("The $action handler must stop an unauthorized request.");
        } catch (WPDieException $exception) {
            $this->assertSame('-1', $exception->getMessage());
        }
    }

    /**
     * @since 4.19.1
     */
    private function assertNothingWasSaved(int $userId): void
    {
        $this->assertEmpty(get_user_meta($userId, 'givewp-goal-notice-dismissed', true));
        $this->assertEmpty(get_user_meta($userId, 'givewp-additional-payment-gateways-notice-dismissed', true));
        $this->assertEmpty(get_user_meta($userId, 'givewp-form-builder-design-tour-completed', true));
    }
}
