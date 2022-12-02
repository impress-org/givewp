import {isElement, isField, isGroup, Node} from '@givewp/forms/types';
import FieldNode from './FieldNode';
import ElementNode from './ElementNode';
import GroupNode from './GroupNode';
import GatewayFieldNode from '@givewp/forms/app/fields/GatewayFieldNode';
import {elementTemplateExists, fieldTemplateExists, groupTemplateExists} from '@givewp/forms/app/templates';

const formTemplates = window.givewp.form.templates;

/**
 * Determine which node template to render
 *
 * @unreleased
 */
export default function SectionNode({node}: {node: Node}) {
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
