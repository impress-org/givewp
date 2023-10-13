import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useFormDesignSetting} from '@givewp/form-builder/hooks';

/**
 * @since 3.0.0
 */
export default function MultiStepNextButtonText({text}: {text: string}) {
    const {inputValue, setInputValue, updateSetting} = useFormDesignSetting(text);

    return (
        <TextControl
            label={__('Next Step Button Text', 'give')}
            value={inputValue}
            onChange={(value) => {
                setInputValue(value);
                updateSetting('multiStepNextButtonText', value);
            }}
            help={__('Customize the text that appears prompting the user to go to the next step.')}
        />
    );
}
