<?php
/**
 * The form skeleton: a grey sketch of the form that holds its space while it loads. Rendered by
 * Give\DonationForms\Actions\RenderFormSkeleton, which validates the design id; the parts live in
 * the form-skeleton directory. The bars are decorative, so the whole sketch is hidden from
 * assistive tech and the element that embeds it carries the loading announcement.
 *
 * @since 4.17.0
 *
 * @var string     $design   One of classic, multi-step, two-panel-steps.
 * @var bool       $header   Whether the form shows a header.
 * @var bool       $goal     Whether the header has a goal bar.
 * @var bool       $image    Whether the header has an image that takes space.
 * @var string[][] $sections Block names per section, in form order.
 * @var int        $gateways Enabled gateways, one row each.
 * @var string[]   $variants Block name to field variant, for the section part.
 */

use Give\Framework\Views\View;

$modifier = $design === 'two-panel-steps' ? 'two-panel' : $design;
$headerHtml = $header ? View::load('DonationForms.form-skeleton/header', compact('goal', 'image')) : '';
$firstSectionHtml = View::load('DonationForms.form-skeleton/section', [
    'blocks' => $sections[0] ?? [],
    'gateways' => $gateways,
    'variants' => $variants,
]);
?>
<div class="givewp-embed-skeleton givewp-embed-skeleton--<?= esc_attr($modifier) ?>" aria-hidden="true">
    <?php if ($design === 'classic') : ?>
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $headerHtml is View::load() output, which escapes internally. ?>
        <?= $headerHtml ?>
        <div class="givewp-embed-skeleton__form">
            <?php foreach ($sections as $blocks) : ?>
                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- View::load() output escapes internally. ?>
                <?= View::load('DonationForms.form-skeleton/section', compact('blocks', 'gateways', 'variants')) ?>
            <?php endforeach; ?>
            <div class="givewp-embed-skeleton__section">
                <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__button"></span>
            </div>
        </div>
    <?php elseif ($design === 'two-panel-steps') : ?>
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $headerHtml is View::load() output, which escapes internally. ?>
        <?= $headerHtml ?>
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- View::load() output escapes internally; $firstSectionHtml is likewise pre-escaped. ?>
        <?= View::load('DonationForms.form-skeleton/step', ['content' => $firstSectionHtml]) ?>
    <?php else : ?>
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- View::load() output escapes internally; $headerHtml/$firstSectionHtml are likewise pre-escaped. ?>
        <?= View::load('DonationForms.form-skeleton/step', ['content' => $header ? $headerHtml : $firstSectionHtml]) ?>
    <?php endif; ?>
</div>
