import {setFormSettings, useFormSettings, useFormSettingsDispatch} from '@givewp/form-builder/stores/form-settings';
import {__} from '@wordpress/i18n';
import {
    __experimentalNumberControl as NumberControl,
    PanelBody,
    PanelRow,
    SelectControl,
    ToggleControl,
} from '@wordpress/components';

const DonationGoalSettings = () => {
    const {enableDonationGoal, enableAutoClose, goalFormat, goalAmount} = useFormSettings();
    const dispatch = useFormSettingsDispatch();

    const goalFormatOptions = [
        {
            value: 'amount-raised',
            label: __('Amount Raised', 'give'),
        },
        {
            value: 'percentage-raised',
            label: __('Percentage Raised', 'give'),
        },
        {
            value: 'number-donations',
            label: __('Number of Donations', 'give'),
        },
        {
            value: 'number-donors',
            label: __('Number of Donors', 'give'),
        },
    ];

    return (
        <PanelBody title={__('Donation Goal', 'give')} initialOpen={false}>
            <PanelRow>
                <ToggleControl
                    label={__('Enable Donation Goal', 'give')}
                    help={__('Do you want to set a donation goal for this form?', 'give')}
                    checked={enableDonationGoal}
                    onChange={() => {
                        dispatch(setFormSettings({enableDonationGoal: !enableDonationGoal}));
                    }}
                />
            </PanelRow>

            {enableDonationGoal && (
                <>
                    <PanelRow>
                        <ToggleControl
                            label={__('Auto-Close Form', 'give')}
                            help={__(
                                'Do you want to close the donation forms and stop accepting donations once this goal has been met?',
                                'give'
                            )}
                            checked={enableAutoClose}
                            onChange={() => dispatch(setFormSettings({enableAutoClose: !enableAutoClose}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <NumberControl
                            label={__('Goal Amount', 'give')}
                            min={0}
                            value={goalAmount}
                            onChange={(goalAmount) => dispatch(setFormSettings({goalAmount}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <SelectControl
                            label={__('Goal Format', 'give')}
                            help={__(
                                'Do you want to display the total amount raised based on your monetary goal or a percentage? For instance, "$500 of $1,000 raised" or "50% funded" or "1 of 5 donations". You can also display a donor-based goal, such as "100 of 1,000 donors have given".',
                                'give'
                            )}
                            value={goalFormat}
                            options={goalFormatOptions}
                            onChange={(goalFormat) => dispatch(setFormSettings({goalFormat}))}
                        />
                    </PanelRow>
                </>
            )}
        </PanelBody>
    );
};

export default DonationGoalSettings;
