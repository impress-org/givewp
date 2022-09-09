import {ElementType, FC, ReactNode} from 'react';
import {applyFilters} from '@wordpress/hooks';
import type {Element} from '@givewp/forms/types';
import type {ElementProps, FieldProps, GroupProps} from '@givewp/forms/propTypes';
import TextField from './fields/Text';
import TextAreaField from './fields/TextArea';
import EmailField from './fields/Email';
import HiddenField from './fields/Hidden';
import HtmlElement from './elements/Html';
import DonationSummaryElement from './elements/DonationSummary';
import NameGroup from './groups/Name';
import SectionLayout, {SectionProps} from './layouts/Section';
import Form, {FormProps} from './layouts/Form';
import AmountField from './fields/Amount';
import classNames from 'classnames';
import Gateways from './fields/Gateways';
import Paragraph from './elements/Paragraph';

export function NodeWrapper({
    type,
    nodeType,
    htmlTag: Element = 'div',
    name,
    children,
}: {
    type: string;
    nodeType: string;
    htmlTag?: ElementType;
    name?: string;
    children: ReactNode;
}) {
    return (
        <Element
            className={classNames(`givewp-${nodeType}`, `givewp-${nodeType}-${type}`, {
                [`givewp-${nodeType}-${type}-${name}`]: name,
            })}
        >
            {children}
        </Element>
    );
}

export function withWrapper(NodeComponent, section, type, htmlTag) {
    return (props) => {
        return (
            <NodeWrapper type={type} nodeType={section} htmlTag={htmlTag}>
                <NodeComponent {...props} />
            </NodeWrapper>
        );
    };
}

const defaultTemplate = {
    fields: {
        amount: AmountField,
        text: TextField,
        textarea: TextAreaField,
        email: EmailField,
        hidden: HiddenField,
        gateways: Gateways,
    },
    elements: {
        paragraph: Paragraph,
        html: HtmlElement,
        donationSummary: DonationSummaryElement,
    },
    groups: {
        name: NameGroup,
    },
    layouts: {
        section: SectionLayout,
        form: Form,
    },
};

const activeTemplate = window.givewp.template.get();

const template = {
    fields: {
        ...defaultTemplate.fields,
        ...activeTemplate?.fields,
    },
    elements: {
        ...defaultTemplate.elements,
        ...activeTemplate?.elements,
    },
    groups: {
        ...defaultTemplate.groups,
        ...activeTemplate?.groups,
    },
    layouts: {
        ...defaultTemplate.layouts,
        ...activeTemplate?.layouts,
    },
};

function getTemplate<NodeProps>(type: string, section: string, htmlTag?: string): FC<NodeProps> {
    const Node = template[section].hasOwnProperty(type)
        ? withWrapper(template[section][type], section, type, htmlTag)
        : null;

    let FilteredNode = applyFilters(`givewp/form/${section}/${type}`, Node);
    FilteredNode = applyFilters(`givewp/form/${section}`, Node, type);

    if (nodeIsFunctionalComponent(FilteredNode)) {
        return FilteredNode as FC<NodeProps>;
    } else {
        throw new Error(`Invalid field type: ${type}`);
    }
}

export function getFieldTemplate(type: string): FC<FieldProps> {
    return getTemplate<FieldProps>(type, 'fields');
}

export function getElementTemplate(type: string): FC<ElementProps> {
    return getTemplate<ElementProps>(type, 'elements');
}

export function getGroupTemplate(type: string): FC<GroupProps> {
    return getTemplate<GroupProps>(type, 'groups');
}

export function getSectionTemplate(): FC<SectionProps> {
    return getTemplate<SectionProps>('section', 'layouts', 'section');
}

export function getFormTemplate(): FC<FormProps> {
    return getTemplate<FormProps>('form', 'layouts');
}

function nodeIsFunctionalComponent(Node: unknown): Node is FC {
    return typeof Node === 'function';
}
