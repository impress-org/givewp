import {TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';

type Props = {
    placeholder: string;
    setAttributes: BlockEditProps<any>['setAttributes'];
};

export default function Placeholder({placeholder, setAttributes}: Props) {
    return (
         <TextControl
            label={__('Placeholder', 'give')}
            value={placeholder}
            onChange={(val) => setAttributes({placeholder: val})}
        />
    );
}