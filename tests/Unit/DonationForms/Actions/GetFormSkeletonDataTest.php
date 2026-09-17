<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\GetFormSkeletonData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * This data is the contract the embed's skeleton is drawn from, so every branch that changes a
 * value gets pinned here.
 *
 * @since TBD
 */
class GetFormSkeletonDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testReturnsDesignHeaderFlagsAndBlockNamesPerSection()
    {
        give_update_option('gateways_v3', ['manual' => true, 'not-registered' => true, 'offline' => false]);

        $form = $this->form([
            'designId' => 'multi-step',
            'showHeader' => true,
            'enableDonationGoal' => true,
            'designSettingsImageUrl' => 'https://example.org/hero.jpg',
            'designSettingsImageStyle' => 'above',
        ], [
            $this->section(['givewp/donation-amount']),
            $this->section(['givewp/donor-name', 'givewp/email']),
        ]);

        $this->assertSame([
            'design' => 'multi-step',
            'header' => true,
            'goal' => true,
            'image' => true,
            'sections' => [['givewp/donation-amount'], ['givewp/donor-name', 'givewp/email']],
            'gateways' => 1,
        ], (new GetFormSkeletonData())($form));
    }

    /**
     * @since TBD
     */
    public function testBackgroundImageAddsNoHeight()
    {
        $form = $this->form([
            'showHeader' => true,
            'designSettingsImageUrl' => 'https://example.org/hero.jpg',
            'designSettingsImageStyle' => 'background',
        ]);

        $this->assertFalse((new GetFormSkeletonData())($form)['image']);
    }

    /**
     * @since TBD
     */
    public function testImageStyleWithoutUrlIsNoImage()
    {
        $form = $this->form(['designSettingsImageStyle' => 'above']);

        $this->assertFalse((new GetFormSkeletonData())($form)['image']);
    }

    /**
     * @since TBD
     */
    public function testTopLevelBlocksThatAreNotSectionsBecomeTheirOwnSection()
    {
        $form = $this->form([], [
            BlockModel::make(['name' => 'givewp/section', 'attributes' => [], 'innerBlocks' => []]),
            BlockModel::make(['name' => 'givewp/payment-gateways', 'attributes' => []]),
        ]);

        $this->assertSame(
            [[], ['givewp/payment-gateways']],
            (new GetFormSkeletonData())($form)['sections']
        );
    }

    /**
     * @since TBD
     */
    public function testNoEnabledGatewaysIsZero()
    {
        give_update_option('gateways_v3', []);

        $this->assertSame(0, (new GetFormSkeletonData())(DonationForm::factory()->create())['gateways']);
    }

    /**
     * A saved form whose settings and blocks are then swapped in memory. Saving runs the blocks
     * through the fields API, which wants fully formed blocks; the skeleton only reads their names.
     */
    private function form(array $settings, array $blocks = null): DonationForm
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();
        $form->settings = FormSettings::fromArray($settings);

        if ($blocks !== null) {
            $form->blocks = BlockCollection::make($blocks);
        }

        return $form;
    }

    private function section(array $blockNames): BlockModel
    {
        return BlockModel::make([
            'name' => 'givewp/section',
            'attributes' => [],
            'innerBlocks' => array_map(static function (string $name): array {
                return ['name' => $name, 'attributes' => []];
            }, $blockNames),
        ]);
    }
}
