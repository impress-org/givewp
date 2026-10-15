<?php

namespace Give\Tests\Unit\Helpers;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
class DeprecatedFunctionNoticeTest extends TestCase
{
    /**
     * @var string[]
     */
    private $notices = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->notices = [];

        add_filter('give_deprecated_function_trigger_error', '__return_true');
        add_filter('give_doing_it_wrong_trigger_error', '__return_true');

        set_error_handler(function ($level, $message) {
            $this->notices[] = $message;

            return true;
        }, E_USER_NOTICE);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        restore_error_handler();
        remove_filter('give_deprecated_function_trigger_error', '__return_true');
        remove_filter('give_doing_it_wrong_trigger_error', '__return_true');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDeprecatedFunctionTriggersOneNoticeAndKeepsItsReturnValue(): void
    {
        $result = give_log_default_views();

        $this->assertArrayHasKey('sales', $result);
        $this->assertCount(1, $this->notices);
        $this->assertStringContainsString('give_log_default_views is <strong>deprecated</strong>', $this->notices[0]);
        $this->assertStringContainsString('since GiveWP version 1.8 with no alternative available.', $this->notices[0]);
        $this->assertStringNotContainsString('[function] =>', $this->notices[0]);
    }

    /**
     * @since TBD
     */
    public function testDeprecatedFunctionWithReplacementNamesTheReplacement(): void
    {
        give_purchase_form_validate_agree_to_terms();

        $this->assertCount(1, $this->notices);
        $this->assertStringContainsString('Use give_donation_form_validate_agree_to_terms instead.', $this->notices[0]);
    }

    /**
     * @since TBD
     */
    public function testDeprecatedFunctionStillFiresItsHook(): void
    {
        $calls = [];
        $listener = function ($function, $replacement, $version) use (&$calls) {
            $calls[] = [$function, $replacement, $version];
        };
        add_action('give_deprecated_function_run', $listener, 10, 3);

        give_log_default_views();

        remove_action('give_deprecated_function_run', $listener, 10);

        $this->assertSame([['give_log_default_views', null, '1.8']], $calls);
    }

    /**
     * @since TBD
     */
    public function testAddOnsCanStillPassABacktraceArgument(): void
    {
        _give_deprecated_function('old_add_on_function', '1.0', 'new_add_on_function', ['file' => 'add-on.php']);

        $this->assertCount(1, $this->notices);
        $this->assertStringContainsString('old_add_on_function is <strong>deprecated</strong>', $this->notices[0]);
    }

    /**
     * @since TBD
     */
    public function testDeprecatedFunctionIsSilentWhenTheFilterTurnsTheNoticeOff(): void
    {
        remove_filter('give_deprecated_function_trigger_error', '__return_true');
        add_filter('give_deprecated_function_trigger_error', '__return_false');

        give_log_default_views();

        remove_filter('give_deprecated_function_trigger_error', '__return_false');

        $this->assertCount(0, $this->notices);
    }

    /**
     * @since TBD
     */
    public function testDoingItWrongTriggersOneNotice(): void
    {
        give_doing_it_wrong('my_function', 'Bad argument.');

        $this->assertCount(1, $this->notices);
        $this->assertStringContainsString('my_function was called <strong>incorrectly</strong>. Bad argument.', $this->notices[0]);
    }
}
