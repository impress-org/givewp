import {useState, useCallback} from '@wordpress/element';
import type {OptionProps} from '@givewp/form-builder/components/OptionsPanel/types';
import {formatCurrencyAmount} from '@givewp/form-builder/components/CurrencyControl';

function generateLevelId() {
    return String(Math.floor(Math.random() * 1000000));
}

export default function useDonationLevels(
    levels: number[],
    defaultLevel: number,
    setDefaultLevel: (defaultLevel: number) => void,
    setLevels: (levels: number[]) => void,
) {
    const [levelOptions, setLevelOptions] = useState<OptionProps[]>(
        levels.map((level: number) => ({
            id: generateLevelId(),
            label: formatCurrencyAmount(level.toString()),
            value: level.toString(),
            checked: defaultLevel === level,
        }))
    );

    const handleLevelAdded = useCallback(() => {
        const newLevelValue = levels.length ? String(Math.max(...levels) * 2) : '10';
        const newLevel = {
            id: generateLevelId(),
            label: formatCurrencyAmount(newLevelValue),
            value: newLevelValue,
            checked: false,
        };

        // If there are no levels, set the new level as the default.
        if (!levels.length) {
            newLevel.checked = true;
            setDefaultLevel(Number(newLevelValue));
        }

        setLevelOptions([...levelOptions, newLevel]);
        setLevels([...levels, Number(newLevelValue)]);
    }, [levels, setLevels, setDefaultLevel, setLevelOptions]);

    const handleLevelRemoved = useCallback(
        (level: OptionProps, index: number) => {
            const newLevels = levels.filter((_, i) => i !== index);
            const newLevelOptions = levelOptions.filter((_, i) => i !== index);

            if (level.checked && newLevelOptions.length > 0) {
                newLevelOptions[0].checked = true;
                setDefaultLevel(Number(newLevelOptions[0].value));
            }

            setLevelOptions(newLevelOptions);
            setLevels(newLevels);
        },
        [levels, setLevels, setDefaultLevel, setLevelOptions]
    );

    const handleLevelsChange = useCallback((options: OptionProps[]) => {
        const checkedLevel = options.filter((option) => option.checked);
        const newLevels = options.filter((option) => option.value).map((option) => Number(option.value));

        setLevelOptions(options);
        setLevels(newLevels);
        setDefaultLevel(Number(checkedLevel[0].value));
    }, [setLevels, setDefaultLevel, setLevelOptions]);

    return {
        levelOptions,
        handleLevelAdded,
        handleLevelRemoved,
        handleLevelsChange,
    };
}
