import type {Form} from '@givewp/forms/types';
import {Section} from '@givewp/forms/types';
import {DonationFormMultiStepStateProvider} from './store';
import {StepObject} from '@givewp/forms/app/form/MultiStepForm/types';
import StepForm from '@givewp/forms/app/form/MultiStepForm/components/StepForm';
import getSectionFieldNames from '@givewp/forms/app/form/MultiStepForm/utilities/getSectionFieldNames';
import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';
import {withTemplateWrapper} from '@givewp/forms/app/templates';
import SectionNode from '@givewp/forms/app/fields/SectionNode';
import Steps from '@givewp/forms/app/form/MultiStepForm/components/Steps';
import HeaderStep from '@givewp/forms/app/form/MultiStepForm/components/HeaderStep';
import {DonationSummaryProvider} from '@givewp/forms/app/store/donation-summary';

const FormSectionTemplate = withTemplateWrapper(window.givewp.form.templates.layouts.section, 'section');

/**
 * @since 3.0.0
 */
const convertSectionsToSteps = (sections: Section[], hasFirstStep: boolean) => {
    const totalSteps = hasFirstStep ? sections.length + 1 : sections.length;

    return sections.map((section, index) => {
        const currentStep = hasFirstStep ? index + 1 : index;
        const isFirstStep = currentStep === 0;
        const isLastStep = currentStep === totalSteps - 1;
        const fields = getSectionFieldNames(section);
        const title = section?.label;
        const description = section?.description;

        const element = (
            <StepForm key={currentStep} currentStep={currentStep} isFirstStep={isFirstStep} isLastStep={isLastStep}>
                <DonationFormErrorBoundary key={section.name}>
                    <FormSectionTemplate key={section.name} section={section} hideLabel>
                        {section.nodes.map((node) => (
                            <DonationFormErrorBoundary key={node.name}>
                                <SectionNode key={node.name} node={node} />
                            </DonationFormErrorBoundary>
                        ))}
                    </FormSectionTemplate>
                </DonationFormErrorBoundary>
            </StepForm>
        );

        return {
            id: currentStep,
            title,
            description,
            element,
            fields,
            visibilityConditions: section.visibilityConditions,
            isVisible: !section.visibilityConditions.length,
        } as StepObject;
    });
};

/**
 * @since 3.6.0 updated to use includeHeaderInMultiStep
 * @since 3.0.0
 */
export default function MultiStepForm({form}: {form: Form}) {
    const shouldIncludeHeaderInSteps = form.design?.includeHeaderInMultiStep && form.settings?.showHeader;
    const steps = convertSectionsToSteps(form.nodes, shouldIncludeHeaderInSteps);

    if (shouldIncludeHeaderInSteps) {
        steps.unshift({
            id: 0,
            title: null,
            description: null,
            element: <HeaderStep form={form} />,
            fields: [],
            visibilityConditions: [],
            isVisible: true,
        });
    }

    return (
        <DonationFormMultiStepStateProvider initialState={{steps, currentStep: 0}}>
            <DonationSummaryProvider>
                <Steps steps={steps} />
            </DonationSummaryProvider>
        </DonationFormMultiStepStateProvider>
    );
}
