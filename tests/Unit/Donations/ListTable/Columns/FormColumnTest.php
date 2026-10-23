<?php

namespace Give\Tests\Unit\Donations\ListTable\Columns;

use Give\Donations\ListTable\Columns\FormColumn;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.19.0
 */
class FormColumnTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.19.0
     */
    public function testLinksToFormWhenUserCanEditForms()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $donation = new Donation(['formId' => 42, 'formTitle' => 'Spring Form']);

        $cell = (new FormColumn())->getCellValue($donation);

        $this->assertStringContainsString('Spring Form', $cell);
        $this->assertStringContainsString('post.php?post=42', $cell);
    }

    /**
     * @since 4.19.0
     */
    public function testShowsPlainTitleWhenUserCannotEditForms()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'give_accountant']));
        $donation = new Donation(['formId' => 42, 'formTitle' => 'Spring Form']);

        $this->assertSame('Spring Form', (new FormColumn())->getCellValue($donation));
    }
}
