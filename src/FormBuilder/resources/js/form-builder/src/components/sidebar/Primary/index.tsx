import {createSlotFill} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {PopoutSlot} from '../popout';

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

function Sidebar() {
    return (
        <div
            id="sidebar-primary"
            className="givewp-next-gen-sidebar givewp-next-gen-sidebar-primary"
            role="region"
            aria-label={__('Standalone Block Editor advanced settings.')}
            tabIndex={-1}
        >
            <PopoutSlot />
            <div className="sidebar-panel">
                <div className="block-editor-block-inspector">
                    <h2>{__('Block', 'give')}</h2>
                    <InspectorSlot bubblesVirtually />
                </div>
            </div>
        </div>
    );
}

Sidebar.InspectorFill = InspectorFill;

export default Sidebar;
