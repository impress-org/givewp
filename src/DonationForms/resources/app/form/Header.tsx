import {useCallback} from 'react';
import {withTemplateWrapper} from '../templates';
import type {GoalType} from '@givewp/forms/propTypes';
import amountFormatter from '@givewp/forms/app/utilities/amountFormatter';
import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';

const formTemplates = window.givewp.form.templates;

const HeaderTemplate = withTemplateWrapper(formTemplates.layouts.header);
const HeaderTitleTemplate = withTemplateWrapper(formTemplates.layouts.headerTitle);
const HeaderDescriptionTemplate = withTemplateWrapper(formTemplates.layouts.headerDescription);
const GoalTemplate = withTemplateWrapper(formTemplates.layouts.goal);

const headerTextWrapperTemplate = formTemplates.layouts.headerTextWrapper;



/**
 * @since 3.0.0
 */
export default function Header({form}) {

    const formatGoalAmount = useCallback((amount: number) => {
        return amountFormatter(form.currency, {
            maximumFractionDigits: 0,
        }).format(amount);
    }, []);

    return (
        <DonationFormErrorBoundary>
            <HeaderTemplate
                TextWrapper={headerTextWrapperTemplate}
                Title={() => form.settings?.showHeading && <HeaderTitleTemplate text={form.settings.heading} />}
                Description={() =>
                    form.settings?.showDescription && <HeaderDescriptionTemplate text={form.settings.description} />
                }
                Goal={() =>
                    form.goal?.show && (
                        <GoalTemplate
                            currency={form.currency}
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
