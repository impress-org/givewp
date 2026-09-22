<?php
/**
 * One step of a multi-step design: the step title bar with its progress line, the step's content,
 * the button, and the secure-donation badge under it.
 *
 * @since 4.17.0
 *
 * @var string $content The rendered header or section shown in this step.
 */
?>
<div class="givewp-embed-skeleton__form givewp-embed-skeleton__step">
    <div class="givewp-embed-skeleton__step-header">
        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__step-title"></span>
        <span class="givewp-embed-skeleton__step-progress"></span>
    </div>
    <?= $content ?>
    <div class="givewp-embed-skeleton__section">
        <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__button"></span>
    </div>
    <span class="givewp-embed-skeleton__bar givewp-embed-skeleton__secure"></span>
</div>
