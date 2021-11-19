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
        <?php
        if ($isSecureBadgeEnabled) : ?>
            <aside class="give-form-secure-badge">
                <svg class="give-form-secure-icon">
                    <use href="#give-icon-lock"/>
                </svg>
                <?= $secureBadgeContent ?>
            </aside>
        <?php
        endif; ?>
    </div>
    <?php
    if ($hasGoal): ?>
        <aside class="give-form-stats-panel">
            <ul class="give-form-stats-panel-list">
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?= $goalStats[ 'raised' ]; ?>
                    </span> <?= __('raised', 'give'); ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                         <?= $goalStats[ 'count' ]; ?>
                    </span> <?= $goalStats[ 'countLabel' ]; ?>
                </li>
                <li class="give-form-stats-panel-stat">
                    <span class="give-form-stats-panel-stat-number">
                        <?= $goalStats[ 'goal' ]; ?>
                    </span> <?= __('goal', 'give'); ?>
                </li>
                <li class="give-form-goal-progress">
                    <div class="give-form-goal-progress-meter" role="meter" aria-labelledby="meter-label" aria-valuemin="0" aria-valuemax="<?= $goalStats[ 'goal' ]; ?>"
                         aria-valuenow="<?= $goalStats[ 'progress' ]; ?>%" aria-valuetext="<?= $goalStats[ 'progress' ]; ?>%">
                        <div id="meter-label" hidden>
                            <?= sprintf(__('%s of %s goal', 'give'), $goalStats[ 'raised' ], $goalStats[ 'goal' ]); ?>
                        </div>
                        <div
                            class="give-form-goal-progress-meter-bar"
                            style="width: <?= $goalStats[ 'progress' ]; ?>%;"></div>
                    </div>
                </li>
            </ul>
        </aside>
    <?php
    endif; ?>
</div>
