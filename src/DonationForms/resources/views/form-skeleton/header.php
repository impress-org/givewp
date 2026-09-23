<?php
/**
 * The form header: title, two lines of description, and the two parts with real height when the
 * form has them.
 *
 * @since TBD
 *
 * @var bool $goal
 * @var bool $image
 */
?>
<div class="givewp-embed-skeleton__header">
    <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__title"></span>
    <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__line"></span>
    <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__line givewp-embed-skeleton__line--short"></span>
    <?php if ($image) : ?>
        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__image"></span>
    <?php endif; ?>
    <?php if ($goal) : ?>
        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__goal"></span>
    <?php endif; ?>
</div>
