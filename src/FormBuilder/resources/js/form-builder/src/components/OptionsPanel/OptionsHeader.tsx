import {BaseControl, Button} from '@wordpress/components';

import {plusCircle} from './icons';
import {OptionsHeaderProps} from '@givewp/form-builder/components/OptionsPanel/types';

export default function OptionsHeader({handleAddOption, label, readOnly}: OptionsHeaderProps) {
    return (
        <div className={'givewp-options-header'}>
            <BaseControl.VisualLabel className={'givewp-options-header--label'}>{label}</BaseControl.VisualLabel>
            {!readOnly && (
                <Button icon={plusCircle} className={'givewp-options-header--button'} onClick={handleAddOption} />
            )}
        </div>
    );
}
