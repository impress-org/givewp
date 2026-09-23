<?php
/**
 * One form section: a heading, a line of description, and a field per block. The amount, gateway
 * and summary blocks get their own sketch; every other block is a label and one input row.
 *
 * @since 4.17.0
 *
 * @var string[] $blocks   Block names in the section.
 * @var int      $gateways Enabled gateways; the selected one opens its fields under its row.
 * @var string[] $variants Block name to field variant.
 */
?>
<div class="givewp-embed-skeleton__section">
    <div class="givewp-embed-skeleton__section-header">
        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__heading"></span>
        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__line givewp-embed-skeleton__line--short"></span>
    </div>
    <?php foreach ($blocks as $block) : ?>
        <?php $variant = $variants[$block] ?? 'field'; ?>
        <div class="givewp-embed-skeleton__field givewp-embed-skeleton__field--<?= esc_attr($variant) ?>">
            <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__label"></span>
            <?php if ($variant === 'amount') : ?>
                <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__input givewp-embed-skeleton__input--tall"></span>
                <div class="givewp-embed-skeleton__levels">
                    <?php for ($i = 0; $i < 6; $i++) : ?>
                        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__level"></span>
                    <?php endfor; ?>
                </div>
            <?php elseif ($variant === 'gateways') : ?>
                <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__input"></span>
                <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__panel"></span>
                <?php for ($i = 1; $i < $gateways; $i++) : ?>
                    <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__input"></span>
                <?php endfor; ?>
            <?php elseif ($variant === 'summary') : ?>
                <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__panel givewp-embed-skeleton__panel--tall"></span>
            <?php else : ?>
                <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__input"></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
