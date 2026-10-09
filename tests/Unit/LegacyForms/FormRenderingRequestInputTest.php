<?php

namespace Give\Tests\Unit\LegacyForms;

use Give\Tests\TestCase;

/**
 * Checks that unslashing and sanitizing request input in the legacy form rendering code
 * did not change how valid requests behave.
 *
 * @since TBD
 */
final class FormRenderingRequestInputTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testRedirectAndPopupFormAddsPostedPaymentModeForFormGrid(): void
    {
        $_POST['is-form-grid'] = wp_slash('true');
        $_POST['payment-mode'] = wp_slash('offline');

        $url = give_redirect_and_popup_form('https://example.org/page/?x=1', ['form-id' => 12]);

        $this->assertSame('https://example.org/page/?form-id=12&payment-mode=offline', $url);
    }

    /**
     * @since TBD
     */
    public function testRedirectAndPopupFormDoesNotFailWhenPaymentModeIsMissing(): void
    {
        $_POST['is-form-grid'] = 'true';

        $url = give_redirect_and_popup_form('https://example.org/page/', ['form-id' => 12]);

        $this->assertSame('https://example.org/page/?form-id=12&payment-mode', $url);
    }

    /**
     * @since TBD
     */
    public function testRedirectAndPopupFormLeavesUrlAloneOutsideFormGrid(): void
    {
        $url = give_redirect_and_popup_form('https://example.org/page/?x=1', ['form-id' => 12]);

        $this->assertSame('https://example.org/page/?x=1', $url);
    }

    /**
     * @since TBD
     */
    public function testSuccessPageContentUsesTheConfirmationFilterFromTheUrl(): void
    {
        $pageId = self::factory()->post->create(['post_type' => 'page']);
        give_update_option('success_page', $pageId);
        $this->go_to(get_permalink($pageId));
        $_GET['payment-confirmation'] = wp_slash('unit_test');

        add_filter('give_payment_confirm_unit_test', static function ($content) {
            return $content . ' confirmed';
        });

        $this->assertSame('Thanks confirmed', give_filter_success_page_content('Thanks'));
    }

    /**
     * @since TBD
     */
    public function testPriceRangeFollowsTheOrderParam(): void
    {
        $formId = self::factory()->post->create(['post_type' => 'give_forms']);

        $_REQUEST['order'] = wp_slash('desc');
        $desc = give_price_range($formId);
        unset($_REQUEST['order']);
        $asc = give_price_range($formId);

        $this->assertStringContainsString('give_price_range_high', $desc);
        $this->assertStringStartsWith('<span class="give_price_range_low">', $asc);
    }

    /**
     * @since TBD
     */
    public function testFinalTotalReadsThePostedTotalWithQuotesAndBackslashes(): void
    {
        $formId = self::factory()->post->create(['post_type' => 'give_forms']);
        $_POST['give_total'] = wp_slash('25.50');

        ob_start();
        give_checkout_final_total($formId);
        $html = ob_get_clean();

        $this->assertStringContainsString('data-total="25.50"', $html);
    }
}
