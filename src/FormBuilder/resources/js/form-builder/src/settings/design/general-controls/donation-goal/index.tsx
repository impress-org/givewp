import React, {CSSProperties, useState} from 'react';
import {
    __experimentalNumberControl as NumberControl,
    Icon,
    PanelBody,
    PanelRow,
    SelectControl,
    TextareaControl,
    ToggleControl,
} from '@wordpress/components';
import {close, external} from '@wordpress/icons';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import {__} from '@wordpress/i18n';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';
import DatePicker from '@givewp/form-builder/components/DatePicker';
import {updateUserNoticeOptions} from '@givewp/campaigns/utils';

declare const window: {
    goalNotificationData: {
        actionUrl: string;
        isDismissed: boolean;
    };
} & Window;

const noticeStyles = {
    notice: {
        position: 'relative',
        display: 'flex',
        flexDirection: 'column',
        gap: 8,
        padding: 12,
        borderRadius: 2,
        backgroundColor: '#f2f2f2',
        color: '#0e0e0e',
        fontSize: 12,
    },
    title: {
        fontWeight: 600,
    },
    closeIcon: {
        cursor: 'pointer',
        height: 16,
        width: 16,
        position: 'absolute',
        right: 12,
        top: 12,
    },
    externalIcon: {
        height: 18,
        width: 18,
        fill: '#2271b1',
        float: 'left',
        marginTop: 2,
        marginRight: 8,
    },
} as CSSProperties;


const {goalTypeOptions, goalSourceOptions, goalProgressOptions, showFormGoalNotice} = getFormBuilderWindowData();

const DonationGoal = ({dispatch}) => {
    const {
        settings: {
            enableDonationGoal,
            enableAutoClose,
            goalAchievedMessage,
            goalType,
            goalSource,
            goalProgressType,
            goalAmount,
            goalStartDate,
            goalEndDate,
        },
    } = useFormState();

    const {publishGoal, publishGoalType, publishGoalSource} = useDonationFormPubSub();
    const [showNotice, setShowNotice] = useState(!window.goalNotificationData.isDismissed);
    const [showGoalSourceNotice, setShowGoalSourceNotice] = useState(showFormGoalNotice);

    const selectedGoalType = goalTypeOptions.find((option) => option.value === goalType);
    const selectedGoalDescription = selectedGoalType ? selectedGoalType.description : '';
    const selectedGoalSource = goalSourceOptions.find((option) => option.value === goalSource);
    const selectedGoalSourceDescription = selectedGoalSource ? selectedGoalSource.description : '';
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
                            label={__('Form Goal', 'give')}
                            value={goalSource}
                            options={goalSourceOptions}
                            onChange={(goalSource: string) => {
                                dispatch(setFormSettings({goalSource}));
                                publishGoalSource({goalSource});
                            }}
                            help={selectedGoalSourceDescription}
                        />
                    </PanelRow>
                    {goalSource === 'form' ? (
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

                            {goalProgressType === 'custom' && (
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

                                    {showNotice && (
                                        <PanelRow>
                                            <div style={noticeStyles['notice']}>
                                        <span style={noticeStyles['title']}>
                                            {__('What is custom goal progress?', 'give')}
                                            <Icon
                                                icon={close}
                                                style={noticeStyles['closeIcon']}
                                                onClick={() => {
                                                    fetch(window.goalNotificationData.actionUrl, {method: 'POST'})
                                                        .then(() => {
                                                            setShowNotice(false);
                                                        });
                                                }}
                                            />
                                        </span>
                                                <span>
                                            {__('You can now set a time frame to show progress toward your goal.', 'give')}
                                        </span>
                                                <span>
                                            <a href="https://docs.givewp.com/goal-timeframe" target="_blank">
                                                <Icon
                                                    style={noticeStyles['externalIcon']}
                                                    icon={external}
                                                />
                                                {__('Learn more about how to use the custom goal progress.', 'give')}
                                            </a>
                                        </span>
                                            </div>
                                        </PanelRow>
                                    )}
                                </>
                            )}
                        </>
                    ) : (
                        <>
                            {showGoalSourceNotice && (
                                <PanelRow>
                                    <div style={noticeStyles['notice']}>
                                        <span style={noticeStyles['title']}>
                                            {__('Campaign and Form Goal', 'give')}
                                            <Icon
                                                icon={close}
                                                style={noticeStyles['closeIcon']}
                                                onClick={() => {
                                                    updateUserNoticeOptions('givewp_campaign_form_goal_notice')
                                                        .then(() => setShowGoalSourceNotice(false))
                                                }}
                                            />
                                        </span>
                                        <span>
                                            {__('You can either use the campaign goal or set a separate goal specifically for this form.', 'give')}
                                        </span>
                                        <span>
                                            <a href="https://docs.givewp.com/goal-timeframe" target="_blank">
                                                <Icon
                                                    style={noticeStyles['externalIcon']}
                                                    icon={external}
                                                />
                                                {__('Learn more about the difference', 'give')}
                                            </a>
                                        </span>
                                    </div>
                                </PanelRow>
                            )}
                        </>
                    )}
                </>
            )}
        </PanelBody>
    );
};

export default DonationGoal;
