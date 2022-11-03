import {createSlotFill} from '@wordpress/components';
import {__} from '@wordpress/i18n';

import TabPanel from './tab-panel';

import {DonationGoalSettings, FormTitleSettings, OfflineDonationsSettings} from '../../settings';
import FormFields from "../../settings/form-fields";
import {PopoutSlot} from "./popout";
import {useEffect} from "react";
import useSelectedBlocks from "../../hooks/useSelectedBlocks";

const {Slot: InspectorSlot, Fill: InspectorFill} = createSlotFill(
    'StandAloneBlockEditorSidebarInspector',
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
        ),
    },
    {
        name: 'block',
        title: __('Block'),
        className: 'tab-block',
        content: () => (
            <>
                <InspectorSlot bubblesVirtually />
            </>
        ),
    },
    {
        name: 'design',
        title: __('Design'),
        className: 'tab-block',
        content: () => (
            <>
                <span className={'block-editor-block-inspector__no-blocks'}>No template selected.</span>
            </>
        ),
    },
];

function Sidebar({selectedTab, setSelectedTab}) {

    const selectedBlocks = useSelectedBlocks();

    useEffect(
        () => {
            if (selectedBlocks.length) setSelectedTab('block');
        }
        , [selectedBlocks, setSelectedTab], // only run effect when selectedBlocks changes
    );

    return (
        <div
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={__('Standalone Block Editor advanced settings.')}
            tabIndex="-1"
        >
            <PopoutSlot />
            <TabPanel
                className="sidebar-panel"
                activeClass="active-tab"
                tabs={tabs}
                state={[selectedTab, setSelectedTab]}
            >
                {(tab) => <tab.content />}
            </TabPanel>
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
