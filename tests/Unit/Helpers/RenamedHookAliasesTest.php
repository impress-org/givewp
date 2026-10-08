<?php

namespace Give\Tests\Unit\Helpers;

use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Donor;

/**
 * Each renamed hook has a givewp_-prefixed name. The old name must keep firing with the same
 * arguments and the same filtering result, and log exactly one deprecation notice. With no
 * listener on the old name there is no notice.
 *
 * @since TBD
 */
class RenamedHookAliasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string[]
     */
    private $deprecatedHooks = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->deprecatedHooks = [];
        add_action('deprecated_hook_run', [$this, 'recordDeprecatedHook']);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_action('deprecated_hook_run', [$this, 'recordDeprecatedHook']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function recordDeprecatedHook($hook)
    {
        $this->deprecatedHooks[] = $hook;
    }

    /**
     * @since TBD
     */
    public function testBeforeInitActionFiresOldThenNew()
    {
        $this->setExpectedDeprecated('before_give_init');
        $calls = [];
        add_action('before_give_init', function () use (&$calls) {
            $calls[] = 'old';
        });
        add_action('givewp_before_init', function () use (&$calls) {
            $calls[] = 'new';
        });

        give()->init();

        $this->assertSame(['old', 'new'], array_slice($calls, 0, 2));
        $this->assertSame(['before_give_init'], $this->deprecatedHooks);
    }

    /**
     * @since TBD
     */
    public function testBeforeInitActionHasNoNoticeWithoutOldListener()
    {
        $calls = 0;
        add_action('givewp_before_init', function () use (&$calls) {
            $calls++;
        });

        give()->init();

        $this->assertSame(1, $calls);
        $this->assertSame([], $this->deprecatedHooks);
    }

    /**
     * @since TBD
     */
    public function testDonorInitialsNewFilterRuns()
    {
        add_filter('givewp_get_donor_initials', function ($initials) {
            return $initials . '!';
        });

        $this->assertSame('JD!', $this->makeDonor()->get_donor_initals());
        $this->assertSame([], $this->deprecatedHooks);
    }

    /**
     * @since TBD
     */
    public function testDonorInitialsOldFilterStillAppliesAndIsDeprecated()
    {
        $this->setExpectedDeprecated('get_donor_initals');
        $args = null;
        add_filter('get_donor_initals', function ($initials) use (&$args) {
            $args = func_get_args();

            return 'X' . $initials;
        });
        add_filter('givewp_get_donor_initials', function ($initials) {
            return $initials . '!';
        });

        $this->assertSame('XJD!', $this->makeDonor()->get_donor_initals());
        $this->assertSame(['JD'], $args);
        $this->assertSame(['get_donor_initals'], $this->deprecatedHooks);
    }

    /**
     * @since TBD
     */
    public function testFeaturedImagePlaceholderNewFilterRuns()
    {
        $postId = $this->makePostId();
        add_filter('givewp_single_form_image_html', function ($html, $id) use ($postId) {
            return $id === $postId ? '<b>new</b>' : $html;
        }, 10, 2);

        $this->assertStringContainsString('<b>new</b>', $this->renderFeaturedImage($postId));
        $this->assertSame([], $this->deprecatedHooks);
    }

    /**
     * @since TBD
     */
    public function testFeaturedImagePlaceholderOldFilterStillAppliesAndIsDeprecated()
    {
        $this->setExpectedDeprecated('single_give_form_image_html');
        $postId = $this->makePostId();
        $args = null;
        add_filter('single_give_form_image_html', function () use (&$args) {
            $args = func_get_args();

            return '<i>old</i>';
        }, 10, 2);

        $this->assertStringContainsString('<i>old</i></div>', $this->renderFeaturedImage($postId));
        $this->assertCount(2, $args);
        $this->assertSame($postId, $args[1]);
        $this->assertSame(['single_give_form_image_html'], $this->deprecatedHooks);
    }

    /**
     * @since TBD
     */
    public function testFeaturedImageThumbnailFiltersOldAndNew()
    {
        $this->setExpectedDeprecated('single_give_form_large_thumbnail_size');
        $this->setExpectedDeprecated('single_give_form_image_html');
        $postId = $this->makePostId();
        add_filter('has_post_thumbnail', '__return_true');
        $size = null;
        add_filter('post_thumbnail_html', function ($html, $id, $thumbnailId, $requestedSize) use (&$size) {
            $size = $requestedSize;

            return '<img src="thumb" />';
        }, 10, 4);
        add_filter('single_give_form_large_thumbnail_size', function ($value) {
            return $value . '-old';
        });
        add_filter('givewp_single_form_large_thumbnail_size', function ($value) {
            return $value . '-new';
        });
        add_filter('single_give_form_image_html', function ($html) {
            return $html . '<!--old-->';
        });
        add_filter('givewp_single_form_image_html', function ($html) {
            return $html . '<!--new-->';
        });

        $html = $this->renderFeaturedImage($postId);

        $this->assertSame('large-old-new', $size);
        $this->assertStringContainsString('<img src="thumb" /><!--old--><!--new--></div>', $html);
        $this->assertSame(
            ['single_give_form_large_thumbnail_size', 'single_give_form_image_html'],
            $this->deprecatedHooks
        );
    }

    /**
     * @since TBD
     */
    private function makeDonor(): Give_Donor
    {
        $donorId = Donor::factory()->create(['name' => 'Jane Doe', 'firstName' => 'Jane', 'lastName' => 'Doe'])->id;

        return new Give_Donor($donorId);
    }

    /**
     * @since TBD
     */
    private function makePostId(): int
    {
        return (int)self::factory()->post->create(['post_type' => 'give_forms']);
    }

    /**
     * @since TBD
     */
    private function renderFeaturedImage(int $postId): string
    {
        global $post;
        $post = get_post($postId);

        ob_start();
        include GIVE_PLUGIN_DIR . 'templates/single-give-form/featured-image.php';

        return ob_get_clean();
    }
}
