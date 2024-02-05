import {createRef, MouseEventHandler, RefObject, useState} from 'react';
import {createPortal} from 'react-dom';
import cx from 'classnames';
import {__, sprintf} from '@wordpress/i18n';
import {Button, PanelBody, PanelRow, Spinner, TextControl} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {CopyIcon} from '@givewp/form-builder/components/icons';
import FormSummarySettings from '@givewp/form-builder/settings/group-general/form-summary';
import {getWindowData} from '@givewp/form-builder/common';
import {Interweave} from 'interweave';

interface FormPrepublishPanelProps {
    isSaving: boolean;
    isPublished: boolean;
    handleSave: MouseEventHandler<HTMLButtonElement>;
    handleClose: MouseEventHandler<HTMLButtonElement>;
}

export default function FormPrepublishPanel({
    isSaving,
    isPublished,
    handleSave,
    handleClose,
}: FormPrepublishPanelProps) {
    const permalinkField: RefObject<HTMLInputElement> = createRef();

    const {settings} = useFormState();
    const {formTitle, formStatus, newFormStatus} = settings;
    const dispatch = useFormStateDispatch();
    const {
        formPage: {permalink},
    } = getWindowData();

    const [isCopied, setIsCopied] = useState(false);

    const handleCopy = () => {
        setIsCopied(true);

        permalinkField.current.select();
        document.execCommand('copy');

        setTimeout(() => {
            setIsCopied(false);
        }, 3000);
    };

    const isPrivate = () => {
        if (newFormStatus) {
            return 'private' === newFormStatus;
        }

        return 'private' === formStatus;
    };

    return createPortal(
        <div
            className={cx('givewp-next-gen-prepublish-panel', {
                'givewp-next-gen-prepublish-panel__animate': !isPublished,
            })}
        >
            {isSaving ? (
                <>
                    <div className="givewp-next-gen-prepublish-panel__header">
                        <div className="givewp-next-gen-prepublish-panel__header-actions">
                            <Button variant="primary" isBusy={true} isPressed={true}>
                                {__('Publishing', 'give')}
                            </Button>
                        </div>
                    </div>
                    <div className="givewp-next-gen-prepublish-panel__spinner">
                        <Spinner />
                    </div>
                </>
            ) : (
                <>
                    {isPublished ? (
                        <>
                            <div className="givewp-next-gen-prepublish-panel__header">
                                <div className="givewp-next-gen-prepublish-panel__header-actions">
                                    <Button variant="secondary" onClick={handleClose}>
                                        {__('Close panel', 'give')}
                                    </Button>
                                </div>
                            </div>

                            <div className="givewp-next-gen-prepublish-panel__content_publish">
                                <strong>
                                    <Interweave
                                        content={sprintf(
                                            __('%s is now live.', 'give'),
                                            `<a href="${permalink}" target="_blank">${formTitle}</a>`
                                        )}
                                    />
                                </strong>
                            </div>

                            <PanelBody title={__("What's next?", 'give')} initialOpen={true}>
                                <PanelRow className="givewp-next-gen-prepublish-panel_link">
                                    <span>{__('PAGE ADDRESS', 'give')}</span>
                                    <span>
                                        <Button
                                            href="#"
                                            icon={CopyIcon}
                                            variant="tertiary"
                                            onClick={handleCopy}
                                            className="givewp-next-gen-prepublish-panel_copy_link"
                                        >
                                            {isCopied ? __('Copied', 'give') : __('Copy', 'give')}
                                        </Button>
                                    </span>
                                </PanelRow>
                                <PanelRow className="givewp-next-gen-prepublish-panel_input">
                                    <TextControl ref={permalinkField} value={permalink} onChange={null} />
                                </PanelRow>
                                <PanelRow className="givewp-next-gen-prepublish-panel_view">
                                    <Button
                                        className="givewp-next-gen-prepublish-panel_view_button"
                                        variant="primary"
                                        href={permalink}
                                        target="_blank"
                                    >
                                        {__('View form', 'give')}
                                    </Button>
                                </PanelRow>
                            </PanelBody>
                        </>
                    ) : (
                        <>
                            <div className="givewp-next-gen-prepublish-panel__header">
                                <div className="givewp-next-gen-prepublish-panel__header-actions">
                                    <Button variant="primary" onClick={handleSave}>
                                        {__('Publish', 'give')}
                                    </Button>

                                    <Button variant="secondary" onClick={handleClose}>
                                        {__('Cancel', 'give')}
                                    </Button>
                                </div>
                            </div>

                            <div className="givewp-next-gen-prepublish-panel__content">
                                <div>
                                    <strong>{__('Are you ready to publish?', 'give')}</strong>
                                </div>
                                <p>{__('Double-check your settings before publishing', 'give')}</p>
                            </div>

                            <PanelBody
                                className={'givewp-panel-body--summary'}
                                title={__('Summary', 'give')}
                                initialOpen={true}
                            >
                                <FormSummarySettings
                                    settings={settings}
                                    setSettings={(props: {}) => dispatch(setFormSettings(props))}
                                />
                            </PanelBody>
                        </>
                    )}
                </>
            )}
        </div>,
        document.body
    );
}
