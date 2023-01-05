<?php
/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 * @var Give\MultiFormGoals\MultiFormGoal\Model $this
 */
?>


<?php
if ( ! empty($this->getInnerBlocks())) {
    echo $this->getInnerBlocks();
} else {
    ?>
    <div class="give-multi-form-goal-block">
        <div class="give-multi-form-goal-block__content">
            <div class="give-multi-form-goal-block__image">
                <img src="<?= esc_url($this->getImageSrc()) ?>"  alt="goal image"/>
            </div>
            <div class="give-multi-form-goal-block__text">
                <h2>
                    <?= esc_html($this->getHeading()) ?>
                </h2>
                <p>
                    <?= esc_html($this->getSummary()) ?>
                </p>
            </div>
        </div>
        <?php
        echo $this->getProgressBarOutput(); ?>
    </div>
<?php
} ?>
