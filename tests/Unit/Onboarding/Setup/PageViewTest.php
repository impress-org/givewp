<?php

namespace Give\Tests\Unit\Onboarding\Setup;

use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\Onboarding\FormRepository;
use Give\Onboarding\Setup\PageView;
use Give\Tests\TestCase;

final class PageViewTest extends TestCase
{

    /**
     * @link https://github.com/impress-org/givewp/issues/5575
     * @link https://github.com/impress-org/givewp/issues/5575#issuecomment-770950149
     */
    public function testContentSurroundedByUnmergedTagIsNotScrubbed()
    {
        $connectClient = give(ConnectClient::class);
        $pageView = new PageView(
            $this->createMock(FormRepository::class), $connectClient
        );

        $this->assertContains(
            '<article id="" class="setup-item foo" data-givewp-test="">',
            $pageView->render_template('row-item', ['class' => 'foo'])
        );
    }
}
