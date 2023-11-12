import {BaseControl, PanelRow, ToggleControl} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import OptionsHeader from './OptionsHeader';
import OptionsList from './OptionsList';

import {OptionsPanelProps} from './types';

export default function Options({
    currency,
    multiple,
    selectable = true,
    options,
    setOptions,
    defaultControlsTooltip,
    onAddOption,
    onRemoveOption,
}: OptionsPanelProps) {
    const [showValues, setShowValues] = useState<boolean>(false);

    const handleAddOption = (): void => {
        if (onAddOption) {
            onAddOption();
            return;
        }

        setOptions([...options, {label: '', value: '', checked: false}]);
    };

    return (
        <>
            {!currency && (
                <PanelRow>
                    <ToggleControl
                        label={__('Show values', 'give')}
                        checked={showValues}
                        onChange={() => setShowValues(!showValues)}
                    />
                </PanelRow>
            )}
            <PanelRow>
                <BaseControl id={'give'}>
                    <OptionsHeader handleAddOption={handleAddOption} />
                    <OptionsList
                        currency={currency}
                        options={options}
                        showValues={showValues}
                        multiple={multiple}
                        selectable={selectable}
                        setOptions={setOptions}
                        defaultControlsTooltip={defaultControlsTooltip}
                        onRemoveOption={onRemoveOption}
                    />
                </BaseControl>
            </PanelRow>
        </>
    );
}
