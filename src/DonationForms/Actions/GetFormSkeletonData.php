<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\ValueObjects\DesignSettingsImageStyle;
use Give\Framework\Blocks\BlockModel;

/**
 * The little the on-page embed needs to sketch a form before it loads: the design, whether there
 * is a header and the two header parts with real height (goal bar, image), the block names in
 * each section and how many gateway rows the donor will see. Every value is already on the form
 * or in options, so this costs no extra queries. The embed only knows how to draw the core
 * designs and falls back to a spinner for any other design id.
 *
 * @since 4.17.0
 */
class GetFormSkeletonData
{
    /**
     * @since 4.17.0
     */
    public function __invoke(DonationForm $donationForm): array
    {
        $settings = $donationForm->settings;

        $sections = array_map(static function (BlockModel $block): array {
            if ($block->name !== 'givewp/section' || !$block->innerBlocks) {
                return [$block->name];
            }

            return array_map(static function (BlockModel $innerBlock): string {
                return $innerBlock->name;
            }, $block->innerBlocks->getBlocks());
        }, $donationForm->blocks->getBlocks());

        return [
            'design' => (string)$settings->designId,
            'header' => (bool)$settings->showHeader,
            'goal' => (bool)$settings->enableDonationGoal,
            'image' => $this->imageTakesSpace($settings->designSettingsImageUrl, $settings->designSettingsImageStyle),
            'sections' => array_values($sections),
            'gateways' => count(give(DonationFormRepository::class)->getEnabledPaymentGateways($donationForm->id)),
        ];
    }

    /**
     * Only the "above" and "center" image styles put an image element in the header flow. The
     * background and cover styles paint behind the text and add no height.
     *
     * @since 4.17.0
     *
     * @param DesignSettingsImageStyle|string|null $style
     */
    private function imageTakesSpace(?string $url, $style): bool
    {
        if (!$url || !$style instanceof DesignSettingsImageStyle) {
            return false;
        }

        return $style->isAbove() || $style->isCenter();
    }
}
