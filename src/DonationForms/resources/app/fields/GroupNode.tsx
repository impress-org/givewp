import {Group, Node} from '@givewp/forms/types';
import {useTemplateWrapper} from '../templates';
import type {GroupProps} from '@givewp/forms/propTypes';
import SectionNode from './SectionNode';
import memoNode from '@givewp/forms/app/utilities/memoNode';

const formTemplates = window.givewp.form.templates;

/**
 * Renders a group node and its children. At this point, group nodes are not generic, and are only used for specific
 * subtypes of group fields, such as the Name field. The nodes are grouped by component and props, and then passed to
 * the group template component. This way the group template controls how the nodes are rendered, and can choose to
 * manipulate or override props.
 *
 * @since 3.0.0
 */
function GroupNode({node}: {node: Group}) {
    const Group = useTemplateWrapper<GroupProps>(formTemplates.groups[node.type], 'div', node.name);

    const nodeComponents = node.reduceNodes((nodes, node: Node) => {
        nodes[node.name] = (props: Node) => <SectionNode node={{...node, ...props}} />;

        return nodes;
    }, {});

    const nodeProps = node.reduceNodes((nodes, node: Node) => {
        nodes[node.name] = node;

        return nodes;
    }, {});

    return <Group key={node.name} nodeComponents={nodeComponents} nodeProps={nodeProps} {...node} />;
}

const MemoizedGroupNode = memoNode(GroupNode);

export default MemoizedGroupNode;
