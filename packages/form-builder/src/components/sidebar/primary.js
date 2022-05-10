import {createSlotFill, PanelHeader} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const { Slot: InspectorSlot, Fill: InspectorFill } = createSlotFill(
    'StandAloneBlockEditorSidebarInspector'
);

function Sidebar() {
    return (
        <div
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={ __( 'Standalone Block Editor advanced settings.' ) }
            tabIndex="-1"
        >
            <PanelHeader label={__('Settings')} />
            <InspectorSlot bubblesVirtually />
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
