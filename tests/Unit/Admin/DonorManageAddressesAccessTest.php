<?php

namespace Give\Tests\Unit\Admin;

use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Donor;
use WPDieException;

/**
 * The donor_manage_addresses AJAX handler must only change a donor's addresses for a user who holds
 * the donor-edit capability and sends a nonce issued for that donor.
 *
 * @since 4.19.0.1
 *
 * @covers ::give_ajax_donor_manage_addresses
 */
final class DonorManageAddressesAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Donor
     */
    private $donor;

    /**
     * @var Donor
     */
    private $otherDonor;

    /**
     * @var int
     */
    private $viewOnlyUserId;

    /**
     * @var int
     */
    private $editorUserId;

    /**
     * @since 4.19.0.1
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!function_exists('give_ajax_donor_manage_addresses')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
        }

        if (!function_exists('give_get_format_address')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/donors/donors.php';
        }

        // wp_send_json_* calls wp_die() only when wp_doing_ajax() is true.
        add_filter('wp_doing_ajax', '__return_true');

        // Priority 11 so give_die()'s priority-10 handlers are overridden and wp_die() stays catchable.
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_json_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_handler', [$this, 'get_wp_die_handler'], 11);

        add_role('give_test_donor_viewer', 'Donor Viewer', ['read' => true, 'view_give_reports' => true]);
        add_role('give_test_donor_editor', 'Donor Editor', [
            'read' => true,
            'view_give_reports' => true,
            'edit_give_payments' => true,
        ]);

        $this->viewOnlyUserId = self::factory()->user->create(['role' => 'give_test_donor_viewer']);
        $this->editorUserId = self::factory()->user->create(['role' => 'give_test_donor_editor']);

        $this->donor = Donor::factory()->create(['addresses' => []]);
        $this->otherDonor = Donor::factory()->create(['addresses' => []]);
    }

    /**
     * @since 4.19.0.1
     */
    public function tearDown(): void
    {
        remove_role('give_test_donor_viewer');
        remove_role('give_test_donor_editor');
        $_POST = [];

        parent::tearDown();
    }

    /**
     * @since 4.19.0.1
     */
    public function testViewOnlyUserCannotAddAddress(): void
    {
        wp_set_current_user($this->viewOnlyUserId);

        $response = $this->callHandler($this->donor->id, $this->nonceFor($this->donor->id), 'add', 'billing');

        $this->assertFalse($response['success']);
        $this->assertEmpty($this->billingAddresses($this->donor->id));
    }

    /**
     * @since 4.19.0.1
     */
    public function testViewOnlyUserCannotUpdateAddress(): void
    {
        $original = $this->seedBillingAddress($this->donor->id);
        wp_set_current_user($this->viewOnlyUserId);

        $response = $this->callHandler(
            $this->donor->id,
            $this->nonceFor($this->donor->id),
            'update',
            'billing_0',
            ['line1' => 'Changed ' . $original['line1']]
        );

        $this->assertFalse($response['success']);
        $this->assertSame($original['line1'], $this->billingAddresses($this->donor->id)[0]['line1']);
    }

    /**
     * @since 4.19.0.1
     */
    public function testViewOnlyUserCannotRemoveAddress(): void
    {
        $original = $this->seedBillingAddress($this->donor->id);
        wp_set_current_user($this->viewOnlyUserId);

        $response = $this->callHandler($this->donor->id, $this->nonceFor($this->donor->id), 'remove', 'billing_0');

        $this->assertFalse($response['success']);
        $this->assertSame($original['line1'], $this->billingAddresses($this->donor->id)[0]['line1']);
    }

    /**
     * A nonce issued for one donor's page is not accepted for another donor.
     *
     * @since 4.19.0.1
     */
    public function testNonceForOneDonorIsRejectedForAnotherDonor(): void
    {
        wp_set_current_user($this->editorUserId);

        $response = $this->callHandler($this->otherDonor->id, $this->nonceFor($this->donor->id), 'add', 'billing');

        $this->assertFalse($response['success']);
        $this->assertEmpty($this->billingAddresses($this->otherDonor->id));
    }

    /**
     * @since 4.19.0.1
     */
    public function testInvalidNonceIsRejected(): void
    {
        wp_set_current_user($this->editorUserId);

        $response = $this->callHandler($this->donor->id, 'invalid', 'add', 'billing');

        $this->assertFalse($response['success']);
        $this->assertEmpty($this->billingAddresses($this->donor->id));
    }

    /**
     * @since 4.19.0.1
     */
    public function testDonorEditorCanAddUpdateAndRemoveAddress(): void
    {
        wp_set_current_user($this->editorUserId);
        $nonce = $this->nonceFor($this->donor->id);
        $address = $this->addressFields();

        $added = $this->callHandler($this->donor->id, $nonce, 'add', 'billing', $address);

        $this->assertTrue($added['success']);
        $this->assertSame($address['line1'], $this->billingAddresses($this->donor->id)[0]['line1']);

        $updatedLine1 = 'Changed ' . $address['line1'];
        $updated = $this->callHandler(
            $this->donor->id,
            $nonce,
            'update',
            'billing_0',
            array_merge($address, ['line1' => $updatedLine1])
        );

        $this->assertTrue($updated['success']);
        $this->assertSame($updatedLine1, $this->billingAddresses($this->donor->id)[0]['line1']);

        $removed = $this->callHandler($this->donor->id, $nonce, 'remove', 'billing_0');

        $this->assertTrue($removed['success']);
        $this->assertEmpty($this->billingAddresses($this->donor->id));
    }

    /**
     * @since 4.19.0.1
     */
    private function nonceFor(int $donorId): string
    {
        return wp_create_nonce('give-manage-donor-addresses-' . $donorId);
    }

    /**
     * @since 4.19.0.1
     *
     * @return array{line1: string, line2: string, city: string, state: string, zip: string, country: string}
     */
    private function addressFields(): array
    {
        return [
            'line1' => '123 Main St',
            'line2' => '',
            'city' => 'San Diego',
            'state' => 'CA',
            'zip' => '92101',
            'country' => 'US',
        ];
    }

    /**
     * @since 4.19.0.1
     *
     * @return array{line1: string, line2: string, city: string, state: string, zip: string, country: string}
     */
    private function seedBillingAddress(int $donorId): array
    {
        $address = $this->addressFields();
        (new Give_Donor($donorId))->add_address('billing[]', $address);

        return $address;
    }

    /**
     * @since 4.19.0.1
     *
     * @return array<int, array<string, string>>
     */
    private function billingAddresses(int $donorId): array
    {
        $donor = new Give_Donor($donorId);

        return array_values($donor->address['billing'] ?? []);
    }

    /**
     * Runs the handler with a request shaped like the one the donor screen sends and returns the
     * decoded JSON response.
     *
     * @since 4.19.0.1
     *
     * @param array<string, string> $address
     *
     * @return array{success: bool, data?: mixed}
     */
    private function callHandler(int $donorId, string $nonce, string $action, string $addressId, array $address = []): array
    {
        $_POST = [
            'action' => 'donor_manage_addresses',
            'donorID' => (string)$donorId,
            'form' => http_build_query(array_merge($address ?: $this->addressFields(), [
                '_wpnonce' => $nonce,
                'address-action' => $action,
                'address-id' => $addressId,
            ])),
        ];

        ob_start();

        try {
            give_ajax_donor_manage_addresses();
            $output = ob_get_clean();
            $this->fail("Expected WPDieException was not thrown. Output: {$output}");
        } catch (WPDieException $exception) {
            $output = ob_get_clean() ?: $exception->getMessage();
        }

        $response = json_decode(trim($output), true);

        if (!is_array($response)) {
            $this->fail("Handler output is not valid JSON: \"{$output}\"");
        }

        return $response;
    }
}
