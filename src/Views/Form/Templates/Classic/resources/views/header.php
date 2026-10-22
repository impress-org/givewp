<?php
/**
 * @since TBD Escape output, replace short echo tags with escaped echo, number the placeholders, and add translators comments.
 *
 * @var string $title
 * @var string $description
 * @var bool $isSecureBadgeEnabled
 * @var bool $secureBadgeContent
 * @var bool $hasGoal
 * @var array $goalStats
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="give-form-header">
    <div class="give-form-header-top-wrap">
        <h1 class="give-form-title"><?php echo esc_html($title); ?></h1>
        <p class="give-form-description"><?php echo wp_kses_post($description); ?></p>
        <?php if ($isSecureBadgeEnabled) : ?>
            <aside class="give-form-secure-badge">
                <svg class="give-form-secure-icon">
                    <use href="#give-icon-lock"/>
                </svg>
                <?php echo wp_kses_post($secureBadgeContent); ?>
            </aside>
        <?php endif; ?>
    </div>
    <?php if ($hasGoal) : ?>
        <aside class="give-form-stats-panel">
            <ul class="give-form-stats-panel-list">
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?php echo esc_html($goalStats[ 'raised' ]); ?>
                    </span> <?php echo esc_html__('Raised', 'give'); ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                         <?php echo esc_html($goalStats[ 'count' ]); ?>
                    </span> <?php echo esc_html($goalStats[ 'countLabel' ]); ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?php echo esc_html($goalStats[ 'goal' ]); ?>
                    </span> <?php echo esc_html__('Goal', 'give'); ?>
                </li>
                <li class="give-form-goal-progress">
                    <div
                        role="meter"
                        class="give-form-goal-progress-meter"
                        style="--progress: <?php echo esc_attr($goalStats[ 'progress' ]); ?>%"
                        aria-label="<?php echo esc_attr(sprintf(/* translators: 1: Amount raised, 2: Goal amount */ __('%1$s of %2$s goal', 'give'), $goalStats[ 'raised' ], $goalStats[ 'goal' ])); ?>"
                        aria-valuemin="0"
                        aria-valuemax="<?php echo esc_attr($goalStats[ 'goalRaw' ]); ?>"
                        aria-valuenow="<?php echo esc_attr($goalStats[ 'raisedRaw' ]); ?>"
                        aria-valuetext="<?php echo esc_attr($goalStats[ 'progress' ]); ?>%"
                    >
                    </div>
                </li>
            </ul>
        </aside>
    <?php endif; ?>
</div>
