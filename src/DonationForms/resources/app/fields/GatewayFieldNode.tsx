import {Field} from '@givewp/forms/types';
import registerFieldAndBuildProps from '../utilities/registerFieldAndBuildProps';
import {useDonationFormState} from '@givewp/forms/app/store';
import {withTemplateWrapper} from '@givewp/forms/app/templates';

const formTemplates = window.givewp.form.templates;
const GatewayFieldTemplate = withTemplateWrapper(formTemplates.fields.gateways);

export default function GatewayFieldNode({node}: {node: Field}) {
    const {register} = window.givewp.form.hooks.useFormContext();
    const {errors} = window.givewp.form.hooks.useFormState();
    const fieldProps = registerFieldAndBuildProps(node, register, errors);
    const {gateways} = useDonationFormState();

    // @ts-ignore
    return <GatewayFieldTemplate key={node.name} {...fieldProps} gateways={gateways} />;
}
