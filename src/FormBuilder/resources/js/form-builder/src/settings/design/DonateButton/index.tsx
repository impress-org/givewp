import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useFormDesignSetting} from '@givewp/form-builder/hooks';

/**
 * @since 3.0.0
 */
export default function DonateButton({text}: {text: string}) {
    const {inputValue, setInputValue, updateSetting} = useFormDesignSetting(text);

    return (
        <TextControl
            label={__('Button caption', 'give')}
            value={inputValue}
            onChange={(value) => {
                setInputValue(value);
                updateSetting('donateButtonCaption', value, 'previewFormSettings');
            }}
            help={__('Enter the text you want to display on the donation button.', 'give')}
        />
    );
}
