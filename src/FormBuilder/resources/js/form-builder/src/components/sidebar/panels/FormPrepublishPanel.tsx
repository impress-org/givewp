import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
import {Button, PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {GiveIcon} from '@givewp/form-builder/components/icons';

export default function FormPrepublishPanel({handleSave, handleClose}) {

    const {
        settings: {formTitle},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return createPortal(
        <div className="givewp-next-gen-prepublish-panel">
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
                    <div className="givewp-next-gen-prepublish-panel__site-icon">
                        <GiveIcon />
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
        </div>,
        document.body
    )

}
