import {CheckboxControl} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, description} = attributes;

    return (
        <>
            <div>
                <CheckboxControl label={label} readOnly onChange={null} help={description} />
            </div>
        </>
    );
}
