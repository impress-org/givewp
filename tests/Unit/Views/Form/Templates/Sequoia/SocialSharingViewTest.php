<?php

namespace Give\Tests\Unit\Views\Form\Templates\Sequoia;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class SocialSharingViewTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testTwitterMessageIsEmittedInAnEscapedSingleQuotedStringNotATemplateLiteral(): void
    {
        // Input containing characters that are significant in JS string / HTML attribute contexts.
        $input    = '${alert(1)}' . "'" . '`</script>';
        $html     = $this->renderView($input);
        $expected = esc_js($input);

        // The value is emitted in a single-quoted string context, not an evaluating template literal.
        $this->assertStringNotContainsString('const text = `', $html);

        // And it is escaped with esc_js() — expected derived from the input, no magic literal.
        $this->assertStringContainsString("const text = '" . $expected . "'", $html);
    }

    /**
     * Render the Sequoia social-sharing view with the given twitter message.
     * The view reads $options from local scope, so it is required under output
     * buffering with a crafted array.
     *
     * @since TBD
     */
    private function renderView(string $twitterMessage): string
    {
        $options = [
            'thank-you' => [
                'sharing'             => 'enabled',
                'sharing_instruction' => 'Share your support',
                'twitter_message'     => $twitterMessage,
            ],
        ];

        ob_start();
        require GIVE_PLUGIN_DIR . 'src/Views/Form/Templates/Sequoia/views/social-sharing.php';

        return (string) ob_get_clean();
    }
}
