<?php

namespace Give\Tests\Unit\LegacyMisc;

use Give\Tests\TestCase;

/**
 * Checks that unslashing and sanitizing request input in the public legacy includes
 * did not change how valid requests behave.
 *
 * @since TBD
 */
final class PublicMiscRequestInputTest extends TestCase
{
    /**
     * The $_SERVER values from before the test, so changes made here do not reach later tests.
     *
     * @since TBD
     */
    private array $serverBackup = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        $this->serverBackup = $_SERVER;

        parent::setUp();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];

        parent::tearDown();

        $_SERVER = $this->serverBackup;
    }

    /**
     * @since TBD
     */
    public function testGetIpReturnsTheFirstForwardedAddress(): void
    {
        unset($_SERVER['HTTP_CLIENT_IP']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5, 10.0.0.1';

        $this->assertSame('203.0.113.5', give_get_ip());
        $this->assertSame('203.0.113.5,10.0.0.1', give_get_ip(false));
    }

    /**
     * @since TBD
     */
    public function testGetIpFallsBackToTheRemoteAddress(): void
    {
        unset($_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = '198.51.100.7';

        $this->assertSame('198.51.100.7', give_get_ip());
    }

    /**
     * @since TBD
     */
    public function testAdminMessagesKeyReadsTheUrlMessages(): void
    {
        $_GET['give-messages'] = ['api-key-generated'];
        $_GET['give-message'] = 'legacy-message';

        $this->assertSame(['api-key-generated', 'legacy-message'], give_get_admin_messages_key());
    }

    /**
     * @since TBD
     */
    public function testAdminPostIdReadsTheRequestedPost(): void
    {
        $_REQUEST['post_id'] = '42';

        $this->assertSame(42, give_get_admin_post_id());
    }

    /**
     * @since TBD
     */
    public function testRecentlyActivatedAddonsRecordsTheSelectedPlugins(): void
    {
        delete_option('give_recently_activated_addons');
        $_REQUEST['action'] = '-1';
        $_REQUEST['action2'] = 'activate-selected';
        $_REQUEST['checked'] = ['give-stripe/give-stripe.php', 'akismet/akismet.php'];

        give_recently_activated_addons();

        $this->assertSame(['give-stripe/give-stripe.php'], get_option('give_recently_activated_addons'));
    }

    /**
     * @since TBD
     */
    public function testRecentlyActivatedAddonsRecordsASinglePlugin(): void
    {
        delete_option('give_recently_activated_addons');
        $_REQUEST['action'] = 'activate';
        $_REQUEST['plugin'] = 'give-paypal/give-paypal.php';

        give_recently_activated_addons();

        $this->assertSame(['give-paypal/give-paypal.php'], get_option('give_recently_activated_addons'));
    }

    /**
     * @since TBD
     */
    public function testIsHostDetectsFlywheelFromTheServerName(): void
    {
        $_SERVER['SERVER_NAME'] = 'Flywheel';

        $this->assertTrue(give_is_host('flywheel'));
    }

    /**
     * @since TBD
     */
    public function testIsAddNewFormPageReadsTheRequestUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/wp-admin/post-new.php?post_type=give_forms';

        $this->assertTrue(give_is_add_new_form_page());

        $_SERVER['REQUEST_URI'] = '/wp-admin/index.php';

        $this->assertFalse(give_is_add_new_form_page());
    }
}
