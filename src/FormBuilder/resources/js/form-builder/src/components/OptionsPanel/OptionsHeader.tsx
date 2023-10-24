import {__} from '@wordpress/i18n';
import {BaseControl, Button} from '@wordpress/components';

import {plusCircle} from './icons';

export default function OptionsHeader({handleAddOption}: {handleAddOption: () => void}) {
    return (
        <div className={'givewp-options-header'}>
            <BaseControl.VisualLabel className={'givewp-options-header--label'}>
                {__('Options', 'give')}
            </BaseControl.VisualLabel>
            <Button icon={plusCircle} className={'givewp-options-header--button'} onClick={handleAddOption} />
        </div>
    );
}
