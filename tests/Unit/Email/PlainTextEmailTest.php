<?php

namespace Give\Tests\Unit\Email;

use Give\Tests\TestCase;
use Give_Donation_Receipt_Email;

/**
 * @since TBD
 */
class PlainTextEmailTest extends TestCase
{
    /**
     * The mailer settings before the test: the preview changes them on the shared mailer.
     *
     * @var array
     */
    private $mailerState = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        foreach (['template', 'content_type', 'html'] as $property) {
            $reflection = new \ReflectionProperty(get_class(Give()->emails), $property);
            $reflection->setAccessible(true);
            $this->mailerState[$property] = $reflection->getValue(Give()->emails);
        }
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach ($this->mailerState as $property => $value) {
            Give()->emails->__set($property, $value);
        }

        remove_all_filters('pre_wp_mail');
        remove_all_filters('give_donation-receipt_get_email_message');
        remove_all_filters('give_donation-receipt_get_email_content_type');

        parent::tearDown();
    }

    /**
     * Style and script blocks are removed with their contents, and blank lines at the start or end go away.
     *
     * @since TBD
     */
    public function testPlainTextEmailDropsStyleAndScriptTextAndOuterBlankLines()
    {
        $message = $this->sendPreviewWithMessage(
            "\n\n<style>p{color:red}</style><script>alert(1)</script><p>Dear Ann,</p>\n\nThank you.\n\n"
        );

        $this->assertStringNotContainsString('<', $message);
        $this->assertStringNotContainsString('p{color:red}', $message);
        $this->assertStringNotContainsString('alert(1)', $message);
        $this->assertStringContainsString('Dear Ann,', $message);
        $this->assertStringContainsString('Thank you.', $message);
        $this->assertStringStartsWith('Dear Ann,', $message);
        $this->assertSame(trim($message), $message);
    }

    /**
     * Runs the donation receipt preview as a plain text email and returns the message that would be sent.
     *
     * @since TBD
     */
    private function sendPreviewWithMessage(string $template): string
    {
        $sentMessage = null;

        add_filter('give_donation-receipt_get_email_message', static function () use ($template) {
            return $template;
        });
        add_filter('give_donation-receipt_get_email_content_type', static function () {
            return 'text/plain';
        });
        add_filter('pre_wp_mail', static function ($return, $atts) use (&$sentMessage) {
            $sentMessage = $atts['message'];

            return true;
        }, 10, 2);

        Give_Donation_Receipt_Email::get_instance()->send_preview_email();

        $this->assertNotNull($sentMessage, 'The preview email was not sent.');

        return $sentMessage;
    }
}
