import {TextControl} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, isRequired, placeholder} = attributes;
    const requiredClass = isRequired ? 'give-is-required' : '';

    return (
        <>
            <div>
                <TextControl
                    label={label}
                    placeholder={placeholder}
                    required={isRequired}
                    className={requiredClass}
                    readOnly
                    onChange={null}
                    value={placeholder}
                />
            </div>
        </>
    );
}
