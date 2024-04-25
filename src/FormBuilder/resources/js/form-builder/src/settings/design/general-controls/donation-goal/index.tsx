import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {__} from '@wordpress/i18n';
import {
    __experimentalNumberControl as NumberControl,
    PanelBody,
    PanelRow,
    SelectControl,
    TextareaControl,
    ToggleControl,
} from '@wordpress/components';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';
import DatePicker from '@givewp/form-builder/components/DatePicker';

const {goalTypeOptions, goalProgressOptions} = getFormBuilderWindowData();

const DonationGoal = ({dispatch}) => {
    const {
        settings: {
            enableDonationGoal,
            enableAutoClose,
            goalAchievedMessage,
            goalType,
            goalProgressType,
            goalAmount,
            goalStartDate,
            goalEndDate
        },
    } = useFormState();

    const {publishGoal, publishGoalType} = useDonationFormPubSub();

    const selectedGoalType = goalTypeOptions.find((option) => option.value === goalType);
    const selectedGoalDescription = selectedGoalType ? selectedGoalType.description : '';
    const selectedGoalProgressType = goalProgressOptions.find((option) => option.value === goalProgressType);
    const selectedGoalProgressDescription = selectedGoalProgressType ? selectedGoalProgressType.description : '';

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
                        <SelectControl
                            label={__('Goal Type', 'give')}
                            value={goalType}
                            options={goalTypeOptions}
                            onChange={(goalType: string) => {
                                dispatch(setFormSettings({goalType}));
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
                                onValueChange={(goalAmount) => {
                                    dispatch(setFormSettings({goalAmount}));
                                    publishGoal({targetAmount: Number(goalAmount)});
                                }}
                            />
                        ) : (
                            <NumberControl
                                label={__('Goal Amount', 'give')}
                                min={0}
                                value={goalAmount}
                                onChange={(goalAmount) => {
                                    dispatch(setFormSettings({goalAmount}));
                                    publishGoal({targetAmount: Number(goalAmount)});
                                }}
                            />
                        )}
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Auto-Close Form', 'give')}
                            help={__(
                                'Do you want to close the donation forms and stop accepting donations once this goal has been met?',
                                'give',
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
                            label={__('Goal Progress', 'give')}
                            value={goalProgressType}
                            options={goalProgressOptions}
                            onChange={(goalProgressType: string) => {
                                dispatch(setFormSettings({goalProgressType}));
                            }}
                            help={selectedGoalProgressDescription}
                        />
                    </PanelRow>

                    {selectedGoalProgressType.isCustom && (
                        <>
                            <DatePicker
                                showTimeSelector
                                label={__('Start Date', 'give')}
                                placeholder={__('Select Date', 'give')}
                                date={goalStartDate}
                                invalidDateAfter={goalEndDate}
                                onSelect={(goalStartDate) => {
                                    dispatch(setFormSettings({goalStartDate}));
                                }}
                            />

                            <DatePicker
                                showTimeSelector
                                label={__('End Date', 'give')}
                                placeholder={__('Select Date', 'give')}
                                date={goalEndDate}
                                invalidDateBefore={goalStartDate}
                                onSelect={(goalEndDate) => {
                                    dispatch(setFormSettings({goalEndDate}));
                                }}
                            />
                        </>
                    )}
                </>
            )}
        </PanelBody>
    );
};

export default DonationGoal;
