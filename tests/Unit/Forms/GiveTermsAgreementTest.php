<?php

namespace Give\Tests\Unit\Forms;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
class GiveTermsAgreementTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testKeepsLinksInTheAgreementLabel(): void
    {
        $formId = $this->createFormWithAgreementLabel('I agree to the <a href="https://givewp.com">Terms</a>');

        ob_start();
        give_terms_agreement($formId);
        $output = ob_get_clean();

        $this->assertStringContainsString('I agree to the <a href="https://givewp.com">Terms</a></label>', $output);
    }

    /**
     * @since TBD
     */
    public function testStripsScriptsFromTheAgreementLabel(): void
    {
        $formId = $this->createFormWithAgreementLabel('Agree<script>alert(1)</script>');

        ob_start();
        give_terms_agreement($formId);
        $output = ob_get_clean();

        $this->assertStringNotContainsString('<script>', $output);
    }

    /**
     * @since TBD
     */
    private function createFormWithAgreementLabel(string $label): int
    {
        $formId = wp_insert_post(['post_type' => 'give_forms', 'post_status' => 'publish', 'post_title' => 'Form']);
        give_update_meta($formId, '_give_terms_option', 'enabled');
        give_update_meta($formId, '_give_agree_label', $label);
        give_update_meta($formId, '_give_agree_text', 'Terms text');

        return $formId;
    }
}
