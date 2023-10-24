import {Form, Group, isGroup, Node} from '@givewp/forms/types';
import {mapGroup, reduceGroup, walkGroup} from './groups';

/**
 * Receives the form data as provided directly from the server and mutates it to be ready for use by the React application
 *
 * @since 3.0.0
 */
export default function prepareFormData(form: Form) {
    form.walkNodes = walkGroupNodes.bind(form);
    form.mapNodes = mapGroupNodes.bind(form);
    form.reduceNodes = reduceGroupNodes.bind(form);

    form.walkNodes((node: Group) => {
        node.walkNodes = walkGroupNodes.bind(node);
        node.mapNodes = mapGroupNodes.bind(node);
        node.reduceNodes = reduceGroupNodes.bind(node);
    }, isGroup);
}

function walkGroupNodes(this: Group, callback: (node: Node) => void, filter?: (node: Node) => boolean) {
    walkGroup(this, callback, filter);
}

function mapGroupNodes(this: Group, callback: (node: Node) => void, filter?: (node: Node) => boolean) {
    return mapGroup(this, callback, filter);
}

function reduceGroupNodes(
    this: Group,
    callback: (accumulator: any, node: Node) => any,
    initialValue: any,
    filter?: (node: Node) => boolean
) {
    return reduceGroup(this, callback, initialValue, filter);
}
