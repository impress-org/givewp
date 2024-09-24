import {Field} from '@givewp/forms/types';
import {useTemplateWrapper} from '../templates';
import registerFieldAndBuildProps from '../utilities/registerFieldAndBuildProps';
import type {FieldProps} from '@givewp/forms/propTypes';
import memoNode from '@givewp/forms/app/utilities/memoNode';

const formTemplates = window.givewp.form.templates;

const excludeFromTemplateWrapper = ['hidden', 'honeypot'];

/**
 * @since 3.16.2 added excludeFromTemplateWrapper
 * @since 3.0.0
 */
function FieldNode({node}: {node: Field}) {
    const {register} = window.givewp.form.hooks.useFormContext();
    const {errors} = window.givewp.form.hooks.useFormState();
    const Field =
        !excludeFromTemplateWrapper.includes(node.type)
            ? useTemplateWrapper<FieldProps>(formTemplates.fields[node.type], 'div', node.name)
            : formTemplates.fields[node.type];
    const fieldProps = registerFieldAndBuildProps(node, register, errors);

    return <Field key={node.name} {...fieldProps} />;
}

const MemoizedFieldNode = memoNode(FieldNode);

export default MemoizedFieldNode;
