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
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';

const {goalTypeOptions} = getFormBuilderWindowData();

const DonationGoalSettings = () => {
    const {
        settings: {enableDonationGoal, enableAutoClose, goalAchievedMessage, goalType, goalAmount},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const {publishGoal, publishGoalType} = useDonationFormPubSub();

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
                        publishGoal({show: !enableDonationGoal});
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
                            onChange={(goalType: string) => {
                                dispatch(setFormSettings({goalType}))
                                publishGoalType(goalType);
                            }}
                            help={selectedGoalDescription}
                        />
                    </PanelRow>
                    <PanelRow>
                        {selectedGoalType.isCurrency ? (
                            <CurrencyControl
                                label={__('Goal Amount', 'give')}
                                min={0}
                                value={goalAmount}
                                onValueChange={debounce((goalAmount) => {
                                    dispatch(setFormSettings({goalAmount}));
                                    publishGoal({targetAmount: goalAmount});
                                }, 500)}
                            />
                        ) : (
                            <NumberControl
                                label={__('Goal Amount', 'give')}
                                min={0}
                                value={goalAmount}
                                onChange={debounce((goalAmount: number) => {
                                    dispatch(setFormSettings({goalAmount}))
                                    publishGoal({targetAmount: goalAmount});
                                }, 100)}
                            />
                        )}
                    </PanelRow>
                </>
            )}
        </PanelBody>
    );
};

export default DonationGoalSettings;
