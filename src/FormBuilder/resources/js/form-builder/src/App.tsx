import {ShortcutProvider} from '@wordpress/keyboard-shortcuts';
import BlockEditorContainer from './containers/BlockEditorContainer';
import {FormStateProvider} from './stores/form-state';
import {Storage} from './common';
import defaultBlocks from './blocks.json';
import Feedback from '@givewp/form-builder/feedback';
import {BlockInstance} from '@wordpress/blocks';
import './App.scss';
import FormBuilderErrorBoundary from '@givewp/form-builder/errors/FormBuilderErrorBounday';
import {TransferNotice, TransferDialog, UpgradeDialog} from '@givewp/form-builder/components/onboarding/transfer';

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
    }
};

if (initialBlocks instanceof Error) {
    alert('Unable to load initial blocks.');
    console.error(initialBlocks);
}

if (ShortcutProvider === undefined) {
    console.error('ShortcutProvider is undefined.');
}

export default function App() {
    return (
        <FormBuilderErrorBoundary>
            <FormStateProvider initialState={initialState}>
                <ShortcutProvider>
                    <BlockEditorContainer />
                    <Feedback />
                    <TransferNotice />
                    <TransferDialog />
                    <UpgradeDialog />
                </ShortcutProvider>
            </FormStateProvider>
        </FormBuilderErrorBoundary>
    );
}
