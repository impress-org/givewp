import type {FC, ReactNode} from 'react';
import {useMemo} from 'react';
import {Element, Field, Group} from '@givewp/forms/types';

/**
 * Get the active template from the window
 *
 * @since 3.0.0
 */
const template = window.givewp.form.templates

/**
 * Get the NodeWrapper from active template
 *
 * @since 3.0.0
 */
const NodeWrapper = template.layouts.wrapper;

/**
 * Find the names of nodeType and type based on the value of the template.
 *
 * @since 3.0.0
 */
export function findTemplateKeys<S extends keyof typeof template, T extends keyof typeof template[S]>(
    templateValue: ReactNode
): {nodeType: S | null; type: T | null} {
    let nodeType = null;
    let type = null;

    Object.entries(template).forEach((firstLevel) => {
        if (typeof firstLevel[1] === 'object') {
            const firstLevelKey = firstLevel[0];
            const secondLevelKeys = Object.keys(firstLevel[1]);
            const templateType = secondLevelKeys.find((key) => firstLevel[1][key] === templateValue);

            if (templateType) {
                type = templateType;
                nodeType = firstLevelKey;
            }
        }
    });

    return {nodeType, type};
}

/**
 * This HOC will wrap a template component in our NodeWrapper and automatically figure out what nodeType and type to use as props.
 *
 * @since 3.0.0
 */
export function withTemplateWrapper<TemplateProps>(
    Template: FC<TemplateProps>,
    htmlTag: keyof JSX.IntrinsicElements = 'div',
    name?: string
): FC<TemplateProps> {
    // @ts-ignore
    const {nodeType, type} = findTemplateKeys(Template);

    return (props: TemplateProps) => (
        <NodeWrapper nodeType={nodeType} type={type} htmlTag={htmlTag} name={name}>
            <Template {...(props as TemplateProps)} />
        </NodeWrapper>
    );
}

/**
 * A component version of withTemplateWrapper that uses the child component to determine the NodeWrapper props.
 *
 * @since 3.0.0
 */
export function TemplateWrapper({children, htmlTag}: {children: JSX.Element; htmlTag?: keyof JSX.IntrinsicElements}) {
    const {nodeType, type} = useMemo(() => findTemplateKeys(children.type), []);

    return (
        <NodeWrapper nodeType={nodeType} type={type} htmlTag={htmlTag}>
            {children}
        </NodeWrapper>
    );
}

/**
 * A hook version of withTemplateWrapper
 *
 * @since 3.0.0
 */
export function useTemplateWrapper<TemplateProps>(
    Template: FC<TemplateProps>,
    htmlTag: keyof JSX.IntrinsicElements = 'div',
    name?: string
): FC<TemplateProps> {
    return useMemo(() => withTemplateWrapper(Template, htmlTag, name), [Template]);
}

/**
 * @since 3.0.0
 */
export function fieldTemplateExists(field: Field): boolean {
    return template.fields.hasOwnProperty(field.type);
}

/**
 * @since 3.0.0
 */
export function elementTemplateExists(element: Element): boolean {
    return template.elements.hasOwnProperty(element.type);
}

/**
 * @since 3.0.0
 */
export function groupTemplateExists(group: Group): boolean {
    return template.groups.hasOwnProperty(group.type);
}
