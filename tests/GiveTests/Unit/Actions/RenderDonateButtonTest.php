<?php

namespace GiveTests\Unit\Actions;

use Give\Campaigns\Actions\RenderDonateButton;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\Models\DonationForm as LegacyDonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 */
class RenderDonateButtonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased Update test with new action signature
     * @since 4.0.0
     */
    public function testItReturnsEmptyIfFormIsNotFound(): void
    {
        $campaign = Campaign::factory()->create([
            'defaultFormId' => 123,
        ]);

        $action = give(RenderDonateButton::class);
        $result = $action($campaign, [], 'Donate');

        $this->assertEmpty($result);
    }

    /**
     * @unreleased Update test with new action signature
     * @since 4.0.0
     */
    public function testItReturnsEmptyIfFormIsNotPublished(): void
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);
        $form->status = DonationFormStatus::DRAFT();
        $form->save();

        $action = give(RenderDonateButton::class);
        $result = $action($campaign, [], 'Donate');

        $this->assertEmpty($result);
    }

    /**
     * @unreleased Update test with new action signature
     * @since 4.0.0
     */
    public function testItRendersV3ButtonWhenFormIsPublished(): void
    {
        $campaign = Campaign::factory()->create();

        $action = give(RenderDonateButton::class);
        $result = $action($campaign, [], 'Donate Now');

        // Assert the root div contains the correct class and data attributes
        $this->assertStringContainsString("class='root-data-givewp-embed'", $result);
        $this->assertStringContainsString("data-form-url='http://example.org/?post_type=give_forms&#038;p={$campaign->defaultFormId}'", $result);
        $this->assertStringContainsString("data-form-view-url='http://example.org/?givewp-route=donation-form-view&form-id={$campaign->defaultFormId}'", $result);
        $this->assertStringContainsString("data-src='http://example.org/?givewp-route=donation-form-view&form-id={$campaign->defaultFormId}'", $result);
        $this->assertStringContainsString("data-form-format='modal'", $result);
        $this->assertStringContainsString("data-open-form-button='Donate Now'", $result);

        // Assert the CSS custom properties are set
        $this->assertStringContainsString("--givewp-primary-color: {$campaign->primaryColor};", $result);
        $this->assertStringContainsString("--givewp-secondary-color: {$campaign->secondaryColor};", $result);

        // Assert the overall structure
        $this->assertStringStartsWith("<div class='root-data-givewp-embed'", $result);
        $this->assertStringEndsWith("</div>", $result);
    }

    /**
     * @unreleased Update test with new action signature
     * @since 4.0.0
     */
    public function testItRendersDefaultButtonIfInEditor(): void
    {
        define('REST_REQUEST', true);

        $campaign = Campaign::factory()->create();

        $action = give(RenderDonateButton::class);
        $result = $action($campaign, [], 'Donate Now');

        $this->assertStringContainsString('<button type="button" class="givewp-donation-form-modal__open">Donate Now</button>', $result);
    }

}
