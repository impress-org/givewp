import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import ServerSideRender from '@wordpress/server-side-render';
import ModalForm from '../../../shared/components/ModalForm';
import '../../../shared/components/EntitySelector/styles/index.scss';

/**
 * @unreleasaed
 */
export interface BlockPreviewProps {
    formId: number;
    clientId: string;
    displayStyle: 'onpage' | 'modal' | 'newTab' | 'reveal';
    continueButtonTitle: string;
    link: string;
    isLegacyForm: boolean;
    attributes: any;
    isSelected: boolean;
    className: string;
}

/**
 * @unreleasaed
 */
export default function BlockPreview({
    clientId,
    formId,
    displayStyle,
    continueButtonTitle,
    link,
    isLegacyForm,
    attributes,
    isSelected,
    className,
}: BlockPreviewProps) {
    // @ts-ignore
    const selectedBlock = useSelect((select) => select('core/block-editor').getSelectedBlock(), []);
    const isBlockSelected = selectedBlock?.clientId === clientId;

    const iframePointerEvents = isBlockSelected ? 'auto' : 'none';
    const isModalDisplay = displayStyle === 'modal' || displayStyle === 'reveal';
    const isNewTabDisplay = displayStyle === 'newTab';

    if (isLegacyForm) {
        return (
            <div className={`${className}${isSelected ? ' isSelected' : ''}`}>
                <ServerSideRender block="givewp/campaign-form" attributes={attributes} />
            </div>
        );
    }

    if (isNewTabDisplay) {
        return (
            <a className="givewp-donation-form-link" href={link} target="_blank" rel="noopener noreferrer">
                {continueButtonTitle}
            </a>
        );
    }

    if (isModalDisplay) {
        return (
            <ModalForm
                dataSrc={`/?givewp-route=donation-form-view&form-id=${formId}`}
                embedId=""
                buttonText={continueButtonTitle}
                isFormRedirect={false}
                formViewUrl=""
            />
        );
    }

    return (
        <IframeResizer
            title={__('Donation Form', 'give')}
            src={`/?givewp-route=donation-form-view&form-id=${formId}`}
            checkOrigin={false}
            style={{
                width: '1px',
                minWidth: '100%',
                border: '0',
                pointerEvents: iframePointerEvents,
            }}
        />
    );
}
