<?php
/**
 * @var string $title
 * @var string $description
 * @var bool   $isSecureBadgeEnabled
 * @var bool   $secureBadgeContent
 */
?>
<div class="give-form-header">
    <div class="give-form-header-top-wrap">
        <h1 class="give-form-title"><?= $title ?></h1>
        <p class="give-form-description"><?= $description ?></p>
        <?php if ($isSecureBadgeEnabled) : ?>
        <aside class="give-form-secure-badge">
            <svg class="give-form-secure-icon">
                <use href="#give-icon-lock" />
            </svg>
            <?= $secureBadgeContent ?>
        </aside>
        <?php endif; ?>
    </div>
    <aside class="give-form-stats-panel">
        <ul class="give-form-stats-panel-list">
            <li class="give-form-stats-panel-stat">
                <span class="give-form-stats-panel-stat-number">$0</span> raised
            </li>
            <li class="give-form-stats-panel-stat">
                <span class="give-form-stats-panel-stat-number">50</span> donations
            </li>
            <li class="give-form-stats-panel-stat">
                <span class="give-form-stats-panel-stat-number">$10,000</span> goal
            </li>
            <li class="give-form-goal-progress">
                <div class="give-form-goal-progress-meter" role="meter" aria-labelledby="meter-label" aria-valuemin="0" aria-valuemax="1000000" aria-valuenow="200000" aria-valuetext="20%">
                    <div id="meter-label" hidden>
                        $20,000 of $1,000,000 goal
                    </div>
                    <div class="give-form-goal-progress-meter-bar" style="width: 20%"></div>
                </div>
            </li>
        </ul>
    </aside>
</div>
