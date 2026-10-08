<?php

namespace Give\Tests\Unit\Admin;

use Give\Tests\TestCase;
use Give_Addon_Activation_Banner;
use ReflectionClass;

/**
 * @since TBD
 */
class TestAddonActivationBanner extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['give_addon'], $_GET['give_addon_activation_ignore'], $_GET['_wpnonce']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDismissLinkWithoutNonceSavesNothing()
    {
        $userId = $this->factory()->user->create();
        $this->setRequest(null);

        $this->makeBanner($userId)->give_addon_notice_ignore();

        $this->assertSame('', get_user_meta($userId, 'give_addon_activation_ignore_give-test-addon', true));
    }

    /**
     * @since TBD
     */
    public function testDismissLinkWithInvalidNonceSavesNothing()
    {
        $userId = $this->factory()->user->create();
        $this->setRequest('invalid');

        $this->makeBanner($userId)->give_addon_notice_ignore();

        $this->assertSame('', get_user_meta($userId, 'give_addon_activation_ignore_give-test-addon', true));
    }

    /**
     * @since TBD
     */
    public function testDismissLinkWithValidNonceSavesTheDismissal()
    {
        $userId = $this->factory()->user->create();
        wp_set_current_user($userId);
        $this->setRequest(wp_create_nonce('give_addon_activation_ignore'));

        $this->makeBanner($userId)->give_addon_notice_ignore();

        $this->assertSame('true', get_user_meta($userId, 'give_addon_activation_ignore_give-test-addon', true));
    }

    /**
     * @since TBD
     */
    private function setRequest($nonce)
    {
        $_GET['give_addon'] = 'give-test-addon';
        $_GET['give_addon_activation_ignore'] = '1';

        if ($nonce !== null) {
            $_GET['_wpnonce'] = $nonce;
        }
    }

    /**
     * Builds the banner without its constructor, which only runs on the Plugins screen.
     *
     * @since TBD
     */
    private function makeBanner(int $userId): Give_Addon_Activation_Banner
    {
        $reflection = new ReflectionClass(Give_Addon_Activation_Banner::class);
        $banner = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('user_id');
        $property->setAccessible(true);
        $property->setValue($banner, $userId);

        return $banner;
    }
}
