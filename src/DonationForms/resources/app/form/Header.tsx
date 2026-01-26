import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';
import amountFormatter from '@givewp/forms/app/utilities/amountFormatter';
import type { GoalType } from '@givewp/forms/propTypes';
import type { Form as DonationForm } from '@givewp/forms/types';
import { useCallback, useMemo } from 'react';
import { withTemplateWrapper } from '../templates';

const formTemplates = window.givewp.form.templates;

const HeaderTemplate = withTemplateWrapper(formTemplates.layouts.header);
const HeaderTitleTemplate = withTemplateWrapper(formTemplates.layouts.headerTitle);
const HeaderDescriptionTemplate = withTemplateWrapper(formTemplates.layouts.headerDescription);
const GoalTemplate = withTemplateWrapper(formTemplates.layouts.goal);

const HeaderImageTemplate = withTemplateWrapper(formTemplates.layouts.headerImage);

/**
 * @since 4.14.0.0 Make the goal currency use the base currency always
 * @since 3.0.0
 */
export default function Header({form}: {form: DonationForm}) {

    // Get the base currency for goal formatting
    // The goal progress bar should always use the form's base currency, not the selected currency
    // This matches the behavior of legacy forms where only donation amounts are converted
    const goalCurrency = useMemo(() => {
        // If currency switcher is enabled, find the base currency (exchangeRate === 0)
        if (form.currencySwitcherSettings && form.currencySwitcherSettings.length > 0) {
            const baseCurrencySetting = form.currencySwitcherSettings.find(
                (setting) => setting.exchangeRate === 0
            );
            if (baseCurrencySetting) {
                return baseCurrencySetting.id;
            }
        }
        // Fallback to form's default currency
        return form.currency;
    }, [form.currencySwitcherSettings, form.currency]);

    const formatGoalAmount = useCallback((amount: number) => {
        // Use the base currency for goal amounts, not the selected currency
        return amountFormatter(goalCurrency).format(amount);
    }, [goalCurrency]);

    return (
        <DonationFormErrorBoundary>
            <HeaderTemplate
                isMultiStep={form.design?.isMultiStep}
                HeaderImage={() =>
                    form.settings?.designSettingsImageUrl && (
                        <HeaderImageTemplate
                            url={form.settings?.designSettingsImageUrl}
                            alt={form.settings?.designSettingsImageAlt || form.settings?.formTitle}
                            color={form.settings?.designSettingsImageColor}
                            opacity={form.settings?.designSettingsImageOpacity}
                        />
                    )
                }
                Title={() => form.settings?.showHeading && <HeaderTitleTemplate text={form.settings.heading} />}
                Description={() =>
                    form.settings?.showDescription && <HeaderDescriptionTemplate text={form.settings.description} />
                }
                Goal={() =>
                    form.goal?.show && (
                        <GoalTemplate
                            currency={goalCurrency}
                            type={form.goal.type as GoalType}
                            goalLabel={form.goal.label}
                            progressPercentage={Math.round((form.goal.currentAmount / form.goal.targetAmount) * 100)}
                            currentAmount={form.goal.currentAmount}
                            currentAmountFormatted={
                                form.goal.typeIsMoney
                                    ? formatGoalAmount(form.goal.currentAmount)
                                    : form.goal.currentAmount.toString()
                            }
                            targetAmount={form.goal.targetAmount}
                            targetAmountFormatted={
                                form.goal.typeIsMoney
                                    ? formatGoalAmount(form.goal.targetAmount)
                                    : form.goal.targetAmount.toString()
                            }
                            totalRevenue={form.stats.totalRevenue}
                            totalRevenueFormatted={formatGoalAmount(form.stats.totalRevenue)}
                            totalCountValue={form.stats.totalCountValue}
                            totalCountLabel={form.stats.totalCountLabel}
                        />
                    )
                }
            />
        </DonationFormErrorBoundary>
    );
}
