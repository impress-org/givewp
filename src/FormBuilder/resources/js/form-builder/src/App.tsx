import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import BlockEditorContainer from './containers/BlockEditorContainer';
import {FormStateProvider} from './stores/form-state';
import {Storage} from './common';
import defaultBlocks from './blocks.json';
import {BlockInstance} from '@wordpress/blocks';
import './App.scss';
import FormBuilderErrorBoundary from '@givewp/form-builder/errors/FormBuilderErrorBounday';
import Transfer from '@givewp/form-builder/components/onboarding/transfer';
import {EditorStateProvider} from "@givewp/form-builder/stores/editor-state";

const {blocks: initialBlocks, formSettings: initialFormSettings} = Storage.load();

const initialState = {
    blocks: initialBlocks || (defaultBlocks as BlockInstance[]),
    settings: {
        ...initialFormSettings,
    },
    transfer: {
        showNotice: Boolean(window.migrationOnboardingData.transferShowNotice),
        showUpgradeModal: Boolean(window.migrationOnboardingData.showUpgradeDialog),
        showTransferModal: false,
        showTooltip: false,
    }
};

if (initialBlocks instanceof Error) {
    alert('Unable to load initial blocks.');
    console.error(initialBlocks);
}

if (ShortcutProvider === undefined) {
    console.error('ShortcutProvider is undefined.');
}

/**
 * This is a workaround for a bug where the draggable cursor does not reset.
 *
 * @since 3.0.0
 */
document.addEventListener('dragend', () => {
    // Reset the drag cursor.
    document.body.classList.remove('is-dragging-components-draggable');

    // Scroll the interface down by 1px to force a repaint and reset the popover position.
    document.getElementsByClassName('interface-interface-skeleton__body')[0].scrollBy(0,1);
});

export default function App() {
    return (
        <FormBuilderErrorBoundary>
            <EditorStateProvider initialState={{mode: 'design'}}>
                <FormStateProvider initialState={initialState}>
                    <ShortcutProvider>
                        <BlockEditorContainer />
                        <Transfer />
                    </ShortcutProvider>
                </FormStateProvider>
            </EditorStateProvider>
        </FormBuilderErrorBoundary>
    );
}
