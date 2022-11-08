import {Field} from '@givewp/forms/types';
import {getFieldTemplate} from '../templates';
import {useFormContext, useFormState} from 'react-hook-form';
import {useMemo} from 'react';
import registerFieldAndBuildProps from '../utilities/registerFieldAndBuildProps';

export default function FieldNode({node}: {node: Field}) {
    const {register} = useFormContext();
    const {errors} = useFormState();
    const Field = useMemo(() => getFieldTemplate(node.type), [node.type]);
    const fieldProps = registerFieldAndBuildProps(node, register, errors);

    return <Field key={node.name} {...fieldProps} />;
}
