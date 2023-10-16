import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {ToggleControl} from '@wordpress/components';

type Props = {
    isRequired: boolean;
    setAttributes: BlockEditProps<any>['setAttributes'];
};

export default function Required({isRequired, setAttributes}: Props) {
    return (
        <ToggleControl
            label={__('Required', 'give')}
            checked={isRequired}
            onChange={() => setAttributes({isRequired: !isRequired})}
        />
    );
}
