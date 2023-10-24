import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {noop} from 'lodash';
import {FocusEventHandler} from 'react';

type Props = {
    label: string;
    setAttributes: BlockEditProps<any>['setAttributes'];

    onBlur?: FocusEventHandler<HTMLInputElement>;
};

export default function Label({label, setAttributes, onBlur = noop}: Props) {
    return (
        <TextControl
            label={__('Label', 'give')}
            value={label}
            onChange={(val) => setAttributes({label: val})}
            onBlur={onBlur}
        />
    );
}