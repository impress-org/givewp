import {RegisterOptions, useFormContext} from 'react-hook-form';

import {Field, isElement, isField, isGroup, Node, Section} from '@givewp/forms/types';
import {getElementTemplate, getFieldTemplate, getGroupTemplate} from '../templates';
import getErrorByFieldName from "../utilities/getErrorByFieldName";

export default function SectionNodes({nodes}: Section) {
    const {register, formState: {errors}} = useFormContext();

    return (
        <>
            {nodes.map((node) => {
                if (isField(node)) {
                    const Field = getFieldTemplate(node.type);
                    const inputProps = register(node.name, buildRegisterValidationOptions(node.validationRules));

                    return <Field key={node.name} inputProps={inputProps}
                                  fieldError={getErrorByFieldName(errors, node.name)}
                                  {...node} />;
                } else if (isElement(node)) {
                    const Element = getElementTemplate(node.type);
                    return <Element key={node.name} {...node} />;
                } else if (isGroup(node)) {
                    const Group = getGroupTemplate(node.type);
                    const fields = node.nodes.reduce(getGroupFields, []);

                    const inputProps = fields.reduce((inputProps, field) => {
                        inputProps[field.name] = register(
                            field.name,
                            buildRegisterValidationOptions(field.validationRules)
                        );

                        return inputProps;
                    }, {});

                    return <Group key={node.name} inputProps={inputProps} {...node} />;
                } else {
                    return null;
                }
            })}
        </>
    );
}

/**
 * Recursively finds all the fields within a group
 *
 * @unreleased
 */
function getGroupFields(fields: Field[], node: Node): Field[] {
    if (isField(node)) {
        fields.push(node);
    } else if (isGroup(node)) {
        node.nodes.reduce(getGroupFields, fields);
    }

    return fields;
}

/**
 * Builds the expected registration options from the Field API validation rules
 *
 * @unreleased
 */
function buildRegisterValidationOptions(validationRules: {[key: string]: any}): RegisterOptions {
    return ['required', 'maxLength', 'minLength'].reduce((rules, rule) => {
        if (validationRules.hasOwnProperty(rule)) {
            rules[rule] = validationRules[rule];
        }

        return rules;
    }, {});
}
