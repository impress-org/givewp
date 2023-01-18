import {PanelBody, PanelRow, TextareaControl, TextControl} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {setFormSettings, useFormState, useFormStateDispatch} from "@givewp/form-builder/stores/form-state";

const ReceiptSettings = () => {

    const {
        settings: {receiptHeading, receiptDescription},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody title={__('Receipt and Thank You')} initialOpen={false}>
            <PanelRow>
                <TextControl
                    label={__('Heading', 'give')}
                    value={receiptHeading}
                    onChange={(receiptHeading) => dispatch(setFormSettings({receiptHeading}))}
                />
            </PanelRow>
            <PanelRow>
                <TextareaControl
                    label={__('Description', 'give')}
                    value={receiptDescription}
                    onChange={(receiptDescription) => dispatch(setFormSettings({receiptDescription}))}
                />
            </PanelRow>
        </PanelBody>
    )
}

export default ReceiptSettings;
