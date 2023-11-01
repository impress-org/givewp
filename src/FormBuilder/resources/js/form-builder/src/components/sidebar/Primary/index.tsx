import {createSlotFill} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import TabPanel from '../TabPanel';

import {
    DonationConfirmation,
    EmailSettings,
    FormGridSettings,
    FormSummarySettings,
    RegistrationSettings,
} from '../../../settings';
import {PopoutSlot} from '../popout';
import {useEffect} from 'react';
import useSelectedBlocks from '../../../hooks/useSelectedBlocks';
import BlockCard from '../../forks/BlockCard';
import {settings} from '@wordpress/icons';

const {Slot: InspectorSlot, Fill: InspectorFill} = createSlotFill('StandAloneBlockEditorSidebarInspector');

declare const window: {
    givewp: {
        form: {
            settings: {
                setFormSettings: typeof setFormSettings;
                useFormState: typeof useFormState;
                useFormStateDispatch: typeof useFormStateDispatch;
            };
        };
    };
} & Window;
window.givewp.form.settings = {setFormSettings, useFormState, useFormStateDispatch};

const tabs = [
    {
        name: 'form',
        title: __('Form'),
        className: 'tab-form',
        content: () => (
            <>
                <BlockCard
                    icon={settings}
                    title="Form Settings"
                    description={__(
                        'These settings affect how your form functions and is presented, as well as the form page.',
                        'give'
                    )}
                />
                <FormSummarySettings />
                <RegistrationSettings />
                <DonationConfirmation />
                <FormGridSettings />
                <EmailSettings />
                {wp.hooks.applyFilters('givewp_form_builder_pdf_settings', '')}
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
];

function Sidebar({selectedTab, setSelectedTab}) {
    const selectedBlocks = useSelectedBlocks();

    useEffect(
        () => {
            if (selectedBlocks.length) setSelectedTab('block');
        },
        [selectedBlocks, setSelectedTab] // only run effect when selectedBlocks changes
    );

    return (
        <div
            id="sidebar-primary"
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={__('Standalone Block Editor advanced settings.')}
            tabIndex={-1}
        >
            <PopoutSlot />
            <TabPanel
                className="sidebar-panel"
                tabs={tabs}
                state={[selectedTab, setSelectedTab]}
                initialTabName="Sidebar Panel"
            >
                {(tab) => (
                    <div>
                        <div className="block-editor-block-inspector">
                            <tab.content />
                        </div>
                    </div>
                )}
            </TabPanel>
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
