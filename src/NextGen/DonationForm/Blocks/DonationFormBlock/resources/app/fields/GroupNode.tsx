import {Field, Group, isField} from '@givewp/forms/types';
import {getGroupTemplate} from '../templates';
import {useFormContext, useFormState} from 'react-hook-form';
import {useMemo} from 'react';
import registerFieldAndBuildProps from '../utilities/registerFieldAndBuildProps';

export default function GroupNode({node}: {node: Group}) {
    const {register} = useFormContext();
    const {errors} = useFormState();
    const Group = useMemo(() => getGroupTemplate(node.type), [node.type]);

    const fieldProps = node.reduceNodes(
        (fieldProps, field: Field) => {
            fieldProps[field.name] = registerFieldAndBuildProps(field, register, errors);

            return fieldProps;
        },
        {},
        isField
    );

    return <Group key={node.name} fieldProps={fieldProps} {...node} />;
}
