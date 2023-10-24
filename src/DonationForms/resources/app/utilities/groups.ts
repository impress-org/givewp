import {Field, Group, isField, isGroup, Node} from '@givewp/forms/types';

/**
 * Finds the first node with a given name within a collection of nodes
 *
 * @since 3.0.0
 */
export function findNode(name: string, nodes: Node[]): Node {
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
 * Walks through a group's nodes and calls a callback for each node. If a filter is provided the callback only fires for
 * nodes which pass the filter.
 *
 * @since 3.0.0
 */
export function walkGroup(group: Group, callback: (node: Node) => void, filter?: (node: Node) => boolean) {
    walkNodes(group.nodes, callback, filter);
}

/**
 * Maps through a Group's nodes and calls a callback for each node. If a filter is provided the callback only fires for
 * passing nodes.
 *
 * @since 3.0.0
 */
export function mapGroup(group: Group, callback: (node: Node) => unknown, filter?: (node: Node) => boolean) {
    return mapNodes(group.nodes, callback, filter);
}

/**
 * Reduces a Group's nodes into a single value. If a filter is provided the callback only fires for passing nodes.
 * @since 3.0.0
 */
export function reduceGroup(
    group: Group,
    callback: (accumulator: unknown, node: Node) => unknown,
    initialValue: unknown,
    filter?: (node: Node) => boolean
) {
    return reduceNodes(group.nodes, callback, initialValue, filter);
}

/**
 * Walks through a collection of nodes and calls a callback for each node. If a filter is provided the callback only fires for passing nodes.
 *
 * @since 3.0.0
 */
export function walkNodes(nodes: Node[], callback: (node: Node) => void, filter?: (node: Node) => boolean) {
    nodes.forEach((node) => {
        if (!filter || filter(node)) {
            callback(node);
        }

        if (isGroup(node)) {
            walkNodes(node.nodes, callback, filter);
        }
    });
}

/**
 * Maps a collection of nodes to a new array of values. If a filter is provided the callback only fires for nodes which
 * pass.
 *
 * @since 3.0.0
 */
export function mapNodes<Type>(
    nodes: Node[],
    callback: (node: Node) => Type,
    filter?: (node: Node) => boolean
): Type[] {
    let mappedValues: Array<Type> = [];

    walkNodes(
        nodes,
        (node) => {
            mappedValues.push(callback(node));
        },
        filter
    );

    return mappedValues;
}

/**
 * Reduces an array of nodes to a single value. If the filter is provided, only nodes which pass the filter will be used.
 *
 * @since 3.0.0
 */
export function reduceNodes<Type>(
    nodes: Node[],
    callback: (accumulator: Type, node: Node) => Type,
    initialValue: Type,
    filter?: (node: Node) => boolean
): Type {
    let accumulator = initialValue;

    walkNodes(
        nodes,
        (node) => {
            accumulator = callback(accumulator, node);
        },
        filter
    );

    return accumulator;
}

/**
 * Walks through an array of nodes, limited by fields, and calls a callback for each field.
 *
 * @since 3.0.0
 */
export function walkFields(nodes: Node[], callback: (field: Field) => void) {
    walkNodes(nodes, callback, isField);
}

/**
 * Reduces an array of nodes, limited by its fields, into a single value.
 *
 * @since 3.0.0
 */
export function reduceFields<Type>(
    nodes: Parameters<typeof reduceNodes>[0],
    callback: (accumulator: Type, field: Field) => Type,
    initialValue: Type
) {
    return reduceNodes(nodes, callback, initialValue, isField);
}
