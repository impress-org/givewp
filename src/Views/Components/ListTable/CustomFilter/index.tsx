import ReactSelect, { components } from 'react-select';
import { buildStyleConfig } from './utils';
import './styles.scss';

/**
 * @unreleased
 */
type FilterOption = {
	value: string;
	text: string;
}

/**
 * @unreleased
 */
type CustomFilterProps = {
	name: string;
	options?: FilterOption[];
	ariaLabel?: string;
	placeholder?: string;
	onChange: (name: string, value: string) => void;
	defaultValue?: string;
	isSearchable?: boolean;
	isSelectable?: boolean;
	width?: string | number;
}

/**
 * @unreleased
 */
export default function CustomFilter({
	name,
	options,
	ariaLabel,
	placeholder,
	onChange,
	defaultValue,
	isSearchable = true,
	isSelectable = true,
	width,
}: CustomFilterProps) {
	const formattedOptions = options?.map(({ value, text }) => ({
		value,
		label: text,
	}));
    
	const defaultOption = formattedOptions?.find((o) => o.value === defaultValue) || null;

	const handleChange = (selected: any) =>
		onChange(name, selected ? selected.value : '');

	const handleInputChange = (inputValue: string) => {
		onChange(name, inputValue);
	};

	const styleConfig = buildStyleConfig(width);

	return (
        <div id={`givewp-filter-${name}`}>
            <ReactSelect
                name={name}
                options={formattedOptions}
                value={defaultOption}
                onChange={handleChange}
                onInputChange={handleInputChange}
                placeholder={placeholder}
                aria-label={ariaLabel}
                isSearchable={isSearchable}
                isClearable={false}
                classNamePrefix="givewp-filter-select"
                styles={styleConfig}
                components={{
                    DropdownIndicator: isSelectable ? components.DropdownIndicator : () => null,
                    Menu: isSelectable ? components.Menu : () => null,
                    MenuList: isSelectable ? components.MenuList : () => null,
                    IndicatorSeparator: () => null,
                    ClearIndicator: () => null,
                }}
            />
        </div>
	);
}

