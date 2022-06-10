import {
    createSlotFill,
    TabPanel,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import {DonationGoalSettings, FormTitleSettings, OfflineDonationsSettings} from '../../settings'
import FormFields from "../../settings/form-fields";
import {PopoutSlot} from "./popout";

const { Slot: InspectorSlot, Fill: InspectorFill } = createSlotFill(
    'StandAloneBlockEditorSidebarInspector'
);

const tabs = [
    {
        name: 'form',
        title: __('Form'),
        className: 'tab-form',
        content: () => (
            <>
                <FormTitleSettings />
                <DonationGoalSettings />
                <OfflineDonationsSettings />
                <FormFields />
            </>
        )
    },
    {
        name: 'block',
        title: __('Block'),
        className: 'tab-block',
        content: () => (
            <>
                <InspectorSlot bubblesVirtually />
            </>
        )
    },
]

function Sidebar() {

    return (
        <div
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={ __( 'Standalone Block Editor advanced settings.' ) }
            tabIndex="-1"
        >
            <PopoutSlot />
            <TabPanel
                className="sidebar-panel"
                activeClass="active-tab"
                tabs={ tabs }
            >
                { ( tab ) => <tab.content /> }
            </TabPanel>
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
