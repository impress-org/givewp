import type {Field} from '@givewp/forms/types';
import registerFieldAndBuildProps from '../utilities/registerFieldAndBuildProps';
import {useDonationFormState} from '@givewp/forms/app/store';
import {withTemplateWrapper} from '@givewp/forms/app/templates';
import memoNode from '@givewp/forms/app/utilities/memoNode';

const formTemplates = window.givewp.form.templates;
const GatewayFieldTemplate = withTemplateWrapper(formTemplates.fields.gateways);

function GatewayFieldNode({node}: {node: Field}) {
    const {register} = window.givewp.form.hooks.useFormContext();
    const {errors} = window.givewp.form.hooks.useFormState();
    const fieldProps = registerFieldAndBuildProps(node, register, errors);
    const {gateways} = useDonationFormState();

    // @ts-ignore
    return <GatewayFieldTemplate key={node.name} {...fieldProps} gateways={gateways} />;
}

const MemoizedGatewayFieldNode = memoNode(GatewayFieldNode);

export default MemoizedGatewayFieldNode;
