import {CheckboxControl} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';

import './styles.scss';

export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, description} = attributes;

    return (
        <div className={'givewp-fields-anonymous-donations'}>
            <CheckboxControl checked={false} label={label} readOnly onChange={null} help={description} />
        </div>
    );
}
