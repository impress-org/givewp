import {PanelHeader, createSlotFill} from '@wordpress/components';
import {__experimentalListView} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';

const {Fill: InspectorFill} = createSlotFill(
    'StandAloneBlockEditorSidebarInspector',
);

function Sidebar() {

    /* eslint-disable react/jsx-pascal-case */
    return (
        <div
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-secondary"
            role="region"
            aria-label={__('Standalone Block Editor advanced settings.')}
            tabIndex="-1"
        >
            <PanelHeader label={__('Fields')} />
            <__experimentalListView showNestedBlocks={true} expandNested={true} />

        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
