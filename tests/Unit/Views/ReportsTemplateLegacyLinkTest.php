<?php

namespace Give\Tests\Unit\Views;

use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class ReportsTemplateLegacyLinkTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testLegacyReportsTabLinksToThisSitesAdmin(): void
    {
        ob_start();
        include GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/reports-template.php';
        $html = ob_get_clean();

        $expected = esc_url(admin_url('edit.php?post_type=give_forms&page=give-reports&legacy=1'));

        $this->assertStringContainsString('href="' . $expected . '"', $html);
        $this->assertStringNotContainsString('givewp.local', $html);
    }
}
