import {createPortal} from 'react-dom';
import cx from 'classnames';
import {__} from '@wordpress/i18n';
import {filterURLForDisplay} from '@wordpress/url';
import {decodeEntities} from '@wordpress/html-entities';
import {Button, PanelBody, PanelRow, TextControl, Spinner} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {GiveIcon} from '@givewp/form-builder/components/icons';
import {getWindowData, getSiteData} from '@givewp/form-builder/common';

export default function FormPrepublishPanel({isSaving, isPublished, handleSave, handleClose}) {

    const {
        settings: {formTitle},
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const {siteName, siteUrl} = getSiteData()
    const {
        formPage: {permalink},
    } = getWindowData();

    const Panel = () => (
        <div
            className={cx('givewp-next-gen-prepublish-panel', {'givewp-next-gen-prepublish-panel__animate': !isPublished})}
        >
            {isSaving ? (
                <Publishing />
            ) : (
                <>
                    {isPublished ? <Info /> : <Confirm />}
                </>
            )}
        </div>
    )

    const Publishing = () => (
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

    )

    const Confirm = () => (
        <>
            <div className="givewp-next-gen-prepublish-panel__header">
                <div className="givewp-next-gen-prepublish-panel__header-actions">
                    <Button
                        variant="primary"
                        onClick={handleSave}
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
                        <span className="givewp-next-gen-prepublish-panel__site-card-info-url">{filterURLForDisplay(siteUrl || '')}</span>
                    </div>
                </div>
            </div>

            <PanelBody className={'givewp-panel-body--summary'} title={__('Summary', 'give')} initialOpen={true}>
                <PanelRow>
                    <TextControl
                        label={__('Title')}
                        value={formTitle}
                        onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                    />
                </PanelRow>
            </PanelBody>
        </>
    )

    const Info = () => (
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

            <div className="givewp-next-gen-prepublish-panel__content">
                <p>{__('New Generation Form is now live', 'give')}.</p>
            </div>

            <PanelBody className={'givewp-panel-body--summary'} title={__("What's next?", 'give')} initialOpen={true}>
                <PanelRow>
                    <div>
                        {__('PAGE ADDRESS', 'give')}
                    </div>
                    <div>
                        {__('Copy', 'give')}
                    </div>
                </PanelRow>
                <PanelRow>
                    <TextControl
                        value={permalink}
                        onChange={null}
                    />
                </PanelRow>
            </PanelBody>
        </>
    )

    if(!siteName || !siteUrl) {
        return null;
    }

    return createPortal(<Panel />, document.body)

}
