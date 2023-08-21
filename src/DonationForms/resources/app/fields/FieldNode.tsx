import {Field} from '@givewp/forms/types';
import {useTemplateWrapper} from '../templates';
import registerFieldAndBuildProps from '../utilities/registerFieldAndBuildProps';
import type {FieldProps} from '@givewp/forms/propTypes';
import memoNode from '@givewp/forms/app/utilities/memoNode';
import {useEffect} from 'react';

const formTemplates = window.givewp.form.templates;

function FieldNode({node}: {node: Field}) {
    const {register, getValues, setValue} = window.givewp.form.hooks.useFormContext();
    const {errors} = window.givewp.form.hooks.useFormState();
    const Field =
        node.type !== 'hidden'
            ? useTemplateWrapper<FieldProps>(formTemplates.fields[node.type], 'div', node.name)
            : formTemplates.fields[node.type];
    const fieldProps = registerFieldAndBuildProps(node, register, errors);

    /**
     * Set the default value for the field if it is not already set. This is necessary because the default value is not
     * applied to the initial form render if it requires conditions to be visible.
     */
    useEffect(() => {
        if (node.defaultValue === undefined) {
            return;
        }

        const value = getValues(node.name);

        if (value === undefined || value === null) {
            setValue(node.name, node.defaultValue);
        }
    }, []);

    return <Field key={node.name} {...fieldProps} />;
}

const MemoizedFieldNode = memoNode(FieldNode);

export default MemoizedFieldNode;
