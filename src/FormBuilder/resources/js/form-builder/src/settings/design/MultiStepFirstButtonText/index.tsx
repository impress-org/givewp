import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useFormDesignSetting} from '@givewp/form-builder/hooks';

/**
 * @since 3.0.0
 */
export default function MultiStepFirstButtonText({text}: {text: string}) {
    const {inputValue, setInputValue, updateSetting} = useFormDesignSetting(text);

    return (
        <TextControl
            label={__('First Step Button Text', 'give')}
            value={inputValue}
            onChange={(value) => {
                setInputValue(value);
                updateSetting('multiStepFirstButtonText', value);
            }}
            help={__('Customize the text that appears in the first step, prompting the user to go to the next step.')}
        />
    );
}
