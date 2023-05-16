<?php

echo '<div class="givewp-donation-options inner-panel">
    <div>
        <img src="' . esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/light-bulb-icon.svg') . '" alt="light-bulb-icon" />
        <p>';
_e(
    'Did you know you can provide donors with flexible recurring donation options to increase their annual giving by 30% or more.',
    'give'
);
echo '</p>
        <a href="https://docs.givewp.com/recurring-legacy" target="_blank" class="givewp-donation-options_external">';
_e('Get more donations');
echo '<img src="' . esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/external-icon.svg') . '" alt="external-link" />
        </a>
    </div>
    <button type="button" class="givewp-donation-options_close">
        <img src="' . esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/close-icon.svg') . '" alt="close-message" />
    </button>
</div>';

?>
