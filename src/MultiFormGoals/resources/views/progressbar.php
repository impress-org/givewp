<?php
/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 *
 * @since 3.19.1 Format the donation count
 *
 * @var Give\MultiFormGoals\ProgressBar\Model $this
 */

$uniqueId = uniqid('', true);
?>

<div id="<?= esc_attr($uniqueId) ?>" class="give-progress-bar-block">
    <style>
        <?php echo file_get_contents( GIVE_PLUGIN_DIR . 'assets/dist/css/multi-form-goal-block.css' ); ?>
    </style>
    <!-- Target the Progress Bar Block elements using CSS "parts", see https://developer.mozilla.org/en-US/docs/Web/CSS/::part -->
    <div part="goal" class="give-progress-bar-block__goal">
        <div part="progress" class="give-progress-bar-block__progress">
            <?php
            $percent = ($this->getTotal() / $this->getGoal()) * 100; ?>
            <div part="progress-bar" class="give-progress-bar-block__progress-bar" style="width: <?php
            echo esc_attr(min([$percent, 100])); ?>%; background: linear-gradient(180deg, <?php
            echo esc_attr($this->getColor()); ?> 0%, <?php
            echo esc_attr($this->getColor()); ?> 100%), linear-gradient(180deg, #fff 0%, #ccc 100%);"></div>
        </div>
    </div>
    <div part="stats" class="give-progress-bar-block__stats">
        <div part="stat-total" class="give-progress-bar-block__stat">
            <div part="stat-total-value"><?php
                echo esc_html($this->getFormattedTotal()); ?></div>
            <div part="stat-total-label"><?php
                echo __('raised', 'give'); ?></div>
        </div>
        <div part="stat-count" class="give-progress-bar-block__stat">
            <div part="stat-count-value"><?php
                echo esc_html($this->getFormattedDonationCount()); ?></div>
            <div part="stat-count-label"><?php
                echo _n('donation', 'donations', $this->getDonationCount(), 'give'); ?></div>
        </div>
        <div part="stat-goal" class="give-progress-bar-block__stat">
            <div part="stat-goal-value"><?php
                echo esc_html($this->getFormattedGoal()); ?></div>
            <div part="stat-goal-label"><?php
                echo __('goal', 'give'); ?></div>
        </div>
        <?php
        if ( ! empty($this->getEndDate()) && $this->getMinutesRemaining()) : ?>
            <div part="stat-time" class="give-progress-bar-block__stat">
                <div part="stat-time-value"><?php
                    echo esc_html($this->getTimeToGo()); ?></div>
                <div part="stat-time-label"><?php
                    echo esc_html($this->getTimeToGoLabel()); ?></div>
            </div>
        <?php
        endif; ?>
    </div>
</div>
<script>
    (function() {
        const container = document.getElementById('<?php echo $uniqueId; ?>');
        const content = container.innerHTML;
        const shadow = container.attachShadow({mode: 'open'});
        shadow.innerHTML = content;
    })();
</script>
