import {Element} from '@givewp/forms/types';
import {useTemplateWrapper} from '../templates';
import type {ElementProps} from '@givewp/forms/propTypes';
import memoNode from '@givewp/forms/app/utilities/memoNode';

const formTemplates = window.givewp.form.templates;

function ElementNode({node}: {node: Element}) {
    const Element = useTemplateWrapper<ElementProps>(formTemplates.elements[node.type], 'div', node.name);

    return <Element key={node.name} {...node} />;
}

const MemoizedElementNode = memoNode(ElementNode);

export default MemoizedElementNode;
