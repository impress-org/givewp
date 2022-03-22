<?php
defined('ABSPATH') or exit; ?>

<strong>
    <?php
    _e('Activation Error:', 'give'); ?>
</strong>
<?php
_e('You must have', 'give'); ?> <a href="https://givewp.com" target="_blank">GiveWP</a>
<?php
_e('version', 'give'); ?> <?php
echo GIVE_VERSION; ?>+
<?php
printf(esc_html__('for the %1$s add-on to activate', 'give'), GIVE_NEXT_GEN_NAME); ?>.
