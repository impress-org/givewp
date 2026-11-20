<?php

namespace Give\Tests\Unit\Admin;

use Give\Tests\TestCase;
use Give_Shortcode_Totals;
use WPDieException;

/**
 * Locks the form category and tag lists that read terms with get_terms().
 *
 * @since TBD
 */
final class FormTermListsTest extends TestCase
{
    /**
     * @var string[] Taxonomies this test registered, so tearDown() can unregister them.
     */
    private $registeredTaxonomies = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/abstract-shortcode-generator.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/shortcodes/shortcode-give-totals.php';

        foreach (['give_forms_category', 'give_forms_tag'] as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                $this->registeredTaxonomies[] = $taxonomy;
            }
        }

        give_update_option('categories', 'enabled');
        give_update_option('tags', 'enabled');
        give_setup_taxonomies();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach ($this->registeredTaxonomies as $taxonomy) {
            unregister_taxonomy($taxonomy);
        }

        unset($_POST['s']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testCategoryDropdownListsCategoriesThatHaveForms(): void
    {
        $terms = $this->createFormWithTerms();

        $html = Give()->html->category_dropdown();

        $this->assertStringContainsString('<option value="' . $terms['category'] . '">QA3410 Category</option>', $html);
        $this->assertStringNotContainsString('QA3410 Empty Category', $html);
        $this->assertStringNotContainsString('QA3410 Tag', $html);
    }

    /**
     * @since TBD
     */
    public function testCategoryDropdownUsesTheFilteredArgumentsButKeepsItsTaxonomy(): void
    {
        $terms = $this->createFormWithTerms();

        add_filter('give_forms_category_dropdown', static function () {
            return ['hide_empty' => false, 'taxonomy' => 'category'];
        });

        $html = Give()->html->category_dropdown();

        $this->assertStringContainsString('<option value="' . $terms['emptyCategory'] . '">QA3410 Empty Category</option>', $html);
        $this->assertStringNotContainsString('Uncategorized', $html);
    }

    /**
     * @since TBD
     */
    public function testTagsDropdownListsTagsThatHaveForms(): void
    {
        $terms = $this->createFormWithTerms();

        $html = Give()->html->tags_dropdown();

        $this->assertStringContainsString('<option value="' . $terms['tag'] . '">QA3410 Tag</option>', $html);
        $this->assertStringNotContainsString('QA3410 Category', $html);
    }

    /**
     * @since TBD
     */
    public function testTotalsShortcodeListsFormCategoriesAndTags(): void
    {
        $terms = $this->createFormWithTerms();

        $fields = [];
        foreach ((new Give_Shortcode_Totals())->define_fields() as $field) {
            if (!empty($field['name'])) {
                $fields[$field['name']] = $field;
            }
        }

        $this->assertSame('QA3410 Category', $fields['cats']['options'][$terms['category']]);
        $this->assertArrayNotHasKey($terms['emptyCategory'], $fields['cats']['options']);
        $this->assertSame('QA3410 Tag', $fields['tags']['options'][$terms['tag']]);
    }

    /**
     * @since TBD
     */
    public function testAjaxCategorySearchReturnsMatchingCategories(): void
    {
        $terms = $this->createFormWithTerms();
        $_POST['s'] = 'QA3410';

        $this->assertSame(
            [['id' => $terms['category'], 'name' => 'QA3410 Category']],
            $this->runAjaxHandler('give_ajax_categories_search')
        );
    }

    /**
     * @since TBD
     */
    public function testAjaxTagSearchReturnsMatchingTags(): void
    {
        $terms = $this->createFormWithTerms();
        $_POST['s'] = 'QA3410';

        $this->assertSame(
            [['id' => $terms['tag'], 'name' => 'QA3410 Tag']],
            $this->runAjaxHandler('give_ajax_tags_search')
        );
    }

    /**
     * Creates a published form with one category and one tag, plus a category no form uses.
     *
     * @since TBD
     */
    private function createFormWithTerms(): array
    {
        $formId = self::factory()->post->create(['post_type' => 'give_forms', 'post_status' => 'publish']);

        $terms = [
            'category' => self::factory()->term->create(['taxonomy' => 'give_forms_category', 'name' => 'QA3410 Category']),
            'emptyCategory' => self::factory()->term->create(['taxonomy' => 'give_forms_category', 'name' => 'QA3410 Empty Category']),
            'tag' => self::factory()->term->create(['taxonomy' => 'give_forms_tag', 'name' => 'QA3410 Tag']),
        ];

        wp_set_object_terms($formId, [$terms['category']], 'give_forms_category');
        wp_set_object_terms($formId, [$terms['tag']], 'give_forms_tag');

        return $terms;
    }

    /**
     * Runs an AJAX handler and returns its decoded JSON response.
     *
     * @since TBD
     */
    private function runAjaxHandler(string $handler): array
    {
        add_filter('wp_doing_ajax', '__return_true');
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);

        ob_start();

        try {
            $handler();
        } catch (WPDieException $exception) {
            // wp_send_json() ends the request with wp_die().
        }

        return json_decode(ob_get_clean(), true);
    }
}
