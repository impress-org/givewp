<?php
/**
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
        <h1 class="give-form-title"><?= $title ?></h1>
        <p class="give-form-description"><?= $description ?></p>
        <?php if ($isSecureBadgeEnabled) : ?>
            <aside class="give-form-secure-badge">
                <svg class="give-form-secure-icon">
                    <use href="#give-icon-lock"/>
                </svg>
                <?= $secureBadgeContent ?>
            </aside>
        <?php endif; ?>
    </div>
    <?php if ($hasGoal) : ?>
        <aside class="give-form-stats-panel">
            <ul class="give-form-stats-panel-list">
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?= $goalStats[ 'raised' ]; ?>
                    </span> <?= __('Raised', 'give'); ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                         <?= $goalStats[ 'count' ]; ?>
                    </span> <?= $goalStats[ 'countLabel' ]; ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?= $goalStats[ 'goal' ]; ?>
                    </span> <?= __('Goal', 'give'); ?>
                </li>
                <li class="give-form-goal-progress">
                    <div
                        role="meter"
                        class="give-form-goal-progress-meter"
                        style="--progress: <?= $goalStats[ 'progress' ]; ?>%"
                        aria-label="<?= sprintf(__('%s of %s goal', 'give'), $goalStats[ 'raised' ], $goalStats[ 'goal' ]); ?>"
                        aria-valuemin="0"
                        aria-valuemax="<?= $goalStats[ 'goalRaw' ]; ?>"
                        aria-valuenow="<?= $goalStats[ 'raisedRaw' ]; ?>"
                        aria-valuetext="<?= $goalStats[ 'progress' ]; ?>%"
                    >
                    </div>
                </li>
            </ul>
        </aside>
    <?php endif; ?>
</div>
