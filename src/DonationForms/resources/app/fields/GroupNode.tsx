import {Field, Group, isField} from '@givewp/forms/types';
import {useTemplateWrapper} from '../templates';
import type {GroupProps} from '@givewp/forms/propTypes';
import FieldNode from '@givewp/forms/app/fields/FieldNode';

const formTemplates = window.givewp.form.templates;

export default function GroupNode({node}: {node: Group}) {
    const Group = useTemplateWrapper<GroupProps>(formTemplates.groups[node.type], 'div', node.name);

    const fields = node.reduceNodes(
        (fields, field: Field) => {
            fields[field.name] = (props: Field) => <FieldNode node={{...field, ...props}} />;

            return fields;
        },
        {},
        isField
    );

    const fieldProps = node.reduceNodes(
        (fields, field: Field) => {
            fields[field.name] = field;

            return fields;
        },
        {},
        isField
    );

    return <Group key={node.name} fields={fields} fieldProps={fieldProps} {...node} />;
}
