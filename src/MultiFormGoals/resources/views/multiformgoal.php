<?php
/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 *
 */

?>


<?php
if ( ! empty($this->innerBlocks)) {
    echo $this->innerBlocks;
} else {
    ?>
    <div class="give-multi-form-goal-block">
        <div class="give-multi-form-goal-block__content">
            <div class="give-multi-form-goal-block__image">
                <img src="<?php
                echo $this->getImageSrc(); ?>" />
            </div>
            <div class="give-multi-form-goal-block__text">
                <h2>
                    <?php
                    echo $this->getHeading(); ?>
                </h2>
                <p>
                    <?php
                    echo $this->getSummary(); ?>
                </p>
            </div>
        </div>
        <?php
        echo $this->getProgressBarOutput(); ?>
    </div>
<?php
} ?>
