<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 *
 * @since TBD Escape output and replace short echo tags with escaped echo.
 *
 * @var Give\MultiFormGoals\MultiFormGoal\Model $this
 */
?>


<?php
if ( ! empty($this->getInnerBlocks())) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renders the block's inner blocks markup, already escaped internally.
    echo $this->getInnerBlocks();
} else {
    ?>
    <div class="give-multi-form-goal-block">
        <div class="give-multi-form-goal-block__content">
            <div class="give-multi-form-goal-block__image">
                <img src="<?php echo esc_url($this->getImageSrc()); ?>"  alt="goal image"/>
            </div>
            <div class="give-multi-form-goal-block__text">
                <h2>
                    <?php echo esc_html($this->getHeading()); ?>
                </h2>
                <p>
                    <?php echo esc_html($this->getSummary()); ?>
                </p>
            </div>
        </div>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renders progressbar.php, already escaped internally.
        echo $this->getProgressBarOutput(); ?>
    </div>
<?php
} ?>
