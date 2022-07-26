/**
 * Recursively finds all the fields within a group
 *
 * @unreleased
 */
import {Field, isField, isGroup, Node} from "@givewp/forms/types";

export default function getGroupFields(fields: Field[], node: Node): Field[] {
    if (isField(node)) {
        fields.push(node);
    } else if (isGroup(node)) {
        node.nodes.reduce(getGroupFields, fields);
    }

    return fields;
}
