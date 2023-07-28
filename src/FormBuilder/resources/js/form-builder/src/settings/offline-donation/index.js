import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {PanelBody, PanelRow, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import DonationInstructions from './donation-instructions';

const OfflineDonationsSettings = () => {
    const {
        settings: {enableOfflineDonations, offlineDonationsCustomize},
    } = useFormState();
    const dispatch = useFormStateDispatch();

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
                        <DonationInstructions />
                    </PanelRow>
                </>
            )}
        </PanelBody>
    );
};

export default OfflineDonationsSettings;
