import {setFormSettings, useFormSettings, useFormSettingsDispatch} from '@givewp/form-builder/stores/form-settings';
import {PanelBody, PanelRow, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import DonationInstructions from './donation-instructions';

const OfflineDonationsSettings = () => {
    const {enableOfflineDonations, enableBillingFields} = useFormSettings();
    const dispatch = useFormSettingsDispatch();

    return (
        <PanelBody title={__('Offline Donations', 'give')} initialOpen={false}>
            <PanelRow>
                <ToggleControl
                    label={__('Enable Offline Donations', 'give')}
                    help={__('Do you want to customize the donation instructions for this form?', 'give')}
                    checked={enableOfflineDonations}
                    onChange={() => dispatch(setFormSettings({enableOfflineDonations: !enableOfflineDonations}))}
                />
            </PanelRow>
            {enableOfflineDonations && (
                <>
                    <PanelRow>
                        <ToggleControl
                            label={__('Enable Billing Fields', 'give')}
                            help={__(
                                "DThis option will enable the billing details section for this form's offline donation payment gateway. The fieldset will appear above the offline donation instructions.",
                                'give'
                            )}
                            checked={enableBillingFields}
                            onChange={() => dispatch(setFormSettings({enableBillingFields: !enableBillingFields}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <DonationInstructions />
                    </PanelRow>
                </>
            )}
        </PanelBody>
    );
};

export default OfflineDonationsSettings;
