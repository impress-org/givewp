<?php

namespace GiveTests\Unit\Actions;

use Give\Campaigns\Actions\RenderDonateButton;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class RenderDonateButtonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testItReturnsEmptyIfFormIsNotFound(): void
    {
        $action = give(RenderDonateButton::class);
        $result = $action(123, 'Donate');

        $this->assertEmpty($result);
    }

    /**
     * @unreleased
     */
    public function testItReturnsEmptyIfFormIsNotPublished(): void
    {
        $form = DonationForm::factory()->create([
            'status' => DonationFormStatus::DRAFT(),
        ]);

        $action = give(RenderDonateButton::class);
        $result = $action($form->id, 'Donate');

        $this->assertEmpty($result);
    }

    /**
     * @unreleased
     */
    public function testItRendersButtonWithBlockControllerWhenFormIsPublished()
    {
        $form = DonationForm::factory()->create([
            'status' => DonationFormStatus::PUBLISHED(),
        ]);

        $blockControllerMock = $this->createMock(BlockRenderController::class);
        $blockControllerMock->expects($this->once())
            ->method('render')
            ->with([
                'formId' => $form->id,
                'openFormButton' => 'Donate Now',
                'formFormat' => 'modal',
            ])
            ->willReturn('<button>Donate Now</button>');

        $action = new RenderDonateButton($blockControllerMock);
        $result = $action($form->id, 'Donate Now');

        $this->assertSame('<button>Donate Now</button>', $result);
    }

    /**
     * @unreleased
     */
    public function testItRendersDefaultButtonIfBlockControllerReturnsNull()
    {
        $form = DonationForm::factory()->create([
            'status' => DonationFormStatus::PUBLISHED(),
        ]);

        $blockControllerMock = $this->createMock(BlockRenderController::class);
        $blockControllerMock->expects($this->once())
            ->method('render')
            ->willReturn(null);

        $action = new RenderDonateButton($blockControllerMock);
        $result = $action($form->id, 'Donate Now');

        $this->assertSame(
            '<button type="button" class="givewp-donation-form-modal__open">Donate Now</button>',
            $result
        );
    }

}
