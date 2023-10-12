import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {FocusEventHandler} from 'react';

type Props = {
    label: string;
    setAttributes: BlockEditProps<any>['setAttributes'];

    onBlur?: FocusEventHandler<HTMLInputElement>;
};

const noop = () => {};

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