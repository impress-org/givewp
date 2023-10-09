import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import BlockEditorContainer from './containers/BlockEditorContainer';
import {FormStateProvider} from './stores/form-state';
import {Storage} from './common';
import defaultBlocks from './blocks.json';
import Feedback from '@givewp/form-builder/feedback';
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
 * @unreleased
 */
document.addEventListener('dragend', () => {
    document.body.classList.remove('is-dragging-components-draggable')
});

export default function App() {
    return (
        <FormBuilderErrorBoundary>
            <EditorStateProvider initialState={{mode: 'design'}}>
                <FormStateProvider initialState={initialState}>
                    <ShortcutProvider>
                        <BlockEditorContainer />
                        <Feedback />
                        <Transfer />
                    </ShortcutProvider>
                </FormStateProvider>
            </EditorStateProvider>
        </FormBuilderErrorBoundary>
    );
}
