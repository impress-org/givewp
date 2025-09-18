import ReactSelect, { components } from 'react-select';
import { useCampaignAsyncSelect } from './useAsyncCampaigns';
import { AsyncPaginate } from 'react-select-async-paginate';
import { CampaignOption } from './utils';
import styles from './styles.module.scss';

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
	isClearable?: boolean;
	isAsync?: boolean;
}

/**
 * @unreleased
 */
export default function CustomFilter(props: CustomFilterProps) {
	return props.isAsync ? <AsyncFilter {...props} /> : <DefaultFilter {...props} />
}

/**
 * @unreleased
 */
function DefaultFilter({name, options, ariaLabel, placeholder, onChange, defaultValue, isSearchable, isSelectable}: CustomFilterProps) {
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

	return (
			<ReactSelect
				inputId={`givewp-filter-${name}`}
				name={name}
				options={formattedOptions}
				value={defaultOption}
				onChange={handleChange}
				onInputChange={handleInputChange}
				placeholder={placeholder}
				aria-label={ariaLabel}
				isSearchable={isSearchable}
				isClearable={false}
				classNamePrefix="searchableSelect"
				className={styles.searchableSelect}
				components={{
					DropdownIndicator: isSelectable ? components.DropdownIndicator : () => null,
					Menu: isSelectable ? components.Menu : () => null,
					MenuList: isSelectable ? components.MenuList : () => null,
					IndicatorSeparator: () => null,
					ClearIndicator: () => null,
				}}
			/>
	);
}

/**
 * @unreleased
 */
function AsyncFilter({name, placeholder, onChange, isSearchable, isClearable}: CustomFilterProps) {
	const { loadOptions, mapOptionsForMenu, selectedOption, setSelectedOption } = useCampaignAsyncSelect();

	const handleChange = (selectedOption: CampaignOption | null) => {
		onChange(name, selectedOption?.value.toString() ?? '');
		setSelectedOption(selectedOption);
	}

	return (
		<AsyncPaginate
			inputId={`givewp-async-filter-${name}`}
			placeholder={placeholder}
			loadOptions={loadOptions}
			onChange={handleChange}
			value={selectedOption}
			isSearchable={isSearchable}
			isClearable={isClearable}
			mapOptionsForMenu={mapOptionsForMenu}
			className={styles.searchableSelect}
			classNamePrefix="searchableSelect"
			debounceTimeout={600}
		/>
	);
}
