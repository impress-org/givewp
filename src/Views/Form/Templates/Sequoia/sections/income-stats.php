<?php

/**
 * @var int $formId
 */

use Give\DonationForms\DonationQuery;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModal;

/**
 * @since 3.14.0 Use sumIntendedAmount() and getDonationCount() methods to retrieve the proper values for the raised amount and donations count
 */
if ($form->has_goal()) : ?>
    <?php
    $goalStats = give_goal_progress_stats($formId);

    // Setup default raised value
    $raised = give_currency_filter(
        give_format_amount(
            (new DonationQuery())->form($formId)->sumIntendedAmount(),
            [
                'sanitize' => false,
                'decimal' => false,
            ]
        )
    );

    // Setup default count value
    $count = (new ProgressBarModal(['ids' => [$formId]]))->getDonationCount();;

    // Setup default count label
    $countLabel = _n('donation', 'donations', $count, 'give');

    // Setup default goal value
    $goal = give_currency_filter(
        give_format_amount(
            $form->get_goal(),
            [
                'sanitize' => false,
                'decimal' => false,
            ]
        )
    );

    // Change values and labels based on goal format
    switch ($goalStats['format']) {
        case 'percentage':
        {
            $raised = "{$goalStats['progress']}%";
            break;
        }
        case 'donation':
        {
            $count = $goalStats['actual'];
            $goal = $goalStats['goal'];
            break;
        }
        case 'donors':
        {
            $count = $goalStats['actual'];
            $countLabel = _n('donor', 'donors', $count, 'give');
            $goal = $goalStats['goal'];
            break;
        }
    }
    ?>
    <div class="income-stats">
        <div class="raised">
            <div class="number">
                <?php
                echo $raised; ?>
            </div>
            <div class="text"><?php
                _e('raised', 'give'); ?></div>
        </div>
        <div class="count">
            <div class="number">
                <?php
                echo $count; ?>
            </div>
            <div class="text"><?php
                echo $countLabel; ?></div>
        </div>
        <div class="goal">
            <div class="number">
                <?php
                echo $goal; ?>
            </div>
            <div class="text"><?php
                _e('goal', 'give'); ?></div>
        </div>
    </div>
<?php
endif; ?>
