import {withTemplateWrapper} from '@givewp/forms/app/templates';
import SectionNode from '@givewp/forms/app/fields/SectionNode';
import useVisibilityCondition from '@givewp/forms/app/hooks/useVisibilityCondition';
import {Field, isField, Section as SectionType} from '@givewp/forms/types';
import DonationFormErrorBoundary from '@givewp/forms/app/errors/boundaries/DonationFormErrorBoundary';
import {useEffect} from '@wordpress/element';

const formTemplates = window.givewp.form.templates;
const FormSectionTemplate = withTemplateWrapper(formTemplates.layouts.section, 'section');

export default function Section({section}: {section: SectionType}) {
    const showNode = useVisibilityCondition(section.visibilityConditions);
    const {unregister} = window.givewp.form.hooks.useFormContext();

    useEffect(() => {
        if (showNode) {
            return;
        }

        section.walkNodes((field: Field) => {
            unregister(field.name);
        }, isField);
    }, [showNode]);

    if (!showNode) {
        return null;
    }

    return (
        <FormSectionTemplate section={section}>
            {section.nodes.map((node) => (
                <DonationFormErrorBoundary key={node.name}>
                    <SectionNode node={node} />
                </DonationFormErrorBoundary>
            ))}
        </FormSectionTemplate>
    );
}
