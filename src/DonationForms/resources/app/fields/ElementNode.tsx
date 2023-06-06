import {Element} from '@givewp/forms/types';
import {useTemplateWrapper} from '../templates';
import type {ElementProps} from '@givewp/forms/propTypes';

const formTemplates = window.givewp.form.templates;

export default function ElementNode({node}: {node: Element}) {
    const Element = useTemplateWrapper<ElementProps>(formTemplates.elements[node.type], 'div', node.name);

    return <Element key={node.name} {...node} />;
}
