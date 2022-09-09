import {Field, Group, isField, isGroup, Node} from '@givewp/forms/types';

export function findNode(name, nodes: Node[]): Node {
    let node;
    for (let index = 0; index < nodes.length; index++) {
        node = nodes[index];

        if (node.name === name) {
            return node;
        } else if (isGroup(node)) {
            const nestedNode = findNode(name, node.nodes);
            if (nestedNode !== null) {
                return nestedNode;
            }
        }
    }

    return null;
}

/**
 * Recursively finds all the fields within a group
 *
 * @unreleased
 */
export function getGroupFields(group: Group): Field[] {
    return group.nodes.reduce(groupFieldsReducer, []);
}

function groupFieldsReducer(fields: Field[], node: Node): Field[] {
    if (isField(node)) {
        fields.push(node);
    } else if (isGroup(node)) {
        node.nodes.reduce(groupFieldsReducer, fields);
    }

    return fields;
}
