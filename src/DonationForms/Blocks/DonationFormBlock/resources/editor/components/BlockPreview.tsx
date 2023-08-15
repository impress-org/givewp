import ModalPreview from './ModalPreview';
import RevealPreview from './RevealPreview';
import IframeResizer from 'iframe-resizer-react';
import {useSelect} from '@wordpress/data';

interface BlockPreviewProps {
    formId: number;
    clientId: string;
    formFormat: string;
    openFormButton: string;
}

/**
 * @since 3.0.0
 */
export default function BlockPreview({clientId, formId, formFormat, openFormButton}: BlockPreviewProps) {
    // @ts-ignore
    const selectedBlock = useSelect((select) => select('core/block-editor').getSelectedBlock(), []);
    const isBlockSelected = selectedBlock?.clientId === clientId;

    const enableIframe = isBlockSelected ? 'auto' : 'none';

    const isModalDisplay = formFormat === 'modal';
    const isRevealDisplay = formFormat === 'reveal';

    return isModalDisplay ? (
        <ModalPreview enableIframe={enableIframe} formId={formId} openFormButton={openFormButton} />
    ) : isRevealDisplay ? (
        <RevealPreview enableIframe={enableIframe} formId={formId} openFormButton={openFormButton} />
    ) : (
        <IframeResizer
            src={`/?givewp-route=donation-form-view&form-id=${formId}`}
            checkOrigin={false}
            style={{
                width: '1px',
                minWidth: '100%',
                border: '0',
                pointerEvents: enableIframe,
            }}
        />
    );
}
