<?php

namespace Give\Tests\Unit\Admin;

use Faker\Factory;
use Faker\Generator;
use Give\Tests\TestCase;
use WPDieException;

/**
 * @since TBD
 *
 * @covers ::give_load_wp_editor
 */
final class LoadWpEditorRequestTest extends TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!function_exists('give_load_wp_editor')) {
            require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-actions.php';
        }

        $this->faker = Factory::create();

        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);

        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_filter('wp_doing_ajax', '__return_true');
        remove_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);

        $_POST = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testRejectsRequestWithoutValidNonce(): void
    {
        $_POST['wp_editor'] = $this->encodeEditorArguments([]);

        [$output, $dieMessage] = $this->runHandler();

        $this->assertSame('-1', $dieMessage);
        $this->assertSame('', $output);
    }

    /**
     * @since TBD
     */
    public function testRendersEditorWithRowCountAndClassFromRequest(): void
    {
        $rows = $this->faker->numberBetween(2, 30);
        $editorClass = $this->faker->slug(2);
        $textareaName = $this->faker->slug(2);

        $this->setValidNonce();
        $_POST['textarea_name'] = $textareaName;
        $_POST['wp_editor'] = $this->encodeEditorArguments([
            'textarea_rows' => $rows,
            'editor_class' => $editorClass,
        ]);

        [$output] = $this->runHandler();

        $this->assertStringContainsString('name="' . $textareaName . '"', $output);
        $this->assertStringContainsString('rows="' . $rows . '"', $output);
        $this->assertStringContainsString('class="' . $editorClass . ' wp-editor-area"', $output);
    }

    /**
     * @since TBD
     */
    public function testIgnoresOtherEditorSettingsFromRequest(): void
    {
        $editorCss = 'give-editor-css-' . $this->faker->uuid;

        $this->setValidNonce();
        $_POST['wp_editor'] = $this->encodeEditorArguments([
            'editor_css' => $editorCss,
        ]);

        [$output] = $this->runHandler();

        $this->assertStringContainsString('wp-editor-area', $output);
        $this->assertStringNotContainsString($editorCss, $output);
    }

    /**
     * @since TBD
     */
    public function testRendersNothingForUserWhoCannotEditForms(): void
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'subscriber']));

        $this->setValidNonce();
        $_POST['wp_editor'] = $this->encodeEditorArguments([]);

        [$output] = $this->runHandler();

        $this->assertSame('', $output);
    }

    /**
     * @since TBD
     */
    public function testRendersNothingWhenEditorArgumentsCannotBeDecoded(): void
    {
        $this->setValidNonce();
        $_POST['wp_editor'] = $this->faker->word;

        [$output] = $this->runHandler();

        $this->assertSame('', $output);
    }

    /**
     * @since TBD
     */
    private function setValidNonce(): void
    {
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = wp_create_nonce('give_load_wp_editor');
    }

    /**
     * Encodes the editor arguments the way give_wysiwyg() writes them to the data-wp-editor attribute.
     *
     * @since TBD
     *
     * @param array{textarea_rows?: int, editor_class?: string, editor_css?: string} $settings Settings in the request.
     *
     * @return string Base64-encoded JSON of the editor content, editor ID and settings.
     */
    private function encodeEditorArguments(array $settings): string
    {
        return base64_encode(json_encode(['', 'give_editor', $settings]));
    }

    /**
     * Runs the handler until it ends the request with wp_die().
     *
     * @since TBD
     *
     * @return array{0: string, 1: string} The printed output and the wp_die() message.
     */
    private function runHandler(): array
    {
        $dieMessage = null;

        ob_start();

        try {
            give_load_wp_editor();
        } catch (WPDieException $exception) {
            $dieMessage = $exception->getMessage();
        }

        $output = ob_get_clean();

        $this->assertNotNull($dieMessage, 'The handler must end the request with wp_die().');

        return [$output, $dieMessage];
    }
}
