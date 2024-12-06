<?php

echo '
        <tr class =givewp-payment-gateway-fee-recovery-recommendation-row>
            <td colspan="10">
                <div class="givewp-payment-gateway-fee-recovery-recommendation inner-panel">
                    <div>
                        <img src="' . esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/list-table/light-bulb-icon.svg') . '"
                             alt="light-bulb-icon" />
                        <p>';
_e(
    'Dramatically reduce the impact of gateway credit card processing fees by providing donors the option to cover that cost regardless of the gateway selected.',
    'give'
);
echo '</p>
                        <a href="https://docs.givewp.com/feerecovery-settings-gateway" target="_blank"
                           class="givewp-payment-gateway-fee-recovery-recommendation_external">';
_e('Recover your fees', 'give');
echo '<img
                                src="' . esc_url(
        GIVE_PLUGIN_URL . 'assets/dist/images/list-table/external-link-icon.svg'
    ) . '"
                                alt="external-link" />
                        </a>
                    </div>
                    <button type="button" class="givewp-payment-gateway-fee-recovery-recommendation_close">
                        <img src="' . esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/close-icon.svg') . '"
                             alt="close-message" />
                    </button>
                </div>
            </td>
        </tr>';
?>
