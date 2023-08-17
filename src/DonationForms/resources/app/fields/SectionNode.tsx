import {isElement, isField, isGroup, Node} from '@givewp/forms/types';
import FieldNode from './FieldNode';
import ElementNode from './ElementNode';
import GroupNode from './GroupNode';
import GatewayFieldNode from '@givewp/forms/app/fields/GatewayFieldNode';
import {elementTemplateExists, fieldTemplateExists, groupTemplateExists} from '@givewp/forms/app/templates';
import useVisibilityCondition from '@givewp/forms/app/hooks/useVisibilityCondition';
import {useEffect} from '@wordpress/element';

const formTemplates = window.givewp.form.templates;

/**
 * Determine which node template to render
 *
 * @since 3.0.0
 */
export default function SectionNode({node}: {node: Node}) {
    const showNode = useVisibilityCondition(node.visibilityConditions);
    const {unregister} = window.givewp.form.hooks.useFormContext();

    useEffect(() => {
        if (showNode) {
            return;
        }

        if (isField(node)) {
            unregister(node.name);
        }

        if (isGroup(node)) {
            node.walkNodes((node) => {
                unregister(node.name);
            }, isField);
        }
    }, [showNode, unregister]);

    if (!showNode) {
        return null;
    }

    if (isField(node) && fieldTemplateExists(node)) {
        if (node.type === 'gateways') {
            return <GatewayFieldNode node={node} />;
        }
        return <FieldNode node={node} />;
    } else if (isElement(node) && elementTemplateExists(node)) {
        return <ElementNode node={node} />;
    } else if (isGroup(node) && groupTemplateExists(node)) {
        return <GroupNode node={node} />;
    } else {
        console.error(`Node: ${JSON.stringify(node)} does not exist in Form Design: ${JSON.stringify(formTemplates)}`);

        return null;
    }
}
