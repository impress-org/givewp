import {createSlotFill, PanelBody} from '@wordpress/components';
import {__} from '@wordpress/i18n';

import './styles.scss';

import TabPanel from './tab-panel';

import {
    DonationGoalSettings,
    FormDesignSettings,
    FormSummarySettings,
    OfflineDonationsSettings,
    CustomStyleSettings,
    ReceiptSettings,
} from '../../settings/index.ts';
import FormFields from '../../settings/form-fields';
import {PopoutSlot} from './popout';
import {useEffect} from 'react';
import useSelectedBlocks from '../../hooks/useSelectedBlocks';
import BlockCard from "@wordpress/block-editor/build/components/block-card";
import { settings } from '@wordpress/icons';

const {Slot: InspectorSlot, Fill: InspectorFill} = createSlotFill('StandAloneBlockEditorSidebarInspector');

const tabs = [
    {
        name: 'form',
        title: __('Form'),
        className: 'tab-form',
        content: () => (
            <>
                <BlockCard
                    icon={ settings }
                    title="Form Settings"
                    description="These settings affect how your form functions and is presented, as well as the form page."
                />
                <FormSummarySettings />
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
                <FormDesignSettings />
                <DonationGoalSettings />
                <ReceiptSettings />
                <CustomStyleSettings />
            </>
        )
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
                {(tab) => <div><div className="block-editor-block-inspector"><tab.content /></div></div>}
            </TabPanel>
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
