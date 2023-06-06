<?php

echo '<div class="givewp-donation-options inner-panel">
    <div>
        <img src="' . GIVE_PLUGIN_URL . 'assets/dist/images/list-table/light-bulb-icon.svg' . '" alt="light-bulb-icon" />
        <p>';
_e(
    'Did you know? You can provide donors with flexible recurring donation options! Recurring donors give up to 30% more.',
    'give'
);
echo '</p>
        <a href="https://docs.givewp.com/recurring-legacy" target="_blank" class="givewp-donation-options_external">';
_e('Get more donations');
echo '<img src="' . GIVE_PLUGIN_URL . 'assets/dist/images/list-table/external-link-icon.svg' . '" alt="external-link" />
        </a>
    </div>
    <button type="button" class="givewp-donation-options_close">
        <img src="' . GIVE_PLUGIN_URL . 'assets/dist/images/admin/close-icon.svg' . '" alt="close-message" />
    </button>
</div>';

?>
