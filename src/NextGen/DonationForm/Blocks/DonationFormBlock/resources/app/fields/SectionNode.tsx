import {isElement, isField, isGroup, Node} from '@givewp/forms/types';
import FieldNode from './FieldNode';
import ElementNode from './ElementNode';
import GroupNode from './GroupNode';

/**
 * Determine which node template to render
 *
 * @unreleased
 */
export default function SectionNode({node}: {node: Node}) {
    if (isField(node)) {
        return <FieldNode node={node} />;
    } else if (isElement(node)) {
        return <ElementNode node={node} />;
    } else if (isGroup(node)) {
        return <GroupNode node={node} />;
    } else {
        return null;
    }
}
