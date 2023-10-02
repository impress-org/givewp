import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {__} from '@wordpress/i18n';
import {
    __experimentalNumberControl as NumberControl,
    PanelBody,
    PanelRow,
    SelectControl,
    TextareaControl,
    ToggleControl,
} from '@wordpress/components';
import debounce from 'lodash.debounce';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const {isRecurringEnabled} = getFormBuilderWindowData();

const oneTimeGoalOptions = [
    {
        value: 'amount',
        label: __('Amount Raised', 'give'),
        description: __('The total amount raised for the form', 'give'),
    },
    {
        value: 'donations',
        label: __('Number of Donations', 'give'),
        description: __('The total number of donations made for the form', 'give'),
    },
    {
        value: 'donors',
        label: __('Number of Donors', 'give'),
        description: __('The total number of unique donors who have donated to the form', 'give'),
    },
];

const recurringGoalTypeOptions = [
    {
        value: 'amountFromSubscriptions',
        label: __('Amount Raised (from subscriptions)', 'give'),
        description: __('The total amount raised for the form (from subscriptions)', 'give'),
    },
    {
        value: 'subscriptions',
        label: __('Number of Subscriptions', 'give'),
        description: __('The total number of subscriptions made for the form', 'give'),
    },
    {
        value: 'donorsFromSubscriptions',
        label: __('Number of Donors (from subscriptions)', 'give'),
        description: __('The total number of unique donors who have donated to the form (from subscriptions)', 'give'),
    },
];

const goalTypeOptions = isRecurringEnabled ? oneTimeGoalOptions.concat(recurringGoalTypeOptions) : oneTimeGoalOptions;

const DonationGoalSettings = () => {
    const {
        settings: {enableDonationGoal, enableAutoClose, goalAchievedMessage, goalType, goalAmount},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    if (isRecurringEnabled) {
        goalTypeOptions.concat(recurringGoalTypeOptions);
    }

    const selectedGoalType = goalTypeOptions.find((option) => option.value === goalType);
    const selectedGoalDescription = selectedGoalType ? selectedGoalType.description : '';

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
                    {enableAutoClose && (
                        <PanelRow>
                            <TextareaControl
                                label={__('Goal Achieved Message', 'give')}
                                value={goalAchievedMessage}
                                onChange={(goalAchievedMessage) =>
                                    dispatch(setFormSettings({goalAchievedMessage: goalAchievedMessage}))
                                }
                            />
                        </PanelRow>
                    )}
                    <PanelRow>
                        <SelectControl
                            label={__('Goal Type', 'give')}
                            value={goalType}
                            options={goalTypeOptions}
                            onChange={(goalType) => dispatch(setFormSettings({goalType: goalType}))}
                            help={selectedGoalDescription}
                        />
                    </PanelRow>
                    <PanelRow>
                        <NumberControl
                            label={__('Goal Amount', 'give')}
                            min={0}
                            value={goalAmount}
                            onChange={debounce((goalAmount) => dispatch(setFormSettings({goalAmount})), 100)}
                        />
                    </PanelRow>
                </>
            )}
        </PanelBody>
    );
};

export default DonationGoalSettings;
