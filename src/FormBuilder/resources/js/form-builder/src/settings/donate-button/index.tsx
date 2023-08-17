import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

export default function DonateButton() {
    const {
        settings: {donateButtonCaption},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody title={__('Donate Button', 'give')} initialOpen={false}>
            <PanelRow>
                <TextControl
                    label={__('Donate button caption', 'give')}
                    help={__('Update the text inside the donate button.', 'give')}
                    value={donateButtonCaption}
                    onChange={(value) => dispatch(setFormSettings({donateButtonCaption: value}))}
                />
            </PanelRow>
        </PanelBody>
    );
}
