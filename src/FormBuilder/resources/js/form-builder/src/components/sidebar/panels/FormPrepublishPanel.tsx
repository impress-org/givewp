import {useState, createRef, MouseEventHandler, RefObject} from 'react';
import {createPortal} from 'react-dom';
import cx from 'classnames';
import {__} from '@wordpress/i18n';
import {filterURLForDisplay} from '@wordpress/url';
import {decodeEntities} from '@wordpress/html-entities';
import {Button, PanelBody, PanelRow, SelectControl, Spinner, TextControl} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {CopyIcon, GiveIcon} from '@givewp/form-builder/components/icons';
import {getSiteData, getWindowData} from '@givewp/form-builder/common';
import {FormStatus} from "@givewp/form-builder/types";

interface FormPrepublishPanelProps {
    isSaving: boolean;
    isPublished: boolean;
    handleSave: Function;
    handleClose: MouseEventHandler<HTMLButtonElement>;
}

export default function FormPrepublishPanel
({
     isSaving,
     isPublished,
     handleSave,
     handleClose
 }: FormPrepublishPanelProps) {

    const {
        settings: {formTitle, formStatus},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const {siteName, siteUrl} = getSiteData()
    const {
        formPage: {permalink},
    } = getWindowData();

    const [status, setStatus] = useState('publish');

    const permalinkField: RefObject<HTMLInputElement> = createRef();

    return createPortal(
        <div className={cx('givewp-next-gen-prepublish-panel', {'givewp-next-gen-prepublish-panel__animate': false})}>
            {isSaving ? (
                <>
                    <div className="givewp-next-gen-prepublish-panel__header">
                        <div className="givewp-next-gen-prepublish-panel__header-actions">
                            <Button
                                variant="primary"
                                isBusy={true}
                                isPressed={true}
                            >
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
                                    <Button
                                        variant="secondary"
                                        onClick={handleClose}
                                    >
                                        {__('Close panel', 'give')}
                                    </Button>
                                </div>
                            </div>

                            <div className="givewp-next-gen-prepublish-panel__content_publish">
                                <strong>
                                    <a href={permalink}
                                       target="_blank">{formTitle}
                                    </a> {__('is now live', 'give')}.
                                </strong>
                            </div>

                            <PanelBody title={__("What's next?", 'give')} initialOpen={true}>
                                <PanelRow className="givewp-next-gen-prepublish-panel_link">
                                    <span>
                                        {__('PAGE ADDRESS', 'give')}
                                    </span>
                                    <span>
                                        <a
                                            href="#"
                                            onClick={async () => {
                                                permalinkField.current.select();
                                                await navigator.clipboard.writeText(permalink)
                                            }}
                                            className="givewp-next-gen-prepublish-panel_copy_link"
                                        >
                                            <CopyIcon />{__('Copy', 'give')}
                                        </a>
                                    </span>
                                </PanelRow>
                                <PanelRow className="givewp-next-gen-prepublish-panel_input">
                                    <TextControl
                                        ref={permalinkField}
                                        value={permalink}
                                        onChange={null}
                                    />
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
                                    <Button
                                        variant="primary"
                                        onClick={() => handleSave(status)}
                                    >
                                        {__('Publish', 'give')}
                                    </Button>

                                    <Button
                                        variant="secondary"
                                        onClick={handleClose}
                                    >
                                        {__('Close', 'give')}
                                    </Button>
                                </div>
                            </div>

                            <div className="givewp-next-gen-prepublish-panel__content">
                                <div>
                                    <strong>{__('Are you ready to publish?', 'give')}</strong>
                                </div>
                                <p>{__('Double-check your settings before publishing', 'give')}</p>
                                <div className="givewp-next-gen-prepublish-panel__site-card">
                                    <div className="givewp-next-gen-prepublish-panel__site-card-icon">
                                        <GiveIcon />
                                    </div>

                                    <div className="givewp-next-gen-prepublish-panel__site-card-info">
                                        <span className="givewp-next-gen-prepublish-panel__site-card-info-name">
                                            {decodeEntities(siteName)}
                                        </span>
                                        <span
                                            className="givewp-next-gen-prepublish-panel__site-card-info-url">{filterURLForDisplay(siteUrl || '')}</span>
                                    </div>
                                </div>
                            </div>

                            <PanelBody title={__('Summary', 'give')} initialOpen={true}>
                                <PanelRow>
                                    <TextControl
                                        label={__('Title')}
                                        value={formTitle}
                                        onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                                    />
                                </PanelRow>
                                <PanelRow>
                                    <div>
                                        {__('Visibility', 'give')}
                                    </div>
                                    <div>
                                        <SelectControl
                                            value={status}
                                            options={[
                                                {label: __('Public', 'give'), value: 'publish'},
                                                {label: __('Private', 'give'), value: 'private'},
                                            ]}
                                            onChange={(status: FormStatus) => setStatus(status)}
                                        />
                                    </div>
                                </PanelRow>
                                <PanelRow className="givewp-next-gen-prepublish-panel_visibility">
                                    {'publish' === status ? __('Visible to everyone', 'give') : __('Only visible to site admins and editors', 'give')}
                                </PanelRow>
                            </PanelBody>
                        </>
                    )}
                </>
            )}
        </div>, document.body)

}
