<?php
defined('ABSPATH') or exit; ?>

<div class="notice notice-error">
    <p>
        <strong><?php
            _e('Activation Error:', 'give'); ?></strong>
        <?php
        _e('You must have the', 'give'); ?> <a href="https://givewp.com" target="_blank">GiveWP</a>
        <?php
        printf(
            __('plugin installed and activated for the %s add-on to activate', 'give'),
            GIVE_NEXT_GEN_NAME
        ); ?>
    </p>
</div>
