import {memo} from '@wordpress/element';
import {Node} from '@givewp/forms/types';

/**
 * This is used for memoizing Node components. Node props come from the server and are never intended to change. The
 * state of a Node may change, triggering a re-render, but the props should never change.
 *
 * @since 3.0.0
 */
export default function memoNode(NodeComponent) {
    return memo(NodeComponent, compareNodeProps);
}

type NodeProp = { node: Node };

function compareNodeProps(oldNode: NodeProp, newNode: NodeProp) {
    return oldNode.node.name === newNode.node.name;
}
