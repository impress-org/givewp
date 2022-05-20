import { useContext } from 'react'

import {createSlotFill, TabPanel, PanelBody, PanelRow, TextControl} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {FormTitleContext} from "../../context/formTitle";

const { Slot: InspectorSlot, Fill: InspectorFill } = createSlotFill(
    'StandAloneBlockEditorSidebarInspector'
);

const tabs = [
    {
        name: 'form',
        title: __('Form'),
        className: 'tab-form',
        content: ({ formTitle, setFormTitle }) => (
            <PanelBody title={ __( 'Form Settings', 'give' ) } initialOpen={true}>
                <PanelRow>
                    <TextControl
                        label={__('Form Title')}
                        value={ formTitle }
                        onChange={ setFormTitle }
                    />
                </PanelRow>
            </PanelBody>
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
    const [formTitle, setFormTitle] = useContext(FormTitleContext)
    return (
        <div
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={ __( 'Standalone Block Editor advanced settings.' ) }
            tabIndex="-1"
        >
            <TabPanel
                className="sidebar-panel"
                activeClass="active-tab"
                tabs={ tabs }
            >
                { ( tab ) => <tab.content formTitle={formTitle} setFormTitle={setFormTitle} /> }
            </TabPanel>
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
