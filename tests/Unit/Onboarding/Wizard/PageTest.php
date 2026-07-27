<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Onboarding\Wizard;

use Give\Onboarding\FormRepository;
use Give\Onboarding\LocaleCollection;
use Give\Onboarding\SettingsRepositoryFactory;
use Give\Onboarding\Wizard\Page;
use Give\Tests\TestCase;

/**
 * Covers the address the wizard hands the Liquid Web portal to return a user to.
 *
 * The portal sends the user back wherever it is told, so the wizard has to name
 * itself rather than another onboarding screen; a user who leaves the wizard to
 * activate should come back to the wizard.
 *
 * @since TBD
 */
final class PageTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testGetReturnUrlPointsAtTheWizard(): void
    {
        $page = $this->makePage();

        $this->assertSame(
            add_query_arg('page', 'give-onboarding-wizard', admin_url()),
            $page->getReturnUrl()
        );
    }

    /**
     * The return URL was previously the setup page, which sent a user who
     * activated from the wizard to a different screen on the way back.
     *
     * @since TBD
     */
    public function testGetReturnUrlIsNotTheSetupPage(): void
    {
        $returnUrl = $this->makePage()->getReturnUrl();

        $this->assertStringContainsString('page=give-onboarding-wizard', $returnUrl);
        $this->assertStringNotContainsString('give-setup', $returnUrl);
    }

    /**
     * @since TBD
     */
    private function makePage(): Page
    {
        return new Page(
            $this->createMock(FormRepository::class),
            $this->createMock(SettingsRepositoryFactory::class),
            $this->createMock(LocaleCollection::class)
        );
    }
}
