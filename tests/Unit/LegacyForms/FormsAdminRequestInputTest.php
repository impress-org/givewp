<?php

namespace Give\Tests\Unit\LegacyForms;

use Give\Tests\TestCase;
use Give_MetaBox_Form_Data;
use ReflectionProperty;

/**
 * @since TBD
 */
final class FormsAdminRequestInputTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/dashboard-columns.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/forms/class-metabox-form-data.php';
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset(
            $_POST['post_type'],
            $_REQUEST['_give_regprice'],
            $_REQUEST['_give_custom_amount'],
            $_REQUEST['_give_set_price'],
            $_REQUEST['_give_price_option'],
            $_REQUEST['_give_custom_amount_range'],
            $_REQUEST['author'],
            $_POST['give_form_meta_nonce'],
            $_POST['post_ID'],
            $_POST['_give_price_option'],
            $_POST['tx'],
            $_POST['tt'],
            $_POST['gr'],
            $_POST['classic']
        );

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testQuickEditSavesTheUnslashedAndSanitizedPrice(): void
    {
        $formId = $this->createForm();

        $_POST['post_type'] = 'give_forms';
        $_REQUEST['_give_regprice'] = '<b>25.50</b>';

        give_price_save_quick_edit($formId);

        $this->assertSame('25.500000', give_get_meta($formId, '_give_set_price', true));
    }

    /**
     * @since TBD
     */
    public function testQuickEditRaisesTheMinimumWhenThePriceIsLower(): void
    {
        $formId = $this->createForm();

        $_POST['post_type'] = 'give_forms';
        $_REQUEST['_give_custom_amount'] = 'enabled';
        $_REQUEST['_give_set_price'] = '5.00';
        $_REQUEST['_give_price_option'] = 'set';
        $_REQUEST['_give_custom_amount_range'] = ['minimum' => '10.00'];

        give_price_save_quick_edit($formId);

        $this->assertSame('5.000000', give_get_meta($formId, '_give_custom_amount_range_minimum', true));
    }

    /**
     * @since TBD
     */
    public function testQuickEditIgnoresARangeWithoutAMinimum(): void
    {
        $formId = $this->createForm();

        $_POST['post_type'] = 'give_forms';
        $_REQUEST['_give_custom_amount'] = 'enabled';
        $_REQUEST['_give_set_price'] = '5.00';
        $_REQUEST['_give_price_option'] = 'set';
        $_REQUEST['_give_custom_amount_range'] = [];

        give_price_save_quick_edit($formId);

        $this->assertEmpty(give_get_meta($formId, '_give_custom_amount_range_minimum', true));
    }

    /**
     * The expected values are what this code stored before the request input was unslashed for the sniffs.
     *
     * @since TBD
     */
    public function testSaveStoresBackslashesAndQuotesAsBefore(): void
    {
        $formId = $this->createForm();
        $metabox = new Give_MetaBox_Form_Data();
        $settings = new ReflectionProperty($metabox, 'settings');
        $settings->setAccessible(true);
        $settings->setValue($metabox, [
            [
                'id' => 'test',
                'fields' => [
                    ['id' => 'tx', 'type' => 'textarea'],
                    ['id' => 'tt', 'type' => 'text'],
                    [
                        'id' => 'gr',
                        'type' => 'group',
                        'fields' => [
                            ['id' => 'gw', 'type' => 'wysiwyg'],
                            ['id' => 'gt', 'type' => 'text'],
                        ],
                    ],
                ],
            ],
        ]);

        $value = 'a\\b "q" \'s\'';
        $_POST = wp_slash([
            'give_form_meta_nonce' => wp_create_nonce('give_save_form_meta'),
            'post_ID' => $formId,
            '_give_price_option' => 'set',
            'tx' => $value,
            'tt' => $value,
            'gr' => [['gw' => $value, 'gt' => $value]],
        ]);

        $metabox->save($formId, get_post($formId));

        $expected = 'ab "q" \'s\'';

        $this->assertSame($expected, get_post_meta($formId, 'tx', true));
        $this->assertSame($expected, get_post_meta($formId, 'tt', true));
        $this->assertSame([['gw' => $expected, 'gt' => $expected]], get_post_meta($formId, 'gr', true));
    }

    /**
     * @since TBD
     */
    public function testSaveFormTemplateSettingsStoresBackslashesAndQuotesAsBefore(): void
    {
        $formId = $this->createForm();
        give_update_meta($formId, '_give_form_template', 'classic');

        $value = 'a\\b "q" \'s\'';
        $_POST = wp_slash([
            'classic' => ['visual_appearance' => ['main_heading' => $value, 'description' => $value]],
        ]);

        (new Give_MetaBox_Form_Data())->save_form_template_settings('_give_form_template', 'classic', $formId);

        $stored = get_post_meta($formId, '_give_classic_form_template_settings', true);

        $this->assertSame('ab "q" \'s\'', $stored['visual_appearance']['main_heading']);
        $this->assertSame('a\b "q" \'s\'', $stored['visual_appearance']['description']);
    }

    /**
     * @since TBD
     */
    private function createForm(): int
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        return $this->factory()->post->create(['post_type' => 'give_forms']);
    }
}
