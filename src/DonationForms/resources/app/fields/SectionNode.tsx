import {isElement, isField, isGroup, Node} from '@givewp/forms/types';
import FieldNode from './FieldNode';
import ElementNode from './ElementNode';
import GroupNode from './GroupNode';
import GatewayFieldNode from '@givewp/forms/app/fields/GatewayFieldNode';
import {elementTemplateExists, fieldTemplateExists, groupTemplateExists} from '@givewp/forms/app/templates';
import useVisibilityCondition from '@givewp/forms/app/hooks/useVisibilityCondition';
import {useEffect} from '@wordpress/element';
import memoNode from '@givewp/forms/app/utilities/memoNode';

const formTemplates = window.givewp.form.templates;

/**
 * Determine which node template to render and apply visibility conditions. It is important the visibility conditions
 * occur here, instead of in the more specific components, as it prevents the subsequent hooks from firing, which can
 * cause an infinite re-render loop.
 *
 * @since 3.0.0
 */
function SectionNode({node}: {node: Node}) {
    const showNode = useVisibilityCondition(node.visibilityConditions);
    const {unregister} = window.givewp.form.hooks.useFormContext();

    useEffect(() => {
        if (showNode) {
            return;
        }

        if (isField(node)) {
            unregister(node.name, {
                keepDefaultValue: true,
            });
        }

        if (isGroup(node)) {
            node.walkNodes((node) => {
                unregister(node.name, {
                    keepDefaultValue: true,
                });
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

const MemoizedSectionNode = memoNode(SectionNode);

export default MemoizedSectionNode;
