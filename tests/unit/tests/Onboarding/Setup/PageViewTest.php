<?php

use PHPUnit\Framework\TestCase;
use Give\Onboarding\FormRepository;
use Give\Onboarding\Setup\PageView;

final class PageViewTest extends TestCase {

    /**
     * @link https://github.com/impress-org/givewp/issues/5575
     * @link https://github.com/impress-org/givewp/issues/5575#issuecomment-770950149
     */
    public function testContentSurroundedByUnmergedTagIsNotScrubbed() {
        $pageView = new PageView(
            $this->createMock( FormRepository::class )
        );

        $this->assertContains(
            '<article id="" class="setup-item foo" data-givewp-test="">',
            $pageView->render_template( 'row-item', [ 'class' => 'foo' ] )
        );
    }
}
