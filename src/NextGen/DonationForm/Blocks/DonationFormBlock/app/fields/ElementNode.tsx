import {Element} from '@givewp/forms/types';
import {getElementTemplate} from '../templates';
import {useMemo} from "react";

export default function ElementNode({node}: { node: Element }) {
    const Element = useMemo(() => getElementTemplate(node.type), [node.type]);

    return <Element key={node.name} {...node} />;
}

