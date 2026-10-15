<?php
/**
 * @since TBD Escape output.
 * @var string $title
 * @var string $description
 * @var bool $isSecureBadgeEnabled
 * @var bool $secureBadgeContent
 * @var bool $hasGoal
 * @var array $goalStats
 */

?>
<div class="give-form-header">
    <div class="give-form-header-top-wrap">
        <h1 class="give-form-title"><?= esc_html($title) ?></h1>
        <p class="give-form-description"><?= wp_kses_post($description) ?></p>
        <?php if ($isSecureBadgeEnabled) : ?>
            <aside class="give-form-secure-badge">
                <svg class="give-form-secure-icon">
                    <use href="#give-icon-lock"/>
                </svg>
                <?= wp_kses_post($secureBadgeContent) ?>
            </aside>
        <?php endif; ?>
    </div>
    <?php if ($hasGoal) : ?>
        <aside class="give-form-stats-panel">
            <ul class="give-form-stats-panel-list">
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?= esc_html($goalStats[ 'raised' ]); ?>
                    </span> <?= esc_html__('Raised', 'give'); ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                         <?= esc_html($goalStats[ 'count' ]); ?>
                    </span> <?= esc_html($goalStats[ 'countLabel' ]); ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?= esc_html($goalStats[ 'goal' ]); ?>
                    </span> <?= esc_html__('Goal', 'give'); ?>
                </li>
                <li class="give-form-goal-progress">
                    <div
                        role="meter"
                        class="give-form-goal-progress-meter"
                        style="--progress: <?= esc_attr($goalStats[ 'progress' ]); ?>%"
                        aria-label="<?= esc_attr(sprintf(__('%s of %s goal', 'give'), $goalStats[ 'raised' ], $goalStats[ 'goal' ])); ?>"
                        aria-valuemin="0"
                        aria-valuemax="<?= esc_attr($goalStats[ 'goalRaw' ]); ?>"
                        aria-valuenow="<?= esc_attr($goalStats[ 'raisedRaw' ]); ?>"
                        aria-valuetext="<?= esc_attr($goalStats[ 'progress' ]); ?>%"
                    >
                    </div>
                </li>
            </ul>
        </aside>
    <?php endif; ?>
</div>
